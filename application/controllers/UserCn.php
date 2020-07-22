<?php
defined('BASEPATH')  || exit('No direct script access allowed');

require_once APPPATH . 'traits/formCustomValidation.php';

class UserCn extends CI_Controller
{
	use formCustomValidation;

	function __construct()
	{
		parent::__construct();
		$this->load->library('form_validation');
		$this->load->model('UserModel');
		$this->load->library('Notification');
	}

	public function user_register()
	{

		$request_body = get_json_data(false);
		$request_data = $request_body->data;
		$operate_data = (array)$request_data;

		$this->form_validation->set_rules('firstname', 'First Name', 'required|trim|min_length[2]|max_length[50]');
		$this->form_validation->set_rules('lastname', 'Last Name', 'required|trim');
		$this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|callback_email_check|max_length[64]');
		$this->form_validation->set_rules('country', 'Country', 'required|trim');
		$this->form_validation->set_rules('password', 'Password', 'required|trim|callback_valid_password|max_length[100]');
		$this->form_validation->set_rules('confirm_password', 'Password Confirmation', 'required|trim|matches[password]');
		$this->form_validation->set_data($operate_data);

		if (!$this->form_validation->run()) {
			$errors = $this->form_validation->error_array();
			$response = ['status' => false, 'msg' => implode(', ', $errors)];
			echo json_encode($response);
			exit();
		}

		$register = $this->UserModel->insertRegisteredUser($request_data);
		if($register){
			$response = ['status' => true, 'msg' => 'User registerd successfully! We have sent an email to verify your account.'];
		}else{
			$response = ['status' => false, 'msg' => 'Registration failed!'];
		}

		echo json_encode($response);
		exit();

	}


	public function activate_account(){

		$request_body = get_json_data(false);
		$request_data = $request_body->data;

		$decodedtoken = base64_decode(urldecode($request_data->key));
		$decodArray = (array)json_decode(encrypt_decrypt('decrypt', $decodedtoken));


		if(!empty($decodArray) && is_array($decodArray) && array_key_exists('user_email', $decodArray)){

			$getCols = array('id', 'email_verify');
			$whereArray = array(
				'email' => $decodArray['user_email'],
				'archive' => 0
			);
			$checkVerification = $this->CommonModel->getDataWhere($getCols, $whereArray, TBL_PREFIX.'user', ['result_type' => '3']);

			$response = "";
			if(!empty($checkVerification)){

				if($checkVerification->email_verify == 1){
					//Activate User Account 
					$this->CommonModel->updateData($whereArray, array('email_verify'=>0),TBL_PREFIX.'user');

					$response = array('status' => true, 'message' => 'Account activated ! Please login now');

				}
				else{
					$response = array('status' => false, 'message' => 'Link expired!!');
				}

			}
			else{
				$response = array('status' => false, 'message' => 'Invalid request');
			}
		}
		else{
			$response = array('status' => false, 'message' => 'Invalid url request');
		}
		echo json_encode($response);
		exit();

	}

	public function login_user(){
		$request_body = get_json_data(false);
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

		$loginUser = $this->UserModel->loginUser($request_data);
		if($loginUser){
			$response = ['status'=>true,'token'=> $loginUser,'msg' =>"login successful"];
		}else{
			$response = ['status'=>false,'msg'=>'Invalid Email or Password'];
		}
		echo json_encode($response);
		exit();


	}


	public function logout_user()
	{
		$request_body = get_json_data(true,['user_type'=>'user']);
		$res = $this->UserModel->deleteToken($request_body);
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
			$result = $this->UserModel->userInfo($request_body);

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
					//Update Token
					$this->UserModel->updateToken($data,$request_body);
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
		$request_body = get_json_data(false);
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
			TBL_PREFIX.'user',
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
		$url  = FRONT_URL.'user/reset_password/'.urlencode(base64_encode($token));

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
			TBL_PREFIX.'user'
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

		$request_body = get_json_data(false);
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
			TBL_PREFIX.'user',
			['result_type'=>3]
		);

		

		// if(empty($check_token)){
		// 	echo json_encode(['status'=>false,'msg' => 'Invalid Token! Please go to forgot password again.']);
		// 	exit();
		// }

		$current_time = strtotime(date(DATE_TIME_FORMAT));
		if(($current_time - $decrypt_token['datetime'])  > RESETLINK_EXPIRETIME || empty($check_token)){
			echo json_encode(['status'=>false,'msg' => 'Link expired! Please go to forgot password again.']);
			exit();
		}

		$encrypt_password =  password_hash(trim($request_data->password), PASSWORD_BCRYPT);
		//Update Password 
		$this->CommonModel->UpdateData(
			['token'=>$decodedtoken, 'archive'=>0],
			['token'=>'', 'token_status'=>0, 'password'=>$encrypt_password],
			TBL_PREFIX.'user'
		);

		$this->CommonModel->UpdateData(
			['archive'=>0, 'user_id' =>$check_token->id],
			['archive'=>1],
			TBL_PREFIX.'user_login_history'
		);

