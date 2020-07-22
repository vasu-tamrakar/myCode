<?php defined('BASEPATH') || exit('No direct script access allowed');

class UserModel extends CI_Model
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('StatementModel');
		$this->load->library('Notification');
	}

	public function insertRegisteredUser($request_data)
	{
		$encrypt_password =  password_hash(trim($request_data->password), PASSWORD_BCRYPT);
		require_once APPPATH . 'classes/person/person.php';


		$TimezoneDetails = getPrimaryTimezoneFromCountryId($request_data->country);
		

		$objPerson = new PersonClass\Person();

		// create person
		$firstName = strip_tags($request_data->firstname??'');
		$lastName = strip_tags($request_data->lastname??'');
		$objPerson->setFirstName($firstName);
		$objPerson->setLastName($lastName);
		$objPerson->setPersonTypeIdByKey('user');
		$personId = $objPerson->createPerson();

		$objPerson->setPersonEmail([(object)['email'=>strip_tags($request_data->email)]]);
        $objPerson->insertEmail('email');

        $objPerson->setPersonCountry($request_data->country);
        $objPerson->insertAddress();

    
		$data = array(
			'person_id'=>$personId,
			'email' => strip_tags($request_data->email),
			'password' => $encrypt_password,
			'created' => create_date_store_in_db(),
			'user_timezone'=>isset($TimezoneDetails)? $TimezoneDetails->id : 0,
			'status' => 0
		);
		$insertData = $this->CommonModel->insertData($data, TBL_PREFIX.'user');

		if(empty($insertData) || $insertData < 1){
			return false;
		}
		
		$token = array(
	       'datetime' => strtotime(date(DATE_TIME_FORMAT)),
	       'user_email'=> $request_data->email
	    );
	    $token = encrypt_decrypt('encrypt', json_encode($token));
	 
		$url  = FRONT_URL.'user/activate_account/'.urlencode(base64_encode($token));

		$mail_data = [
			'email' => $data['email'],
			'username' => $firstName . ' ' . $lastName,
			'url' => $url
		];

		activate_account_mail($mail_data);
		$this->loges->setActivityType('new_registration');
		$this->loges->setDescription(json_encode($request_data));
		$this->loges->setTableID($insertData);
		$this->loges->setCreatedBy($insertData);
		$this->loges->setCreatedType(1);
		$this->loges->createLog();
		
        $this->notification->setAlertTitle("New user ".$firstName . ' ' . $lastName." added, for approval.");
        $this->notification->setAlertType(2);
        $this->notification->setUserId(0);
        $this->notification->setDescription("New user ".$firstName . ' ' . $lastName." added, for approval.");
        $this->notification->setIsRead(2);
        $this->notification->setNotificationCreated(create_date_store_in_db());
        $this->notification->setCreatedByType(1);
        $this->notification->setCreatedBy($insertData);
        $this->notification->setNotificationArchive(0);
        $this->notification->SaveUserNotificationAlert();
		return true;

	}

	public function loginUser($request_data){

		$whereArray = [
			'email' => $request_data->email,
			'archive' => 0
		];
		$user_data = $this->CommonModel->getDataWhere(['id', 'password', 'email_verify', 'status'], $whereArray, TBL_PREFIX.'user', ['result_type' => '3']);

		if(empty($user_data)){
			return false;
		}

		if(!password_verify($request_data->password, $user_data->password )){
			return false;
		}

		if($user_data->email_verify == 1){
			echo json_encode(['status'=>false, 'msg'=>'Your email is not verified yet. Please check your mailbox for the verification link or contact the admin.']);
			exit();
		}

		if($user_data->status != 1){
			echo json_encode(['status'=>false, 'msg'=>'Your account is not activated. Please contact admin for approval.']);
			exit();
		}


		$tokenData = array('id'=> $user_data->id, 'time'=> DATE_TIME);
		$token= AUTHORIZATION::generateToken($tokenData);
		$data = array(
			"user_id" => $user_data->id,
			"login_time" => DATE_TIME,
			"logout_time" => date(DATE_TIME_FORMAT, strtotime("+60 minutes")),
			"ip_address" => get_client_ip_server(),
			"user_agent" => get_user_agent(),
			"token" => $token
		);

 

		$this->CommonModel->insertData($data, TBL_PREFIX.'user_login_history');

		return $token;


	}


	public function getUserId($request_body)
	{
		$email=$request_body->data->email;
		$query = $this ->db->select('id')
		-> where('email', $email)
		-> get(TBL_PREFIX.'user');
		$res = $query->row();
		if($query->num_rows() > 0)
		{
			return $res->id;
		}
		else
		{
			return false;
		}
	}

	public function deleteToken($request_body){
		$token=$request_body->token;
		$this->db->delete(TBL_PREFIX.'user_login_history', array('token' => $token));
		if ( $this->db->affected_rows() )
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	public function updateToken($data,$request_body){
		$id=$request_body->id;
		$this->db->where('user_id', $id);
		$this ->db->update(TBL_PREFIX.'user_login_history',$data);
		if ( $this->db->affected_rows() )
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	public function forgotPassword($request_body,$token)
	{
		$email=$request_body->data->email;
		$array = array('id' ,'email','firstname');
		$query = $this ->db->select($array)
		-> where('email', $email)
		-> get(TBL_PREFIX.'user');
		if($query->num_rows() > 0)
		{
			$res = $query->row();
			if($token==null){
				return $res;
			}
			else{
				$this->db->where(array('id' => $res->id));
				$this ->db->update(TBL_PREFIX.'user',array('token' => $token));
				return $res;
			}
		}
		else
		{
			return false;
		}

	}
	public function resetPassword($data,$id,$token){

		$que = $this ->db->select('id',$id)
		-> where('id',$id)
		-> get(TBL_PREFIX.'user');
		if($que->num_rows() > 0)
		{
			//check whether request
			$array = array('id' => $id,'token' => $token);
			$this->db->where($array);
			$query = $this ->db->update(TBL_PREFIX.'user',$data);

			if ( $this->db->affected_rows() )
			{
				return TRUE;
			}
			else
			{
				return FALSE;
			}
		}
		else
		{
			return "Invalid ID";
		}
	}
	public function changePasswordCheck($query,$request_data){
		$id=$query;
		$this->db->select('password');
		$this->db->where('id',$id);
		$query = $this->db->get(TBL_PREFIX.'user');
		if($query->num_rows() > 0)
		{
			foreach($query->result() as $row)
			{
				if($row->password)
				{
					$store_password = $row->password;
					if(password_verify($request_data->current_password, $store_password ))
					{
						return true;
					}
					else
					{
						return false;
					}
				}
				else
				{
					return false;
				}
			}
		}
		else
		{
			return false;
		}
		return $query->result();
	}
	public function getUserIdFromToken($token){

		$que = $this->db->select('user_id')
		->where('token',$token)
		->get(TBL_PREFIX.'user_login_history');
		$query = $que->row();
		return $query->user_id;
	}
	public function expireLink($data,$id){
		$que = $this ->db->select('id',$id)
		-> where('id',$id)
		-> get(TBL_PREFIX.'user');
		if($que->num_rows() > 0)
		{
			$array = array('id' => $id, 'token' => '1');
			$this->db->where($array);
			$query = $this ->db->update('tbl_users',$data);
			if ( $this->db->affected_rows() )
			{
				return TRUE;
			}
			else
			{
				return FALSE;
			}
		}
	}
	public function userInfo($request_body)
	{
		$id=$request_body->id;
		$que = $this ->db->select('user_id',$id)
		-> where('user_id', $id)
		-> get(TBL_PREFIX.'user_login_history');
		if($que->num_rows() > 0)
		{
			$query = $this ->db->select('logout_time')
			-> where('user_id', $id)
			-> get(TBL_PREFIX.'user_login_history');
			$expire = $query->row();
			return $expire->logout_time;
		}
		else
		{
			return false;
		}
	}

	public function getRows($params)
	{
		$this->db->select('*');
		$this->db->where('email',$params);
		$this->db->from(TBL_PREFIX."user");
		$query = $this->db->get();
		return $query->num_rows();

	}


	public function get_countries_list_model(){

		$this->db->select(['c.id as value', 'c.country_name as label']);
		$this->db->where(["c.archive"=>0]);
		$this->db->from(TBL_PREFIX.'country c' );
		$query = $this->db->get();
		$result = $query->num_rows() > 0 ? $query->result_array():[];

		return $result;

	}


	



}
