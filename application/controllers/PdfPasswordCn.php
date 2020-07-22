<?php
defined('BASEPATH') || exit('No direct script access allowed');

class PdfPasswordCn extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->library('form_validation');
		$this->load->model('PdfPasswordModel');
	}

	
	function pdfpassword_list(){
		$request_body = get_json_data(true);
		if(!empty($request_body->data))
		{
			$result = $this->PdfPasswordModel->get_pdfpassword_list($request_body);
			if(!empty($result)){
				$response_ary = array('status'=>true,'data' => $result );
			} else {
				$response_ary = array('status'=>false,'msg' => "No Record Found" );
			}
			echo json_encode($response_ary);
			exit();
		}
	}
	
	public function pdf_password_view(){
    	$request_body = get_json_data(true);
    	$request_data = $request_body->data;
    	$passwordData = $this->PdfPasswordModel->pdf_password_view($request_data);
    	if($passwordData){
			$response_ary = array('status'=>true, 'data' => $passwordData );
		} else {
			$response_ary = array('status'=>false, 'msg' => "No Data found." );
		}
		echo json_encode($response_ary);
		exit();
	}

	public function add_pdf_password(){
    	$request_body = get_json_data(true);
		$request_data = $request_body->data;
		$operate_data = (array)$request_data;
		$this->form_validation->set_rules('password', 'New Password', 'required|trim|max_length[100]');
		$this->form_validation->set_rules('confirm_password', 'Confirm Password', 'required|trim|matches[password]');
		$this->form_validation->set_data($operate_data);
		if (!$this->form_validation->run()) {
			$errors = $this->form_validation->error_array();
			$response = ['status'=>false,'msg' => implode(', ', $errors)];
			echo json_encode($response);
			exit();
		}
    	$passwordData = $this->PdfPasswordModel->add_pdf_password($request_body);
    	if($passwordData){
			$response_ary = array('status'=>true, 'msg' => 'Added Pdf password Successfully' );
		} else {
			$response_ary = array('status'=>false, 'msg' => "No Data found." );
		}
		echo json_encode($response_ary);
		exit();
	}
	
	public function update_pdf_password(){
    	$request_body = get_json_data(true);
		$request_data = $request_body->data;
		$operate_data = (array)$request_data;
		$this->form_validation->set_rules('password', 'New Password', 'required|trim|max_length[100]');
		$this->form_validation->set_rules('confirm_password', 'Confirm Password', 'required|trim|matches[password]');
		$this->form_validation->set_data($operate_data);
		if (!$this->form_validation->run()) {
			$errors = $this->form_validation->error_array();
			$response = ['status'=>false,'msg' => implode(', ', $errors)];
			echo json_encode($response);
			exit();
		}
    	$passwordData = $this->PdfPasswordModel->update_pdf_password($request_body);
    	if($passwordData){
			$response_ary = array('status'=>true, 'msg' => 'Updated password Successfully' );
		} else {
			$response_ary = array('status'=>false, 'msg' => "Something went wrong" );
		}
		echo json_encode($response_ary);
		exit();
	}
	
	public function pdf_password_delete(){
    	$request_body = get_json_data(true);
		$request_data = $request_body->data;
    	$passwordData = $this->PdfPasswordModel->pdf_password_delete($request_data);
    	if($passwordData){
			$response_ary = array('status'=>true, 'msg' => 'Deleted password Successfully' );
		} else {
			$response_ary = array('status'=>false, 'msg' => "Something went wrong." );
		}
		echo json_encode($response_ary);
		exit();
    }

  

}