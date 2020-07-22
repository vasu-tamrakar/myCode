<?php

defined('BASEPATH') or exit('No direct script access allowed');

class GoogleFetchModel extends CI_Model {

  function __construct()
	{
		parent::__construct();
		$this->load->library('GmailMessageFetch');
 
	}

  public function getLastFetch($userId){
    $last_fetch = $this->db->select("last_fetch")->where('user_id',$userId)->limit(1)->order_by('id',"DESC")->get(TBL_PREFIX."user_last_fetch_time")->row();
    if(!empty($last_fetch)){
      $lastfetched_email_date = change_one_timezone_to_another_timezone($last_fetch->last_fetch,DEFAULT_TIME_ZONE_SERVER, 'UTC','Y-m-d H:i:s');
    } else {
      $lastfetched_email_date = change_one_timezone_to_another_timezone(date('Y-m-d 00:00:00'), DEFAULT_TIME_ZONE_SERVER, 'UTC','Y-m-d H:i:s');
    }
    return $lastfetched_email_date;
  }

  public function saveAttachments($value)
  {
    if(!empty($value)){
    $filename = str_replace(',', '', $value['filename']);
    $filename = time().'_'.strtolower(str_replace(" ", "", $filename));  // new file name
    $savedPath =  USER_ATTACHMENT_PATH;
    if(!is_dir($savedPath))mkdir($savedPath);
    $fp = fopen($savedPath . $filename, "w+");
    fwrite($fp, base64_decode($value['data']));
    fclose($fp);
    $filePath = FCPATH.$savedPath.$filename;
    $checkEncrypt = check_pdf_is_encrypted($filePath);
    $encrypted= ($checkEncrypt==true)?1:0;
    return ['file'=>$filename, 'data'=>$value['data'], 'protected'=>$encrypted, 'gmail_file_name'=>$value['filename']];
    }
  }

  public function get_gmail_access_request($extraParms=[]){
    $limit = $extraParms['limit'] ?? CRON_GMAIL_FETCH_USER_LIMIT;
    $page = $extraParms['page'] ?? 0;
    $this->db->select(['gm.user_id as id']);
    $this->db->from(TBL_PREFIX.'user_gmail_auth gm');
    $this->db->where(['gm.archive'=>0]);
    $offset = (int)$limit *(int)$page;
    $this->db->limit((int)$limit,(int)$offset);
    $query =$this->db->get();
    return $query->num_rows()>0 ?  $query->result_array():[];
  }


  public function fetchCommonFunction($userId,$from){
    $response = array();
		$stmtDetails = array();
		$last_fetch = $this->GoogleFetchModel->getLastFetch($userId);
		if(!empty($last_fetch)){
    $attachments = $this->gmailmessagefetch->getUserMessageFetch(['from_date'=>$last_fetch]);
    if(!empty($attachments) && !$attachments['status'] && !$attachments['revoke_access']){
      $response =  array('status' => false, 'data' => 'Can not Access Email, Please connect with your email again.',
      'popUp'=>true,'url' => base64_encode($attachments['auth_url']));
    } else {
      if(!empty($attachments) && $attachments['status']=='1' && !empty($attachments['all_attachment'])){
        $attachmentsData = $this->attachmentsLoop($userId,$attachments,$from);
        $stmtDetails = $attachmentsData['data']??[];
        $flag = $attachmentsData['flag']??false;
        if($flag && !empty($stmtDetails)){
          $scount =0; $icount=0;  $ucount=0;
          $getCount = $this->insertAttachment($stmtDetails);
          $scount = $this->count_array_values($getCount,TBL_PREFIX.'statement');
          $icount = $this->count_array_values($getCount,TBL_PREFIX.'invoice');
          $ucount = $this->count_array_values($getCount,TBL_PREFIX.'unreadable_pdf');
          $link = ($ucount!=0)?'<a href='.UNREADABLE_PDF.'/>'.$ucount.'</a>':'0';  
          $tbl_fetch = TBL_PREFIX.'user_last_fetch_time';
          $saveDate = change_one_timezone_to_another_timezone($attachments['fetch_to'],DEFAULT_TIME_ZONE_SERVER,'UTC','Y-m-d H:i:s');
          $this->BasicModel->insertRecords($tbl_fetch, array('last_fetch' => $saveDate,'user_id'=>$userId),FALSE);
          $response =  array('status' => true, 'data' => 'Successfully Fetched '.$icount.' Invoice/ '.$scount.' Statement and  / '.$link.' Unencrypted Files Updated');
        } else {
          $response =  array('status' => false, 'data' => 'Successfully Fetched Invoice/Statement but not Updated');
        }
      } else {
        $response =  array('status' => false, 'data' => 'Sorry, No Attachments Found');
        }
      }
		}  
		return $response;
  }
  
