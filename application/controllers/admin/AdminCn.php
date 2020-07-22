<?php
defined('BASEPATH') || exit('No direct script access allowed');

require_once APPPATH . 'traits/formCustomValidation.php';

class AdminCn extends CI_Controller
{
	use formCustomValidation;

	function __construct()
	{
		parent::__construct();
		$this->load->library('form_validation');
		$this->load->model('admin/AdminModel');
	}
 
    public function user__admin_register()
	{

		$request_body = get_json_data(false);
		$request_data = $request_body->data;
		$operate_data = (array)$request_data;

		$this->form_validation->set_rules('firstname', 'First Name', 'required|trim|min_length[2]|max_length[50]');
		$this->form_validation->set_rules('lastname', 'Last Name', 'required|trim');
		$this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|callback_email_check|max_length[64]');
		$this->form_validation->set_rules('password', 'Password', 'required|trim|callback_valid_password|max_length[100]');
		$this->form_validation->set_rules('confirm_password', 'Password Confirmation', 'required|trim|matches[password]');
		$this->form_validation->set_data($operate_data);

		if (!$this->form_validation->run()) {
			$errors = $this->form_validation->error_array();
			$response = ['status' => false, 'msg' => implode(', ', $errors)];
			echo json_encode($response);
			exit();
		}

		$register = $this->AdminModel->insertRegisteredAdminUser($request_data);
		if($register){
			$response = ['status' => true, 'msg' => 'Admin registerd successfully! We have sent an email to verify your account.'];
		}else{
			$response = ['status' => false, 'msg' => 'Registration failed!'];
		}

		echo json_encode($response);
		exit();

	}

	public function login_admin(){
		$request_body = get_json_data(false,['user_type'=>'admin']);
		$request_data = $request_body->data;
		$operate_data = (array)$request_data;

		$this->form_validation->set_rules('email', 'Email', 'required|valid_email');
		$this->form_validation->set_rules('password', 'Password','required|trim');
		$this->form_validation->set_data($operate_data);

		if(!$this->form_validation->run()){
			$errors = $this->form_validation->error_array();
			$response = ['status' => false, 'msg' => implode(', ', $errors)];
			echo json_encode($response);
			exit();
		}

		$loginUser = $this->AdminModel->loginAdminUser($request_data);
		if($loginUser){
			$response = ['status'=>true,'token'=> $loginUser,'msg' =>"login successful"];
		}else{
			$response = ['status'=>false,'msg'=>'Invalid Email or Password'];
		}
		echo json_encode($response);
		exit();
 	}


	public function logout_admin()
	{
		$request_body = get_json_data(true,['user_type'=>'admin']);
		$res = $this->AdminModel->deleteToken($request_body);
		if ($res){
			$response_ary = array('status' => true, 'token_status' => true, 'msg' => 'Logout Successfully');
		} else {
			$response_ary = array('status' => false, 'msg' => 'No User Found');
		}
		echo json_encode($response_ary);
		exit();

	}

	public function expire_token()
	{
		$request_body = get_json_data();
		if(!empty($request_body))
		{
			$result = $this->AdminModel->userInfo($request_body);

			if($result)
			{
				$diff = (strtotime(DATE_TIME) - strtotime($result));

				if ($diff>20) {
					$response_ary = ['status' => false, 'token_status' => true, 'msg' => 'Token expired'];
				}
				else {
					$data = array(
						"logout_time" => date(DATE_TIME_FORMAT, strtotime("+60 minutes"))
					);
					$this->AdminModel->updateToken($data,$request_body);
					$response_ary = ['status' => true, 'token_status' => false, 'msg' => 'Token updated'];
				}
			}
			else
			{
				$response_ary = array('status' => false, 'msg' => 'No User Found');
			}
			echo json_encode($response_ary);
		}
	}


