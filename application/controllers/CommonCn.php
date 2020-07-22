<?php
defined('BASEPATH')  || exit('No direct script access allowed');

class CommonCn extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('Common_model');
	}

    function cookie_set() {
        $typeCookieName = USER_ATTACHMENT_COOKIE_TOKEN_NAME;
        if($this->input->get('type')){
            $typeCookieName = ($this->input->get('type')) ==1 ? ADMIN_ATTACHMENT_COOKIE_TOKEN_NAME : $typeCookieName; 
        }
        
        if ($this->input->get('tc')) {
            setcookie($typeCookieName, $this->input->get('tc'), time() + (86400 * 1), "/"); // 86400 = 1 day
            if ($this->input->get('rd')) {
                $rd = base64_decode(urldecode($this->input->get('rd')));
                $fd = $this->input->get('fd')? $this->input->get('fd'):0;
                redirect($rd.'?fd='.$fd);
            } else {
                echo 'Access denied';
                exit;
            }
        }
    }
public function mediaShow($loginKey, $type, $file_name, $userId = '') {
        
        $file_name = base64_decode(urldecode($file_name));
        $userId = !empty($userId) ? base64_decode(urldecode($userId)) : $userId;   
        $cookieKeyName = $loginKey == 1 ? ADMIN_ATTACHMENT_COOKIE_TOKEN_NAME : USER_ATTACHMENT_COOKIE_TOKEN_NAME; 
		$this->load->helper('file');
        $this->load->helper('cookie');
        $tokenNotCheck=['xls','u_prf','u_prf_s','a_prf','a_prf_s','i_pdf'];
        
        $token = get_cookie($cookieKeyName);
        $checkToken = in_array($type,$tokenNotCheck)? 0:1; 
        $fd=0;
        if ($this->input->get('fd')) {
            $fd=$this->input->get('fd');
        }

        $resData =$this->Common_model->file_content_media($type,$file_name,$userId,$checkToken,['token'=>$token,'login_type'=>$loginKey]);
        ob_get_clean();
        if($resData['status']){
            header('content-type: ' . $resData['mimetype']);
            if($fd==1){
                header('Content-disposition: attachment; filename='.$file_name);
            }
        }
        echo $resData['msg'];
        
		exit;
    }
}