  private function attachmentsLoop($userId,$attachments=[],$from){
    if(!empty($attachments['all_attachment'])){
      foreach ($attachments['all_attachment'] as $key => $values) {
        $all_attachments[] = $values['all_attachment'];
      }
      $fetchedValues=array();
      if(!empty($all_attachments)){
        foreach($all_attachments as $key => $attach){
          if(!empty($attach)){
            foreach($attach as $d => $nooffile){
              $fetchedValues[] = array(
                'filename' => $nooffile['filename'],
                'mimeType' => $nooffile['mimeType'],
                'data' => $nooffile['data'],
              );
            }
         }
        }
      }
      
      if(!empty($fetchedValues)){
        $pdfDetails = array();
        $flag=false;
        $row= array();
        foreach($fetchedValues as $value){
          $savedFileName = $this->saveAttachments($value);
          if(!empty($savedFileName) && $savedFileName['protected']==1){
            $fileDetails  = $this->EncrypttodecryptTheFIle($savedFileName,$userId);
    
            if(!empty($fileDetails) && $fileDetails['decrypted_file']==0){
              $row  = $this->get_unreadable_pdffile_data($fileDetails,$userId,$from);
            } else if(!empty($fileDetails) && $fileDetails['decrypted_file']==1){
              $pdfDetails =  $fileDetails; 
              $row = checkStatementType($pdfDetails,$userId);
            }
          } else  {
            $pdfDetails =  $savedFileName;
            $row = checkStatementType($pdfDetails,$userId); 
          }
          $stmtDetails[] = $row;
          $flag=true;
        }
 
        return ['flag'=>$flag,'data'=>$stmtDetails];
      }
    }
  }

  function  get_unreadable_pdffile_data($data=null,$id,$from){
    $alldata=array();
    $tableName = TBL_PREFIX.'unreadable_pdf';
    $row['file_name'] = $data['file'];
    $row['user_id'] = $id;
    $row['status'] = 1;
    $row['source_type'] = $from;
    $row['created'] = create_date_store_in_db();
    $row['protected'] = $data['protected'];
    $row['original_file_name'] = $data['gmail_file_name'];
    $alldata[$tableName] = $row;
    return $alldata; 
  }
 
  function EncrypttodecryptTheFIle($data=[],$userId){
    $alldata=array();
    $allPasswords = $this->db->select("password")->where('user_id',$userId)->get(TBL_PREFIX."bank_password")->result_array();
    $targetPath =  FCPATH.USER_ATTACHMENT_PATH.'psw_'.$data['file'];
    $filePath = FCPATH.USER_ATTACHMENT_PATH.$data['file'];
    if(!empty($allPasswords)){
        foreach($allPasswords as $psw){
          $decryptPsw = encrypt_decrypt('decrypt', $psw['password']);
          $result = encrypyted_pdf_to_decrypt($targetPath, $filePath, $decryptPsw); 
        }
       
        if(!empty($result)){
        $checkEncrypt = check_pdf_is_encrypted($targetPath);
        if(!$checkEncrypt){
          $alldata['file']= 'psw_'.$data['file'];
          $alldata['protected']= 0;
          $alldata['decrypted_file']= 1;
          $alldata['gmail_file_name']= $data['gmail_file_name'];
        } else {
          $alldata['file']= $data['file'];
          $alldata['decrypted_file']= 0;
          $alldata['protected']= 1;
          $alldata['gmail_file_name']= $data['gmail_file_name'];
        }
      } else {
        $alldata['file']= $data['file'];
        $alldata['decrypted_file']= 0;
        $alldata['protected']= 1;
        $alldata['gmail_file_name']= $data['gmail_file_name'];
        return $alldata; 
      }
    } else {
      $alldata['file']= $data['file'];
      $alldata['decrypted_file']= 0;
      $alldata['protected']= 1;
      $alldata['gmail_file_name']= $data['gmail_file_name'];
    }
    return $alldata; 
}

	private function insertAttachment($stmtDetails){
		if(!empty($stmtDetails)){
			$tablesCOunt=array();
 			foreach ($stmtDetails as  $value) {
				$tablesCOunt[] = array_keys($value)[0];
				$table = array_keys($value)[0];
				if(!empty($table) && !empty($table)){
				$this->BasicModel->insertRecords($table, $value,TRUE);
				}
		   }
		   return $tablesCOunt; 
		}
	}

	function count_array_values($my_array, $match) 
	{ 
		$count = 0; 
		foreach ($my_array as $key => $value) 
		{ 
			if ($value == $match) 
			{ $count++; } 
		} 
		return $count; 
	} 



}