	public function forgot_password(){
		$request_body = get_json_data(false,['user_type'=>'admin']);
		$request_data = $request_body->data;
		$operate_data = (array)$request_data;
		$this->form_validation->set_rules('email', 'Email', 'required|valid_email');
		$this->form_validation->set_data($operate_data);

		if (!$this->form_validation->run()) {
			$errors = $this->form_validation->error_array();
			$response = ['status'=>false,'msg' => implode(', ', $errors)];
			echo json_encode($response);
			exit();
		}

		$check_user = $this->CommonModel->getDataWhere(
			['email', 'person_id'],
			['email'=> $request_data->email, 'archive'=>0],
			TBL_PREFIX.'admin_user',
			['result_type'=>3]
		);

		if(empty($check_user)){
			echo json_encode(['status'=>false, 'msg' => 'No user is registered with given mail address.']);
			exit();
		}

		$arr = json_encode([
			'datetime' => strtotime(date(DATE_TIME_FORMAT)),
			'user_email'=> $check_user->email
		]);
		$token = encrypt_decrypt('encrypt', $arr);
		$url  = FRONT_URL.'admin/reset_password/'.urlencode(base64_encode($token));

		$userData = $this->CommonModel->getDataWhere(
			['firstname'],
			['id'=> $check_user->person_id, 'archive'=>0],
			TBL_PREFIX.'person',
			['result_type'=>3]
		);

		//Update Token
		$this->CommonModel->UpdateData(
			['email'=>$check_user->email, 'archive'=>0],
			['token'=>$token, 'token_status'=>1],
			TBL_PREFIX.'admin_user'
		);

		$data = [
			'username' =>$userData->firstname,
			'email'=>$check_user->email,
			'url'=>$url
		];
		$mail = forgot_password_mail($data);
		if($mail){
			$response=['status' => true, 'msg' =>'Email Verification Link has been Sent to you Registered Email'];
		}else{

			$response=['status' => false, 'msg' =>'mail send failed! Please try again.'];
		}
		echo json_encode($response);
		exit();
	}

	public function reset_password(){

		$request_body = get_json_data(false,['user_type'=>'admin']);
		$request_data = $request_body->data;
		$operate_data = (array)$request_data;
		$this->form_validation->set_rules('password', 'New Password', 'required|trim|callback_valid_password|max_length[100]');
			$this->form_validation->set_rules('confirm_password', 'Confirm Password', 'required|trim|matches[password]');
		$this->form_validation->set_data($operate_data);

		if (!$this->form_validation->run()) {
			$errors = $this->form_validation->error_array();
			$response = ['status'=>false,'msg' => implode(', ', $errors)];
			echo json_encode($response);
			exit();
		}

		$token = $request_data->token ?? '';
		$decodedtoken = base64_decode(urldecode($token));
		$decrypt_token = json_decode(encrypt_decrypt('decrypt', $decodedtoken), true);

		if(!is_array($decrypt_token)){
			echo json_encode(['status'=>false,'msg' => 'Invalid Token!']);
			exit();
		}

		$check_token = $this->CommonModel->getDataWhere(
			['token', 'email', 'id'],
			['token'=> $decodedtoken, 'token_status'=>1],
			TBL_PREFIX.'admin_user',
			['result_type'=>3]
		);

		if(empty($check_token)){
			echo json_encode(['status'=>false,'msg' => 'Invalid Token! Please go to forgot password again.']);
			exit();
		}

		$current_time = strtotime(date(DATE_TIME_FORMAT));
		if(($current_time - $decrypt_token['datetime'])  > RESETLINK_EXPIRETIME){
			echo json_encode(['status'=>false,'msg' => 'Link expired! Please go to forgot password again.']);
			exit();
		}

		$encrypt_password =  password_hash(trim($request_data->password), PASSWORD_BCRYPT);
		//Update Password
		$this->CommonModel->UpdateData(
			['token'=>$decodedtoken, 'archive'=>0],
			['token'=>'', 'token_status'=>0, 'password'=>$encrypt_password],
			TBL_PREFIX.'admin_user'
		);

		$this->CommonModel->UpdateData(
			['archive'=>0, 'user_id' =>$check_token->id],
			['archive'=>1],
			TBL_PREFIX.'admin_user_login_history'
		);

		echo json_encode(['status'=>true,'msg' => 'Password updated successfully.']);
		exit();


	}