		$logtype = (isset($decrypt_token['setnewpass']) && ($decrypt_token['setnewpass'] == 1))?'set_new_password':'reset_password';
		$this->loges->setActivityType($logtype);
		$this->loges->setDescription(json_encode($request_body));
		$this->loges->setTableID($check_token->id);
		$this->loges->setCreatedBy($check_token->id);
		$this->loges->setCreatedType(1);
		$this->loges->createLog();
		echo json_encode(['status'=>true,'msg' => 'Password updated successfully.']);
		exit();


	}


	public function change_password(){

		$request_body = get_json_data(true,['user_type'=>'user']);
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
			TBL_PREFIX.'user',
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
			TBL_PREFIX.'user'
		);

		$this->CommonModel->UpdateData(
			['token !='=>$request_body->token, 'archive'=>0, 'user_id' =>$request_body->user_id],
			['archive'=>1],
			TBL_PREFIX.'user_login_history'
		);

		$this->loges->setActivityType('change_password');
		$this->loges->setDescription(json_encode($request_body));
		$this->loges->setTableID($request_body->user_id);
		$this->loges->setCreatedBy($request_body->user_id);
		$this->loges->setCreatedType(1);
		$this->loges->createLog();

		echo json_encode(['status' => true, 'msg' => 'Password Updated successfully.']);
		exit();

	}



	public function email_check($str)
	{

		$checkEmail =  $this->CommonModel->getDataWhere(
			['id'],
			['email'=>$str, 'archive'=>0],
			TBL_PREFIX.'user',
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

	public function runPythonScript(){
		$userData = get_json_data(true,['user_type'=>'user']);
		if ($userData->user_id){
			$user_id = $userData->user_id; 
			$checkStatusToRunPython  = $this->checkStatusToRunPython($user_id); 
			if(!empty($checkStatusToRunPython)){
				$command1 = PYTHON_CALL.PYTHON_COMMAND1.$user_id." 2>/dev/null &";
				shell_exec($command1);    
				$command2 = PYTHON_CALL.PYTHON_COMMAND2.$user_id." 2>/dev/null &";   
				shell_exec($command2);  
				if(!empty($user_id)){
					$response= array('status' => true, 'data'=>'We will notify you once Statement/Invoice AI analysis is completed');
				} else{
					$response= array('status' => false, 'data'=>'something went wrong');
				} 
			} else {
				$response= array('status' => true, 'data'=>'No Statement/Invoice are pending to read');
			}
			echo json_encode($response);
			exit;
		}
	}

	function checkStatusToRunPython($userId){
		$whereArr = ['s.user_id'=>$userId, 'sli.read_status'=>1, 's.archive'=>0]; 
		$this->db->select('sli.id');
		$this->db->where($whereArr);
		$this->db->join(TBL_PREFIX.'statement_line_item as sli', 'sli.statement_id = s.id AND sli.archive=s.archive', 'inner');
		$this->db->from(TBL_PREFIX.'statement s');
		$query =  $this->db->get();
		return $query->result_array();
	}

	function NotificationSendOnceBackroundDone($userId){
		// Notifiaction Of Python Script
		$title_or_desc = 'Statement/Invoice AI analysis is completed';
		$this->notification->setAlertTitle($title_or_desc);
		$this->notification->setAlertType(1);
		$this->notification->setUserId($userId);
		$this->notification->setDescription($title_or_desc);
		$this->notification->setIsRead(2);
		$this->notification->setNotificationCreated(create_date_store_in_db());
		$this->notification->setCreatedByType(2);
		$this->notification->setCreatedBy(0);
		$this->notification->setNotificationArchive(0);
		$this->notification->SaveUserNotificationAlert();
	}
	

	function add_user_mapping($userId){
		
		require_once APPPATH . 'classes/VendorUserMapping.php';
		$objVendorUserMapping = new VendorUserMapping($userId);
		$objVendorUserMapping->setUserId($userId);
		$objVendorUserMapping->addVendorUserMappingByPythonAdd();
  

		//Logs of User Mapping
		$title_desc='User mapping call updated by python';
		$this->loges->setTitle($title_desc);
		$this->loges->setDescription(json_encode($title_desc));
		$this->loges->setTableID($userId);
		$this->loges->setCreatedBy($userId);
		$this->loges->setCreatedType(4);
		$this->loges->createLog();

		$this->NotificationSendOnceBackroundDone($userId); 
		
		$response= array('status' => true, 'msg'=>'script run finish');
		echo json_encode($response);
		exit;

	}

	function findDuplicate($userId){
		require_once APPPATH . 'classes/FindDuplicate.php';
		$objFindDuplicate = new FindDuplicate($userId);
		$objFindDuplicate->findDuplicateLineItem();
		$title_desc='Find Duplicate call updated by python';
		$this->loges->setDescription($title_desc);
		$this->loges->setTableID($userId);
		$this->loges->setCreatedBy($userId);
		$this->loges->setCreatedType(4);
		$this->loges->createLog();
		$response= array('status' => true, 'msg'=>'script run finish');
		echo json_encode($response);
		exit;
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


	public function get_countries_list(){
		$request_body = get_json_data(false);
		$list = $this->UserModel->get_countries_list_model();
		$response = ['status'=>true,  'data'=>$list ];
		echo json_encode($response);
		exit();  

	}

	public function get_country_shortcode(){
		$request_body = get_json_data(false);
		$request_data = $request_body->data;
		$data = $this->CommonModel->getDataWhere(
			['country_code'],
			['id'=>$request_data->country, 'archive'=>0],
			TBL_PREFIX.'country',
			['result_type'=>3]

		);
		if($data){
			$response = ['status'=>true,  'data'=>$data ];
		}else{
			$response = ['status'=>true,  'msg'=>'No country found'];
		}
		echo json_encode($response);
		exit();  
	}
}
