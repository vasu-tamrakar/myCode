<?php

defined('BASEPATH') or exit('No direct script access allowed');

class VendorCn extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->library('form_validation','pagination');
		$this->load->model('VendorModel');
		$this->load->model('UserModel');

	}

	public function user_vendor_add()
	{
		$request_body = get_json_data(true);
		$request_data = $request_body->data;
		$request_user_id = $request_body->user_id;

		$process_data = (array)$request_data;
		$this->form_validation->set_rules('name','Vendor name','required|min_length[2]|max_length[100]');
		$this->form_validation->set_rules('gst','gst','required|min_length[2]|max_length[20]');
		$this->form_validation->set_rules('pincode','pincode','required|numeric|min_length[3]|max_length[6]');
		$this->form_validation->set_data($process_data);
		if($this->form_validation->run()){
			$result = $this->VendorModel->user_add_vendor($request_body);
			if($result['status']){
				$this->loges->setSpecificTitle('Add New vendor.');
				$this->loges->setActivityType('add_new_vendor');
				$this->loges->setDescription(json_encode($request_body));
				$this->loges->setTableID($result['data']);
				$this->loges->setCreatedBy($request_user_id);
				$this->loges->setCreatedType(2);
				$this->loges->createLog();
				echo json_encode($result); 
				exit();
			}else{
				echo json_encode(array('status' => false, 'message' => $result['message'])); 
				exit();
			}
		}else{
			/* validation false message send  */
			$errors = $this->form_validation->error_array();
			var_dump($errors); exit;
			$response = ['status' => false, 'msg' => implode(', ', $errors)];
			echo json_encode($response);
			exit();
		}	
	}

	public function user_vendor_update()
	{
		$request_body = get_json_data(true);
		$request_data = $request_body->data;
		$request_user_id = $request_body->user_id;
		$process_data = (array)$request_data;
		$this->form_validation->set_rules('name','Vendor name','trim|required|min_length[2]|max_length[100]');
		$this->form_validation->set_rules('gst','gst','trim|required|min_length[2]|max_length[20]');
		$this->form_validation->set_rules('pincode','pincode','trim|required|numeric|min_length[3]|max_length[6]');
		$this->form_validation->set_data($process_data);
		if($this->form_validation->run()){
			$result = $this->VendorModel->user_update_vendor($request_body);
			if($result['status']){
				$this->loges->setSpecificTitle('Updated vendor.');
				$this->loges->setActivityType('updated_vendor');
				$this->loges->setDescription(json_encode($request_body));
				$this->loges->setTableID($result['data']);
				$this->loges->setCreatedBy($request_user_id);
				$this->loges->setCreatedType(2);
				$this->loges->createLog();
				echo json_encode($result); 
				exit();
			}else{
				echo json_encode(array('status' => false, 'message' => $result['message'])); exit();
			}
		}else{
			/* validation false message send  */
			$error_array = $this->form_validation->error_array();
			$comma_separatedmsg = implode(",", $error_array);
			echo json_encode(array('status' => false, 'message' => $comma_separatedmsg)); exit();
		}	
	}
	
	public function user_vendor_list()
	{
		$request_body = get_json_data(true);
		if(!empty($request_body->data))
		{
			$result = $this->VendorModel->user_vendor_list($request_body);
			if(!empty($result)){
				$response_ary = array('status'=>true,'data' => $result );
			} else {
				$response_ary = array('status'=>false,'msg' => "No Record Found" );
			}
			echo json_encode($response_ary);
			exit();
		}
	}

	public function single_vendor_view(){
    	
    	$request_body = get_json_data(true);
    	$request_data = $request_body->data;

    	$VendorData = $this->VendorModel->view_user_single_vendor($request_data, 2);
    	if($VendorData){
			$response_ary = array('status'=>true, 'data' => $VendorData );
		} else {
			$response_ary = array('status'=>false, 'msg' => "No Data found." );
		}
		echo json_encode($response_ary);
		exit();
	}

	public function user_vendor_pending_category_list(){
		$request_body = get_json_data(true);
    	$request_data = $request_body->data;
		$result = $this->VendorModel->vendor_pending_category_list_model($request_data);
		if(!empty($result)){
			$response_ary = array('status'=>true,'data' => $result );
		} else {
			$response_ary = array('status'=>false,'msg' => "No vendor found." );
		}
		echo json_encode($response_ary);
		exit();
	}

	public function user_vendor_category_approval(){
    	$request_body = get_json_data(true);
    	$request_data = $request_body->data;

    	$result = $this->VendorModel->vendor_category_approval_model($request_data);
		if(!empty($result)){
			$response_ary = array('status'=>true,'msg' => 'Category Updated successfully.' );
		} else {
			$response_ary = array('status'=>false,'msg' => "No Category Found." );
		}
		echo json_encode($response_ary);
		exit();
    }
	 
 
}
