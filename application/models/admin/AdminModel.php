<?php defined('BASEPATH') || exit('No direct script access allowed');

class AdminModel extends CI_Model
{
	function __construct()
	{
		parent::__construct(); 
    }
    
    public function insertRegisteredAdminUser($request_data)
	{
		$encrypt_password =  password_hash(trim($request_data->password), PASSWORD_BCRYPT);
		require_once APPPATH . 'classes/person/person.php';
		$objPerson = new PersonClass\Person();
		$firstName = strip_tags($request_data->firstname??'');
		$lastName = strip_tags($request_data->lastname??'');
		$objPerson->setFirstName($firstName);
		$objPerson->setLastName($lastName);
		$objPerson->setPersonTypeIdByKey('user');
		$personId = $objPerson->createPerson();
		$objPerson->setPersonEmail([(object)['email'=>strip_tags($request_data->email)]]);
        $objPerson->insertEmail('email');
		$data = array(
			'person_id'=>$personId,
			'email' => strip_tags($request_data->email),
			'password' => $encrypt_password,
			'created' => create_date_store_in_db()
		);
		$insertData = $this->CommonModel->insertData($data, TBL_PREFIX.'admin_user');

		if(empty($insertData) || $insertData < 1){
			return false;
		}

		$token = array(
	       'datetime' => strtotime(date(DATE_TIME_FORMAT)),
	       'user_email'=> $request_data->email
	    );
	    $token = encrypt_decrypt('encrypt', json_encode($token));
	 
		$url  = FRONT_URL.'admin/activate_account/'.urlencode(base64_encode($token));

		$mail_data = [
			'email' => $data['email'],
			'username' => $firstName . ' ' . $lastName,
			'url' => $url
		];

		activate_account_mail($mail_data);
		return true;

	}
 
	public function loginAdminUser($request_data){

		$whereArray = [
			'email' => $request_data->email,
			'archive' => 0
		];
		$user_data = $this->CommonModel->getDataWhere(['id', 'password', 'email_verify'], $whereArray, TBL_PREFIX.'admin_user', ['result_type' => '3']);

		if(empty($user_data)){
			return false;
		}

		if(!password_verify($request_data->password, $user_data->password )){
			return false;
		}

		if($user_data->email_verify == 1){
			echo json_encode(['status'=>false, 'msg'=>'Your account is not activated']);
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
 

		$this->CommonModel->insertData($data, TBL_PREFIX.'admin_user_login_history');

		return $token;


	}


	public function getUserId($request_body)
	{
		$email=$request_body->data->email;
		$query = $this ->db->select('id')
		-> where('email', $email)
		-> get(TBL_PREFIX.'admin_user');
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
		$this->db->delete(TBL_PREFIX.'admin_user_login_history', array('token' => $token));
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
		$this ->db->update(TBL_PREFIX.'admin_user_login_history',$data);
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
		-> get(TBL_PREFIX.'admin_user');
		if($query->num_rows() > 0)
		{
			$res = $query->row();
			if($token==null){
				return $res;
			}
			else{
				$this->db->where(array('id' => $res->id));
				$this ->db->update(TBL_PREFIX.'admin_user',array('token' => $token));
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
		-> get(TBL_PREFIX.'admin_user');
		if($que->num_rows() > 0)
		{
			//check whether request
			$array = array('id' => $id,'token' => $token);
			$this->db->where($array);
			$query = $this ->db->update(TBL_PREFIX.'admin_user',$data);

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
		$query = $this->db->get(TBL_PREFIX.'admin_user');

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
		->get(TBL_PREFIX.'admin_user_login_history');
		$query = $que->row();
		return $query->user_id;
	}
	public function expireLink($data,$id){
		$que = $this ->db->select('id',$id)
		-> where('id',$id)
		-> get(TBL_PREFIX.'admin_user');
		if($que->num_rows() > 0)
		{
			$array = array('id' => $id, 'token' => '1');
			$this->db->where($array);
			$this ->db->update('tbl_users',$data);
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
		-> get(TBL_PREFIX.'admin_user_login_history');
		if($que->num_rows() > 0)
		{
			$query = $this ->db->select('logout_time')
			-> where('user_id', $id)
			-> get(TBL_PREFIX.'admin_user_login_history');
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
		$this->db->from(TBL_PREFIX."admin_user");
		$query = $this->db->get();
		return $query->num_rows();

	}

	public function getAdminUserDetails($request_body){
		 
		$userId = $request_body->user_id;
		$personId = $this->CommonModel->getDataWhere(
        	['person_id'],
        	['id'=>$userId, 'archive'=>0],
        	TBL_PREFIX.'admin_user',
        	['result_type'=>3]
		);
		require_once APPPATH . 'classes/person/person.php';
		$objPerson = new PersonClass\Person();
		$objPerson->setPersonId($personId->person_id);
		$objPerson->setPersonType(3);
		return $objPerson->getPersonDetails(); 
	 }
	 
	 public function updateAdminDetails($request_data){
		$request_all_data = $request_data->data;
		$userId = $request_data->user_id;
		$personDetails= $this->CommonModel->getDataWhere(
        	['person_id'],
        	['id'=>$userId, 'archive'=>0],
        	TBL_PREFIX.'admin_user',
        	['result_type'=>3]
		);
		$personId = $personDetails->person_id;
		require_once APPPATH . 'classes/person/person.php';
		$objPerson = new PersonClass\Person();
		$objPerson->setPersonId($personId);
		if(!empty($request_all_data->phone)){  
			$objPerson->setPersonPhone([(object)['phone'=>strip_tags($request_all_data->phone)]]);
			$objPerson->updatePhone();
		  }
		  if(!empty($request_all_data->email)){
			$objPerson->setPersonEmail([(object)['email'=>strip_tags($request_all_data->email)]]);
			$objPerson->updateEmail();
		  }
		  if(!empty($request_all_data->address)){
			$objPerson->setPersonStreet($request_all_data->address);
			$objPerson->setPersonPostal($request_all_data->postal);
			$objPerson->setPersonState($request_all_data->state);
			$objPerson->setPersonCity($request_all_data->suburb);
			$objPerson->setPersonCountry($request_all_data->country);
			$objPerson->updateAddress();
		  }
		  if(!empty($request_all_data->firstname)||!empty($request_all_data->lastname)){
			$firstName = strip_tags($request_all_data->firstname??'');
			$lastName = strip_tags($request_all_data->lastname??'');
			$objPerson->setFirstName($firstName);
			$objPerson->setLastName($lastName);
			$objPerson->updatePerson();
		  }

	$TimezoneDetails = getPrimaryTimezoneFromCountryId($request_all_data->country);
    $this->CommonModel->UpdateData(
      ['person_id'=>$personId, 'archive' => 0],
      [
        'email' => strip_tags($request_all_data->email),
        'user_timezone'=>isset($TimezoneDetails)? $TimezoneDetails->id : 0
      ],
      TBL_PREFIX.'admin_user'
   	);
		return true;
	 }





}
