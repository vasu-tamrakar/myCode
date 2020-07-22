<?php
defined('BASEPATH') || exit('No direct script access allowed');

class AdminCategoryCn extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->library('form_validation','pagination');
		$this->load->model('admin/AdminCategoryModel');
	}

	
	function category_list(){
		$request_body = get_json_data(true,['user_type'=>'admin']);
		if(!empty($request_body->data))
		{
			$result = $this->AdminCategoryModel->get_category_list($request_body);
			if(!empty($result)){
				$response_ary = array('status'=>true,'data' => $result );
			} else {
				$response_ary = array('status'=>false,'msg' => "No Record Found" );
			}
			echo json_encode($response_ary);
			exit();
		}
	}
	
	public function get_category(){
    	$request_body = get_json_data(true,['user_type'=>'admin']);
    	$request_data = $request_body->data;
    	$categoryData = $this->AdminCategoryModel->get_category($request_data);
    	if($categoryData){
			$response_ary = array('status'=>true, 'data' => $categoryData );
		} else {
			$response_ary = array('status'=>false, 'msg' => "No Data found." );
		}
		echo json_encode($response_ary);
		exit();
	}

	public function add_category(){
    	$request_body = get_json_data(true,['user_type'=>'admin']);
		$request_data = $request_body->data;
    	$categoryData = $this->AdminCategoryModel->add_category($request_data);
    	if($categoryData){
			$response_ary = array('status'=>true, 'msg' => 'Added Category Successfully' );
		} else {
			$response_ary = array('status'=>false, 'msg' => "No Data found." );
		}
		echo json_encode($response_ary);
		exit();
	}
	
	public function update_category(){
    	$request_body = get_json_data(true,['user_type'=>'admin']);
		$request_data = $request_body->data;
    	$categoryData = $this->AdminCategoryModel->update_category($request_data);
    	if($categoryData){
			$response_ary = array('status'=>true, 'msg' => 'Updated Category Successfully' );
		} else {
			$response_ary = array('status'=>false, 'msg' => "Something went wrong" );
		}
		echo json_encode($response_ary);
		exit();
	}
	
	public function delete_category(){
    	$request_body = get_json_data(true,['user_type'=>'admin']);
		$request_data = $request_body->data;
    	$categoryData = $this->AdminCategoryModel->delete_category($request_data);
    	if($categoryData){
			$response_ary = array('status'=>true, 'msg' => 'Deleted Category Successfully' );
		} else {
			$response_ary = array('status'=>false, 'msg' => "Something went wrong." );
		}
		echo json_encode($response_ary);
		exit();
    }

  

}