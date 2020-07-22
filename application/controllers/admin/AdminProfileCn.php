<?php
defined('BASEPATH') || exit('No direct script access allowed');

class AdminProfileCn extends CI_Controller {
    function __construct()
	{
		parent::__construct();
		$this->load->library('form_validation');
		$this->load->model('admin/AdminModel');
	}
	public function updateProfilePic()
	{
        $request_body = request_handlerFile(true,['user_type'=>'admin']);
		$Id = $request_body->user_id;
        $config['upload_path'] = UPLOADS;
        $config['directory_name'] = 'admin_profile';
        $config['max_size'] = '100000';
        $config['input_name'] = 'file';
        $config['file_name'] = 'fm_admin'.time();
        $config['allowed_types'] = 'jpg|png|jpeg|JPEG';
        $uploads = do_upload($config);
        if (isset($uploads['error'])) {
            echo json_encode(array('status' => false, 'error' => strip_tags($uploads['error'])));
            exit();
        } 
        $this->load->library('image_lib');
        $config2 = array(
            'source_image'      => $uploads['upload_data']['full_path'],
            'new_image'         => UPLOADS.'admin_profile/small/',
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
        	TBL_PREFIX.'admin_user',
        	['result_type'=>3]
        );
        $updateNewImage = $this->CommonModel->UpdateData(
        	['id'=>$Id, 'archive'=>0],
        	['profile_image'=>$uploads['upload_data']['file_name']],
        	TBL_PREFIX.'admin_user'
        );
        if ($updateNewImage > 0) {
        	if($getCurrentImage !== ''){
		       	// old image delete if there is any
		        $dbpathFile = $config['upload_path'] . $config['directory_name'] . '/' . $getCurrentImage->profile_image;
		        if (file_exists($dbpathFile) && is_file($dbpathFile)) {
		                unlink($dbpathFile);
                 }
                 
                 $dbpathFile2 = $config2['new_image'] .   $getCurrentImage->profile_image;
		        if (file_exists($dbpathFile2) && is_file($dbpathFile2)) {
		                unlink($dbpathFile2);
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
    
    public function getAdminprofile(){
        $request_body = get_json_data(true,['user_type'=>'admin']);
        $userDetails = $this->AdminModel->getAdminUserDetails($request_body);
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
        $request_body = get_json_data(true,['user_type'=>'admin']);
        $process_data = (array) $request_body->data;
        $id = $request_body->user_id;
        $this->form_validation->set_rules('firstname' ,'first name','required|min_length[2]|max_length[100]');
        $this->form_validation->set_rules('lastname' ,'last name','required|min_length[2]|max_length[100]');
        $this->form_validation->set_rules('email' ,'email','required|valid_email|max_length[64]|callback_check_exist['. json_encode(['key'=> 'email', 'person_id'=>$id]) .']');
        $this->form_validation->set_rules('phone' ,'phone','required|min_length[8]|max_length[50]|callback_check_exist['. json_encode(['key'=> 'phone', 'person_id'=>$id]) .']');
        $this->form_validation->set_rules('postal' ,'postal','required|min_length[3]|max_length[20]');
        $this->form_validation->set_rules('state' ,'state','required|max_length[20]');
        $this->form_validation->set_rules('suburb' ,'city','required|max_length[100]');
        $this->form_validation->set_rules('country' ,'country','required');
        $this->form_validation->set_rules('address' ,'address','required|max_length[250]');
        $this->form_validation->set_data($process_data);
        if($this->form_validation->run() == FALSE){
            $errors = $this->form_validation->error_array();
            $response = array('status'=>false,'msg' => implode(', ', $errors) );
            echo json_encode($response);
            exit();
        }
        $userUpdatedDetails = $this->AdminModel->updateAdminDetails($request_body);
        if(!empty($userUpdatedDetails)){
            $response = array('status' => true, 'msg' => 'Profile updated successfully.');
        }
         else {
            $response = array('status' => false, 'data' =>[]);
        }
        echo json_encode($response);
        exit();
    }


    public function check_exist($str, $Arr)
	{
        $arr=json_decode($Arr);
		$key=$arr->key;
        $personDetails= $this->CommonModel->getDataWhere(
            ['person_id'],
            ['id'=>$arr->person_id, 'archive'=>0],
            TBL_PREFIX.'admin_user',
            ['result_type'=>3]
        );
        $personId = $personDetails->person_id;
		$msg = "";
		$valid = true;
		if($key == 'email'){
			$msg = "Email already exist with other user";
            $userWhereArr = ['email'=>$str, 'archive'=>0, 'person_id !='=>$personId];
            $personEmailWhereArr = ['email'=>$str, 'archive'=>0, 'person_id !='=>$personId];
            $checkExistInUserTable = $this->CommonModel->getDataWhere(
				['id'],
				$userWhereArr,
				TBL_PREFIX.'admin_user',
				['result_type'=>3]
            );
			$checkExistInPersonEmailTable = $this->CommonModel->getDataWhere(
				['id'],
				$personEmailWhereArr,
				TBL_PREFIX.'person_email',
				['result_type'=>3]
            );
			if(!empty($checkExistInUserTable) || !empty($checkExistInPersonEmailTable)){
				$valid = false;
			}
		}
		if($key == 'phone'){
			$msg = "Contact already exist with other user";
            $personPhoneWhereArr = ['phone'=>$str, 'archive'=>0, 'person_id !='=>$personId, 'primary_phone'=>1];
			$checkExistInPersonPhoneTable = $this->CommonModel->getDataWhere(
				['id'],
				$personPhoneWhereArr,
				TBL_PREFIX.'person_phone',
				['result_type'=>3]
			);
			if(!empty($checkExistInPersonPhoneTable)){
				$valid = false;
			}
		}
		if(!$valid){
			$this->form_validation->set_message('check_exist', $msg);
			return FALSE;
		}else{
			return TRUE;
		}
	}  

	 

}