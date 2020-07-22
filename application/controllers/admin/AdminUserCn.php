<?php
defined('BASEPATH') || exit('No direct script access allowed');

class AdminUserCn extends CI_Controller {

	function __construct()
	{
		parent::__construct();
        $this->load->library('form_validation','pagination');
        $this->load->model('admin/AdminUserModel');
    }
    
	function list_users(){
        $request_body = get_json_data(true,['user_type'=>'admin']);
		if(!empty($request_body->data))
		{
			$result = $this->AdminUserModel->listUsers($request_body);
			if(!empty($result)){
				$response_ary = array('status'=>true,'data' => $result );
			} else {
				$response_ary = array('status'=>false,'msg' => "No Record Found" );
			}
			echo json_encode($response_ary);
			exit();
		}
    }

    function single_user(){
        $request_body = get_json_data(true,['user_type'=>'admin']);

		if(!empty($request_body->data))
		{
			$result = $this->AdminUserModel->singleUserDetails($request_body);
			if(!empty($result)){
				$response_ary = array('status'=>true,'data' => $result);
			} else {
				$response_ary = array('status'=>false,'msg' => "No Record Found" );
			}
			echo json_encode($response_ary);
			exit();
		}
    }

    function update_user(){
        $request_body = get_json_data(true,['user_type'=>'admin']);
        $request_data = $request_body->data;
		
		$this->common_user_validation($request_data, 'edit');
			
		$result = $this->AdminUserModel->updateUser($request_body);
		if(!empty($result)){
			$response = array('status'=>true,'msg' => "Updated User Successfully");
		} else {
			$response = array('status'=>false,'msg' => "No Record Found" );
		}
		echo json_encode($response);
		exit();
		
	}
	
	function add_user(){
        $request_body = get_json_data(true,['user_type'=>'admin']);
        $request_data = $request_body->data;

        $this->common_user_validation($request_data, 'add');

		if(!empty($request_body->data))
		{
			$result = $this->AdminUserModel->addUser($request_body);
			if(!empty($result)){
				$response_ary = array('status'=>true,'msg' => "Added User Successfully");
			} else {
				$response_ary = array('status'=>false,'msg' => "No Record Found" );
			}
			echo json_encode($response_ary);
			exit();
		}
	}




	function common_user_validation($request_data, $type="add"){

		$operate_data = (array)$request_data;

		$id = null;
		if($type == 'edit'){
			$id = $request_data->person_id;
		}


		$this->form_validation->set_rules('firstname', 'First Name', 'required|trim|min_length[2]|max_length[50]');
		$this->form_validation->set_rules('lastname', 'Last Name', 'required|trim|min_length[2]|max_length[50]');
		$this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|max_length[64]|callback_field_check['. json_encode(['type' => $type, 'key'=> 'email', 'person_id'=>$id]) .']');
		$this->form_validation->set_rules('phone', 'Contact', 'required|trim|min_length[8]|max_length[16]|callback_field_check['. json_encode(['type' => $type, 'key'=> 'phone', 'person_id'=>$id]) .']');
		$this->form_validation->set_rules('country', 'Country', 'required|trim');
		$this->form_validation->set_rules('address', 'address', 'required|trim|max_length[255]');
		$this->form_validation->set_rules('suburb', 'suburb', 'required|trim|max_length[100]');
		$this->form_validation->set_rules('state', 'state', 'required|trim|max_length[100]');
		$this->form_validation->set_rules('postal', 'postal', 'required|trim|max_length[20]');

		$this->form_validation->set_data($operate_data);

		if (!$this->form_validation->run()) {
			$errors = $this->form_validation->error_array();
			$response = ['status' => false, 'msg' => implode(', ', $errors)];
			echo json_encode($response);
			exit();
		}
	}

		public function field_check($str, $Arr)
	{

		$arr=json_decode($Arr);
		$type=$arr->type;
		$key=$arr->key;

		$msg = "";
		$valid = true;



		if($key == 'email'){
			$msg = "Email already exist with other user";


			if($type == 'edit'){
				$userWhereArr = ['email'=>$str, 'archive'=>0, 'person_id !='=>$arr->person_id];
				$personEmailWhereArr = ['email'=>$str, 'archive'=>0, 'person_id !='=>$arr->person_id];
			}else{
				$userWhereArr = ['email'=>$str, 'archive'=>0];
				$personEmailWhereArr = ['email'=>$str, 'archive'=>0];
			}
		
			$checkExistInUserTable = $this->CommonModel->getDataWhere(
				['id'],
				$userWhereArr,
				TBL_PREFIX.'user',
				['result_type'=>3]
			);

			$checkExistInPersonEmailTable = $this->CommonModel->getDataWhere(
				['id'],
				$personEmailWhereArr,
				TBL_PREFIX.'person_email',
				['result_type'=>3]
			);


			if(isset($checkExistInUserTable) || isset($checkExistInPersonEmailTable)){
				$valid = false;
			}



		}
		if($key == 'phone'){
			$msg = "Contact already exist with other user";

			if($type == 'edit'){
				$personPhoneWhereArr = ['phone'=>$str, 'archive'=>0, 'person_id !='=>$arr->person_id, 'primary_phone'=>1];
			}else{
				$personPhoneWhereArr = ['phone'=>$str, 'archive'=>0, 'primary_phone'=>1];
			}

			$checkExistInPersonPhoneTable = $this->CommonModel->getDataWhere(
				['id'],
				$personPhoneWhereArr,
				TBL_PREFIX.'person_phone',
				['result_type'=>3]
			);

			if(isset($checkExistInPersonPhoneTable)){
				$valid = false;
			}

		}

		if(!$valid){
			$this->form_validation->set_message('field_check', $msg);
			return FALSE;
		}else{
			return TRUE;
		}
		

	} 
	
	function delete_user(){
		$request_body = get_json_data(true,['user_type'=>'admin']);
		if(!empty($request_body->data))
		{
			$result = $this->AdminUserModel->deleteUser($request_body);
			if(!empty($result)){
				$response_ary = array('status'=>true,'msg' => "Deleted User Successfully");
			} else {
				$response_ary = array('status'=>false,'msg' => "No Record Found" );
			}
			echo json_encode($response_ary);
			exit();
		}
	}

	function single_user_activate(){
		$request_body = get_json_data(true,['user_type'=>'admin']);
		if(!empty($request_body->data))
		{
			$result = $this->AdminUserModel->single_user_activate($request_body);
			if(!empty($result)){
				$response_ary = array('status'=>true,'msg' => "Activated User Successfully");
			} else {
				$response_ary = array('status'=>false,'msg' => "No Record Found" );
			}
			echo json_encode($response_ary);
			exit();
		}
	}


	public function get_countries_list(){
		$request_body = get_json_data(false);
		$this->load->model('UserModel');
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