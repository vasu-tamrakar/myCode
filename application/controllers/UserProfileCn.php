<?php
defined('BASEPATH') or exit('No direct script access allowed');

class UserProfileCn extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('UserProfileModel');
	}


	public function updateProfilePicOld()
	{
		$request_body = get_form_data(true);
		$Id = $request_body->user_id;

        $config['upload_path'] = FCPATH . '/uploads/';
        $config['directory_name'] = 'user_profile';
        $config['max_size'] = '100000';
        $config['input_name'] = 'file';
        $config['allowed_types'] = 'jpg|png|jpeg|JPEG';

        $uploads = do_upload($config);

        if (isset($uploads['error'])) {
            echo json_encode(array('status' => false, 'error' => strip_tags($uploads['error'])));
            exit();
        } 

        $getCurrentImage = $this->CommonModel->getDataWhere(
        	['profile_image'],
        	['id'=>$Id, 'archive'=>0],
        	TBL_PREFIX.'user',
        	['result_type'=>3]
        );

        $updateNewImage = $this->CommonModel->UpdateData(
        	['id'=>$Id, 'archive'=>0],
        	['profile_image'=>$uploads['upload_data']['file_name']],
        	TBL_PREFIX.'user'
        );

        if ($updateNewImage > 0) {

        	if($getCurrentImage !== ''){
		       	// old image delete if there is any
		        $dbpathFile = $config['upload_path'] . $config['directory_name'] . '/' . $getCurrentImage->profile_image;
		        if (file_exists($dbpathFile) && is_file($dbpathFile)) {
		                unlink($dbpathFile);
		         }
	        }
	        
	        echo json_encode(['status' => true, 'msg' => 'Pic updated successfully.']);
	        exit();
        }else{

        	$newImg = $config['upload_path'] . $config['directory_name'] . '/' . $uploads['upload_data']['file_name'];
            if (file_exists($newImg) && is_file($newImg)) {
                unlink($newImg);
            }
            echo json_encode(['status' => false, 'msg' => 'Please try again.']);
            exit();
        }
	}



	public function userProfileDetails(){
		$request_body = get_json_data(true);
		$user_id = $request_body->user_id;

		// $getData = 
	}

	public function updateProfilePic()
	{
        $request_body = request_handlerFile(true);
		$Id = $request_body->user_id;
        $config['upload_path'] = UPLOADS; 
        //FCPATH .  '/uploads/';
        $config['directory_name'] = 'user_profile';
        $config['max_size'] = '100000';
        $config['input_name'] = 'file';
        $config['file_name'] = 'fm_user'.time();
        $config['allowed_types'] = 'jpg|png|jpeg|JPEG';
        $uploads = do_upload($config);
        
        if (isset($uploads['error'])) {
            echo json_encode(array('status' => false, 'error' => strip_tags($uploads['error'])));
            exit();
        } 
        $this->load->library('image_lib');
        $config2 = array(
            'source_image'      => $uploads['upload_data']['full_path'],
            'new_image'         => UPLOADS.'user_profile/small/',
            'directory_name'    => 'small',
            'maintain_ratio'    => true,
            'width'             => 27,
            'height'            => 27
            );
        $this->image_lib->initialize($config2);
        $this->image_lib->resize();

        $getCurrentImage = $this->CommonModel->getDataWhere(
        	['profile_image'],
        	['id'=>$Id, 'archive'=>0],
        	TBL_PREFIX.'user',
        	['result_type'=>3]
        );
        $updateNewImage = $this->CommonModel->UpdateData(
        	['id'=>$Id, 'archive'=>0],
        	['profile_image'=>$uploads['upload_data']['file_name']],
        	TBL_PREFIX.'user'
        );
        if ($updateNewImage > 0) {
        	if($getCurrentImage !== ''){
		       	// old image delete if there is any
		        $dbpathFile = $config['upload_path'] . $config['directory_name'] . '/' . $getCurrentImage->profile_image;
		        if (file_exists($dbpathFile) && is_file($dbpathFile)) {
		                unlink($dbpathFile);
                 }
                 
                 // old image delete if there is any thumb image
		        $dbpathFile = $config2['new_image'] . $getCurrentImage->profile_image;
		        if (file_exists($dbpathFile) && is_file($dbpathFile)) {
		                unlink($dbpathFile);
		         }
	        }
	        echo json_encode(['status' => true, 'msg' => 'Pic updated successfully.']);
	        exit();
        }else{
        	$newImg = $config['upload_path'] . $config['directory_name'] . '/' . $uploads['upload_data']['file_name'];
            if (file_exists($newImg) && is_file($newImg)) {
                unlink($newImg);
            }
            echo json_encode(['status' => false, 'msg' => 'Please try again.']);
            exit();
        }
    }


	public function getuserprofile(){
        $request_body = get_json_data(true);
        $userDetails = $this->UserProfileModel->getUserDetails($request_body);
        if(!empty($userDetails)){
            $response = array('status' => true, 'data' => $userDetails);
        }
         else {
            $response = array('status' => false, 'data' =>[]);
        }
        echo json_encode($response);
        exit();
	}
	
	public function updateProfile(){
		$request_body = get_json_data(true);
        $this->validation($request_body);
        $userUpdatedDetails = $this->UserProfileModel->updateUserDetails($request_body);
        if(!empty($userUpdatedDetails)){
            $response = array('status' => true, 'msg' => 'User Profile Updated Succesfully');
        }
         else {
            $response = array('status' => false, 'data' =>[]);
        }
        echo json_encode($response);
        exit();
    }

    public function validation($request_body){
        $request_data = $request_body->data;
        $id = $request_body->user_id;
        $operate_data = (array)$request_data;
        $type='edit';
        $this->form_validation->set_rules('firstname', 'First Name', 'required|trim|min_length[2]|max_length[50]');
		$this->form_validation->set_rules('lastname', 'Last Name', 'required|trim|min_length[2]|max_length[50]');
		$this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|max_length[64]|callback_field_check['. json_encode(['type' => $type, 'key'=> 'email', 'user_id'=>$id]) .']');
		$this->form_validation->set_rules('phone', 'Contact', 'required|trim|min_length[8]|max_length[16]|callback_field_check['. json_encode(['type' => $type, 'key'=> 'phone', 'user_id'=>$id]) .']');
		$this->form_validation->set_rules('country', 'Country', 'required|trim');
		$this->form_validation->set_rules('address', 'address', 'required|trim|max_length[255]');
		$this->form_validation->set_rules('suburb', 'suburb', 'required|trim|max_length[100]');
		$this->form_validation->set_rules('state', 'state', 'required|trim|max_length[20]');
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
		$whereArr=['id'=>$arr->user_id];
		$personData =   $this->CommonModel->getDataWhere(
			['person_id'],
			$whereArr,
			TBL_PREFIX.'user',
			['result_type'=>3]
		);
		$personId = $personData->person_id;
	 
		if($key == 'email'){
			$msg = "Email already exist with other user";
			if($type == 'edit'){
				$userWhereArr = ['email'=>$str, 'archive'=>0, 'person_id !='=>$personId];
				$personEmailWhereArr = ['email'=>$str, 'archive'=>0, 'person_id !='=>$personId];
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
				$personPhoneWhereArr = ['phone'=>$str, 'archive'=>0, 'person_id !='=>$personId, 'primary_phone'=>1];
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

}