<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH."third_party/google/vendor/autoload.php";
class GmailMessageFetch
{
	protected $CI;
	protected $gmailApplicationName;
	protected $gmailClientId;
	protected $gmailClientSecret;
	protected $authRedirectUrl;
	protected $client;
	protected $accessToken;
	protected $userId=0;
	protected $googleUserId='me';
	protected $authUrlData=0;
	protected $code=0;
  protected $service;
  protected $reDirect=0;

	public function __construct() {
        // Assign the CodeIgniter super-object
        $this->CI = & get_instance();
        $this->CI->load->config('google_api');
        $this->gmailApplicationName = $this->CI->config->item('google_api_application_name');
        $this->gmailClientId = $this->CI->config->item('google_api_client_id');
        $this->gmailClientSecret = $this->CI->config->item('google_api_client_secret');
        $this->authRedirectUrl = base_url('GoogleFetchCn/authenticate');
        $this->client = new Google_Client();
        $this->accessToken = '';
        $this->setApiKey();
    }

    private function setApiKey(){
        $this->client->setApplicationName($this->gmailApplicationName);
        $this->client->setScopes(Google_Service_Gmail::GMAIL_READONLY);
        $this->client->setClientId($this->gmailClientId);
        $this->client->setClientSecret($this->gmailClientSecret);
        $this->client->setRedirectUri($this->authRedirectUrl);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('select_account consent');
    }
    public function getUserId() {
        return $this->userId;
    }

    public function setUserId(int $userId) {
        $this->userId = $userId;
      }
      
      private function getAuthUrl() {
        $this->client->setState('user_rd='.$this->reDirect.'&user_id='.$this->userId);
        return $this->client->createAuthUrl();
      }
      
      public function setRedirectTo(int $reDirect) {
        $this->reDirect = $reDirect;
    }
    


    public function checkAuthToken(){
      $row = $this->CI->BasicModel->getRow(TBL_PREFIX.'user_gmail_auth',['token'],['archive'=>0,'user_id'=>$this->userId]);
      if($row){
          $token = isset($row->token) && !empty($row->token) ? json_decode($row->token,true) : '';
          $this->client->setAccessToken($token);
          $tokenExipired = $this->client->isAccessTokenExpired();
          if ($tokenExipired) { 
            // Refresh the token if possible, else fetch a new one.
              if($this->client->getRefreshToken()) {
                  $checkRevoke = $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
                  if(isset($checkRevoke['error']) && !empty($checkRevoke['error'])){
                    return ['status'=>false,'auth_url'=>$this->getAuthUrl(), 'revoke_access'=>true ];
                  } 
                  $this->saveUserTokenInDb();
              } 
           } 
          return ['status'=>true];
      }else{
          return ['status'=>false,'auth_url'=> $this->getAuthUrl(), 'revoke_access'=>false];
      }
  }

    public function checkAuthToken_old(){
        $row = $this->CI->BasicModel->getRow(TBL_PREFIX.'user_gmail_auth',['token'],['archive'=>0,'user_id'=>$this->userId]);
        if($row){
            $token = isset($row->token) && !empty($row->token) ? json_decode($row->token,true) : '';
            $this->client->setAccessToken($token);
            if ($this->client->isAccessTokenExpired()) {
                // Refresh the token if possible, else fetch a new one.
                if ($this->client->getRefreshToken()) {
                    $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
                    $this->saveUserTokenInDb();
                } else{
                    return ['status'=>false,'auth_url'=>$this->getAuthUrl() ];
                }
            }
            return ['status'=>true];
        }else{
            return ['status'=>false,'auth_url'=> $this->getAuthUrl()];
        }
    }

    function setTokenCode($code){
        $this->code = $code;
    }
    function setRedirectUri($url){
        $this->authRedirectUrl = $url;
        $this->client->setRedirectUri($this->authRedirectUrl);
    }

    function saveToken()
    {
        $accessToken = $this->client->fetchAccessTokenWithAuthCode($this->code);
        if (array_key_exists('error', $accessToken)) {
            return ['status'=>false,'error'=>'invalid auth code.','res'=>$accessToken];
        }
        $this->client->setAccessToken($accessToken);
        $this->saveUserTokenInDb();
        return ['status'=>true];

    }

    private function saveUserTokenInDb(){
        $this->CI->BasicModel->updateRecords(TBL_PREFIX.'user_gmail_auth',['archive'=>1],['user_id'=>$this->userId]);
        $this->CI->BasicModel->insertRecords(TBL_PREFIX.'user_gmail_auth',['user_id'=>$this->userId,'token'=>json_encode($this->client->getAccessToken()),'archive'=>'0','created'=>create_date_store_in_db()]);
    }

    function getHeader($headers, $name) {
        foreach($headers as $header) {
          if($header['name'] == $name) {
            return $header['value'];
          }
        }
      }

