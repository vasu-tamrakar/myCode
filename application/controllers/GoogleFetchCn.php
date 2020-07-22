<?php
defined('BASEPATH') or exit('No direct script access allowed');

class GoogleFetchCn extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->library('GmailMessageFetch');
		$this->load->model('GoogleFetchModel');
	}

    function get_token_url($userId){
        $response = ['status'=>false,'error'=>'something went worng'];
        $this->gmailmessagefetch->setUserId($userId);
        $res = $this->gmailmessagefetch->checkAuthToken();
        if(!$res['status'] && !empty($res['auth_url'])){
            $response = ['status'=>true,'auth_url'=>$res['auth_url']];
        }else if($res['status']){
            $response = ['status'=>false,'error'=>'token already genrated.'];
        }
		return $response;
    }

    function authenticate(){
        
        parse_str($this->input->get('state'),$parms);
        $userId =$parms['user_id'];
        $redirect_to = $parms['user_rd']??0; 
        $code=$this->input->get('code');
        $this->gmailmessagefetch->setUserId($userId);
        //$this->gmailmessagefetch->setRedirectTo($redirect_to);
        $this->gmailmessagefetch->setTokenCode($code);
        $res =$this->gmailmessagefetch->saveToken();
        if($res['status']){
            $response =['status'=>$res['status']];
            $response['message'] = 'token genrated successfully.';
            if($redirect_to==1){
                redirect(FRONT_URL."user/invoice_statement");
            } else {
                redirect(FRONT_URL."user/bank_statement");
            } 
        }else{
            $response =$res;
        }
				echo json_encode($response);
				exit();
    }

    function get_message_attachment(){
        $userId=1;
        $this->gmailmessagefetch->setUserId($userId);
        $res = $this->gmailmessagefetch->checkAuthToken();
        if($res['status']){
           $result = $this->gmailmessagefetch->getUserMessageFetch(['from_date'=>'2020-05-01 00:00:00']);
           pr($result);
        }
        pr(['res'=>'withoutif',$res]);
    }

		/* Attachement for fetch both Statment or Invoice */
	function fetchUserAttachements(){
            $request_body = get_json_data(true);
            $userId = $request_body->user_id;
            $fetchBy =0;
			if(!empty($request_body) && !empty($request_body->data) && $userId)
			{
                $fetchBy = $request_body->data->fetchBy; 
                $this->gmailmessagefetch->setUserId($userId);
                $this->gmailmessagefetch->setRedirectTo($fetchBy);
				$res = $this->gmailmessagefetch->checkAuthToken();
				$response =  array('status' => false, 'data' => 'Something Went Wrong');
				if(!$res['status'] && !$res['revoke_access']){
					$response =  array('status' => false, 'data' => 'Connect with your Email.',
					'popUp'=>true,'url' => base64_encode($res['auth_url']));
				} else if(!$res['status'] && $res['revoke_access']) {
					$response =  array('status' => false, 'data' => 'Can not Access Email, Please connect with your email again.',
					'popUp'=>true,'url' => base64_encode($res['auth_url']));
				} else 
				if($res['status']){
					$response = $this->GoogleFetchModel->fetchCommonFunction($userId,1);
				} 
			}
		echo json_encode($response);
		exit();
	}


	
	

}