	public function verify_reset_token(){

		$request_body = get_json_data(false,['user_type'=>'admin']);
		$request_data = $request_body->data;
		$token = $request_data->token ?? '';
		$decodedtoken = base64_decode(urldecode($token));
		$decrypt_token = json_decode(encrypt_decrypt('decrypt', $decodedtoken), true);

		if(!is_array($decrypt_token)){
			echo json_encode(['status'=>false,'msg' => 'Invalid Token!']);
			exit();
		}

		$check_token = $this->CommonModel->getDataWhere(
			['token', 'email', 'id'],
			['token'=> $decodedtoken, 'token_status'=>1],
			TBL_PREFIX.'admin_user',
			['result_type'=>3]
		);

		if(empty($check_token)){
			echo json_encode(['status'=>false,'msg' => 'Invalid Token!']);
			exit();
		}

		$current_time = strtotime(date(DATE_TIME_FORMAT));
		if(($current_time - $decrypt_token['datetime'])  > RESETLINK_EXPIRETIME){
			echo json_encode(['status'=>false,'msg' => 'Link expired! Please go to forgot password again.']);
			exit();
		}

		echo json_encode(['status'=>true,'msg' => 'Token verified.']);
		exit();

	}


	public function change_password(){

		$request_body = get_json_data(true,['user_type'=>'admin']);
		$request_data = $request_body->data;
        $operate_data = (array) $request_data;
         

		$this->form_validation->set_rules('current_password', 'Current Password', 'required');
		$this->form_validation->set_rules('password', 'New Password', 'required|trim|callback_valid_password|max_length[100]');
		$this->form_validation->set_rules('confirm_password', 'Confirm Password', 'required|trim|matches[password]');
		$this->form_validation->set_data($operate_data);


		if (!$this->form_validation->run()) {
			$errors = $this->form_validation->error_array();
			$response = array('status' => false, 'msg' => implode(', ', $errors));
			echo json_encode($response);
			exit();
		}

		if($request_data->current_password == $request_data->password){
			$response = array('status' => false, 'msg' => 'Current password and new password should not be same.');
			echo json_encode($response);
			exit();
		}

		$check_user = $this->CommonModel->getDataWhere(
			['password'],
			['id'=>$request_body->user_id, 'archive'=>0],
			TBL_PREFIX.'admin_user',
			['result_type'=>3]
		);


		if(!password_verify($request_data->current_password, $check_user->password )){
			$response = array('status' => false, 'msg' => 'Current password does not match.');
			echo json_encode($response);
			exit();
		}

		$encrypt_password =  password_hash(trim($request_data->password), PASSWORD_BCRYPT);

		$this->CommonModel->UpdateData(
			['id'=>$request_body->user_id, 'archive'=>0],
			['password'=>$encrypt_password],
			TBL_PREFIX.'admin_user'
		);

		$this->CommonModel->UpdateData(
			['token !='=>$request_body->token, 'archive'=>0, 'user_id' =>$request_body->user_id],
			['archive'=>1],
			TBL_PREFIX.'admin_user_login_history'
		);

		echo json_encode(['status' => true, 'msg' => 'Password Updated successfully.']);
		exit();

	}



	public function email_check($str)
	{

		$checkEmail =  $this->CommonModel->getDataWhere(
			['id'],
			['email'=>$str, 'archive'=>0],
			TBL_PREFIX.'admin_user',
			['result_type' => '3']
		);

		if (!empty($checkEmail))
		{
			$this->form_validation->set_message('email_check', 'The given email already exists.');
			return FALSE;
		} else {
			return TRUE;
		}
	}

 
	public function check_mailer(){
		$data = [
			'email' => 'cyclewalamohammad@gmail.com',
			'url' => 'fdaf',
			'username' => 'Mohammad'
		];
		activate_account_mail($data);
		echo "hello";
	}
}
