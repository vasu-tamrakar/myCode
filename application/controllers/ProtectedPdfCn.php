<?php

defined('BASEPATH') or exit('No direct script access allowed');

class ProtectedPdfCn extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->library('form_validation','pagination');
		$this->load->model('ProtectedPdfModel');
		$this->load->model('UserModel');

	}

	 
 
	public function protected_pdf_list()
	{
		$request_body = get_json_data(true);
		if(!empty($request_body->data))
		{
			$result = $this->ProtectedPdfModel->get_protected_pdf_list_model($request_body);
			if(!empty($result)){
				$response_ary = array('status'=>true,'data' => $result );
			} else {
				$response_ary = array('status'=>false,'msg' => "No Record Found" );
			}
			echo json_encode($response_ary);
			exit();
		}
	}

	public function get_pwd_list()
	{
		$request_body = get_json_data(true);
 
		if(!empty($request_body))
		{
			$result = $this->ProtectedPdfModel->get_all_dropdown_password_model($request_body);
			if(!empty($result)){
				$response_ary = array('status'=>true,'data' => $result );
			} else {
				$response_ary = array('status'=>false,'msg' => "No Record Found" );
			}
			echo json_encode($response_ary);
			exit();
		}
	}

	public function unprotectpdffile(){
		$request_body = get_json_data(true);
		if(!empty($request_body))
		{
			
			$request_data = $request_body->data;

			$operate_date = (array)$request_data;
			$this->form_validation->set_rules('bank_id', 'Please Select Bank for password', 'required');
			$this->form_validation->set_data($operate_date);
			if(!$this->form_validation->run()){
				$errors = $this->form_validation->error_array();
				$response = array('status' => false, 'msg' => implode(', ', $errors));
				echo json_encode($response);
				exit();
			}
			$result = $this->ProtectedPdfModel->decrypt_pdf_by_password_model($request_body);
			if(!empty($result) && $result['status']==true){
				$response_ary = array('status'=>true,'msg' => "Successfully Unprotected the file and Update in ".$result['table'].".");
			} else {
				$response_ary = array('status'=>false,'msg' => "Something Went Wrong" );
			}
			echo json_encode($response_ary);
			exit();
		}
	}

	public function delete_protected_pdf()
	{
		$request_body = get_json_data(true);
		if(!empty($request_body))
		{
			$result = $this->ProtectedPdfModel->unprotect_pdf_delete($request_body);
			if(!empty($result)){
				$response_ary = array('status'=>true,'data' => $result );
			} else {
				$response_ary = array('status'=>false,'msg' => "No Record Found" );
			}
			echo json_encode($response_ary);
			exit();
		}
	}

	

 

  
 
}