      function getAttachments($message_id, $parts) {
          $attachments = [];
          $header = [];
          foreach ($parts as $part) {
              if (!empty($part->body->attachmentId)) {
                  $attachment = $this->service->users_messages_attachments->get('me', $message_id, $part->body->attachmentId);
                  $attachments[] = [
                      'filename' => $part->filename,
                      'mimeType' => $part->mimeType,
                      'data'     => strtr($attachment->data, '-_', '+/')
                  ];
              } else if (!empty($part->parts)) {
                  $attachments = array_merge($attachments, $this->getAttachments($message_id, $part->parts));
              }
          }
          return $attachments;
      }
      function conditionApply($key,$val,$condition=''){
          switch ($key!='') {
            case $key:
              $condition .= ' '.$key.':'.$val;
              break;
            default:
              break;
          }
          return $condition;
      }

      function listMessages($userIdData, $extaraParms=[]) {
          $conditionFilter = '';
          $afterDate = $extaraParms['from_date'] ?? date('Y-m-d 00:00:00');
          $afterDateEpoch = strtotime($afterDate);
          $applyAfterDate = $extaraParms['apply_from_date'] ?? true;
          $beforeDate = $extaraParms['to_date'] ?? date('Y-m-d H:i:s');
          $beforeDateEpoch = strtotime($beforeDate);
          $applyBeforeDate = $extaraParms['apply_to_date'] ?? true;
          if(boolval($applyAfterDate) && !empty($afterDateEpoch)){
            $conditionFilter = $this->conditionApply('after',$afterDateEpoch,$conditionFilter);
          }
          if(boolval($applyBeforeDate) && !empty($beforeDateEpoch)){
            $conditionFilter = $this->conditionApply('before',$beforeDateEpoch,$conditionFilter);
          }
          $pageToken = NULL;
          $messages = array();
          $opt_param = array();
          $opt_param['maxResults'] = 40; // Return Only 5 Messages
          $opt_param['labelIds'] = 'INBOX'; // Only show messages in Inbox
          $opt_param['q'] = "has:attachment {filename:pdf} larger:10000".$conditionFilter;
          do {
            try {
              if ($pageToken) {
                $opt_param['pageToken'] = $pageToken;
              }
              $messagesResponse = $this->service->users_messages->listUsersMessages($userIdData, $opt_param);
              if ($messagesResponse->getMessages()) {
                $messages = array_merge($messages, $messagesResponse->getMessages());
                $pageToken = $messagesResponse->getNextPageToken();
              }
              
            } catch (Exception $e) {
              
              $msg= json_decode($e->getMessage(),true);
              if($msg['error']['status']=='UNAUTHENTICATED'){
               return ['status'=>false,'error'=>$msg['error']['message'],'revoke_access'=>false,  'auth_url'=> $this->getAuthUrl() ];
              }
            }
          } while ($pageToken);

          return ['status'=>true,'data'=>$messages,'fetch_to'=>$beforeDate];
        }

        private function getMessage($userIdData, $messageId) {
          try {
            $message = $this->service->users_messages->get($userIdData, $messageId);
            $attachments = $this->getAttachments($messageId, $message->getPayload()->parts);
            $headers = $message->getPayload()->getHeaders();
            $subject = $this->getHeader($headers, 'Subject');
            $fromName = $this->getHeader($headers, 'From');
            return ['status'=>true,'subject'=>$subject,'from_name'=>$fromName,'message'=>$message,'attachment'=>$attachments,'message_date'=>date('Y-m-d H:i:s',(($message->getInternalDate())/1000))];
          } catch (Exception $e) {
            $msg= json_decode($e->getMessage(),true);
            if($msg['error']['status']=='UNAUTHENTICATED'){
              return ['status'=>false,'error'=>$msg['error']['message'],'revoke_access'=>false,  'auth_url'=> $this->getAuthUrl() ];
            }
          }
        }

        private function setGmailService(){
            $this->service = new Google_Service_Gmail($this->client);
        }
        /* ['from_date'=>'2020-05-01 00:00:00'] */
        public function getUserMessageFetch($extaraParms=[]){
            $this->setGmailService();
            $mailData =[];
            $messages = $this->listMessages($this->googleUserId,$extaraParms);
            if($messages['status']){
            $messageListData = $messages['data'];
            foreach($messageListData as $list){
                $messageId = $list->getId();
                $messageData = $this->getMessage($this->googleUserId,$messageId);
                if($messageData['status']){
                $mailData[]=['message_id'=>$messageId,'subject'=> $messageData['subject'],'message_date'=> $messageData['message_date'],
                'from_name'=> $messageData['from_name'],'all_attachment'=> $messageData['attachment']];
                }
            }
                return ['status'=>true,'fetch_to'=>$messages['fetch_to'],'all_attachment'=>$mailData];
            }else{
                return $messages;
            }
        }

}
