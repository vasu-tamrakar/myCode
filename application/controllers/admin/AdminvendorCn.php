<?php
defined('BASEPATH') || exit('No direct script access allowed');

class AdminvendorCn extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->library('form_validation','pagination');
		$this->load->model('VendorModel');
	}

	function index(){

	}

	function vendor_list(){
		$request_body = get_json_data(true,['user_type'=>'admin']);
		if(!empty($request_body->data))
		{
			$result = $this->VendorModel->admin_vendor_list($request_body);
			if(!empty($result)){
				$response_ary = array('status'=>true,'data' => $result );
			} else {
				$response_ary = array('status'=>false,'msg' => "No Record Found" );
			}
			echo json_encode($response_ary);
			exit();
		}
    }


    function vendor_approval(){

    	$request_body = get_json_data(true,['user_type'=>'admin']);

		$result = $this->VendorModel->vendor_approval_model($request_body);
		if(!empty($result)){
			$response_ary = array('status'=>true,'msg' => 'Vendors Updated successfully' );
		} else {
			$response_ary = array('status'=>false,'msg' => "No Record Found" );
		}
		echo json_encode($response_ary);
		exit();
    }


    public function categories_listing(){
    	$request_body = get_json_data(true,['user_type'=>'admin']);

    	$getCategories = $this->CommonModel->getDataWhere(
    			['id as value', 'category_name as label'],
    			['status'=>1, 'archive'=>0],
    			TBL_PREFIX.'category',
    			['result_type'=>1]
    	);
		
		$response_ary = array('status'=>true,'data' => $getCategories );
    	echo json_encode($response_ary);
		exit();

    }

    public function add_vendor(){

    	$request_body = get_json_data(true,['user_type'=>'admin']);
    	$request_data = $request_body->data;
    	
    	$this->vendor_common_validation($request_data);

    	$addVendor = $this->VendorModel->add_vendor_model($request_data);
    	if($addVendor){
			$response_ary = array('status'=>true, 'msg' => 'Vendor Added successfully' );
		} else {
			$response_ary = array('status'=>false, 'msg' => "Please try again." );
		}
		echo json_encode($response_ary);
		exit();

    	

    }


    private function vendor_common_validation($request_data){

    	$operate_data = (array)$request_data;
    	$this->form_validation->set_rules('name', 'Name', 'required|trim|max_length[255]|min_length[2]');
    	$this->form_validation->set_rules('vendor_type', 'Vendor type', 'required|trim');

    	if($request_data->vendor_type == 2){
    		$this->form_validation->set_rules('gst', 'Gst', 'required|trim|max_length[15]');
			$this->form_validation->set_rules('pincode', 'pincode', 'required|trim|min_length[2]|max_length[6]|numeric');
    	}

		$this->form_validation->set_data($operate_data);
    	if ($this->form_validation->run() == FALSE) {
			$errors = $this->form_validation->error_array();
			$response = array('status' => false, 'msg' => implode(', ', $errors));
			echo json_encode($response);
			exit();
		} 

    	if(empty($request_data->categories)){
    		$response = array('status' => false, 'msg' => 'Please select atleast one category.');
			echo json_encode($response);
			exit();
    	}

    }

    public function view_vendor(){
    	
    	$request_body = get_json_data(true,['user_type'=>'admin']);
    	$request_data = $request_body->data;

    	$VendorData = $this->VendorModel->view_vendor_model($request_data, 1);
    	if($VendorData){
			$response_ary = array('status'=>true, 'data' => $VendorData );
		} else {
			$response_ary = array('status'=>false, 'msg' => "No Data found." );
		}
		echo json_encode($response_ary);
		exit();
    }


    public function delete_vendor(){

		$request_body = get_json_data(true,['user_type'=>'admin']);
    	$request_data = $request_body->data;
    	$delete = $this->VendorModel->delete_vendor_model($request_data);
    	if($delete){
			$response_ary = array('status'=>true, 'msg' => 'Vendor deleted successfully.');
		} else {
			$response_ary = array('status'=>false, 'msg' => "No Vendor found." );
		}
		echo json_encode($response_ary);
		exit();
    }

    public function get_vendor_details(){

    	$request_body = get_json_data(true,['user_type'=>'admin']);
    	$request_data = $request_body->data;

    	$VendorData = $this->VendorModel->view_vendor_model($request_data, 2);
    	if($VendorData){
			$response_ary = array('status'=>true, 'data' => $VendorData );
		} else {
			$response_ary = array('status'=>false, 'msg' => "No Data found." );
		}
		echo json_encode($response_ary);
		exit();
    }

    public function update_vendor(){

    	$request_body = get_json_data(true,['user_type'=>'admin']);
    	$request_data = $request_body->data;
    	
    	$this->vendor_common_validation($request_data);

    	$addVendor = $this->VendorModel->update_vendor_model($request_data);
    	if($addVendor){
			$response_ary = array('status'=>true, 'msg' => 'Vendor Updated successfully' );
		} else {
			$response_ary = array('status'=>false, 'msg' => "Please try again." );
		}
		echo json_encode($response_ary);
		exit();	

    }

    public function single_vendor_approval(){

    	$request_body = get_json_data(true,['user_type'=>'admin']);
    	$request_data = $request_body->data;


    	$result = $this->VendorModel->single_vendor_approval_model($request_data, $request_body->user_id);
		if(!empty($result)){
			$response_ary = array('status'=>true,'msg' => 'Vendor Updated successfully.' );
		} else {
			$response_ary = array('status'=>false,'msg' => "No Vendor Found." );
		}
		echo json_encode($response_ary);
		exit();
    	
    }


    public function vendor_pending_category_list(){
    	$request_body = get_json_data(true,['user_type'=>'admin']);
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


    public function vendor_category_approval(){

    	$request_body = get_json_data(true,['user_type'=>'admin']);
    	$request_data = $request_body->data;

    	$result = $this->VendorModel->vendor_category_approval_model($request_data,$request_body->user_id);
		if(!empty($result)){
			$response_ary = array('status'=>true,'msg' => 'Category Updated successfully.' );
		} else {
			$response_ary = array('status'=>false,'msg' => "No Category Found." );
		}
		echo json_encode($response_ary);
		exit();
    }


}