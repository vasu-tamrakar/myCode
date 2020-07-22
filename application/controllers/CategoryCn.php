<?php
defined('BASEPATH') or exit('No direct script access allowed');

class CategoryCn extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->library('form_validation','pagination');
		$this->load->model('CategoryModel');
	}

	public function getCategories(){
		$request_body = get_json_data($token = true);
	  if ($request_body) {
	 	 $result = $this->CategoryModel->getCategoryList($request_body);
	 	 if (!empty($result)) {
	 		 $response = array('status' => true, 'data' => $result);
	 	 }else{
	 		 $response = array('status' => false, 'msg' => 'No record found');
	 	 }
	  }
	  echo json_encode($response);
	  exit();
	}

	public function getSubCategories(){
		$request_body = get_json_data($token = true);
	  if ($request_body && !empty($request_body->data)) {
	 	  $result = $this->CategoryModel->getSubCategoryList($request_body);
	 	  if (!empty($result)) {
	 		 $response = array('status' => true, 'data' => $result);
	 	 	}else{
	 		 $response = array('status' => false, 'msg' => 'No record found');
	 	 	}
	  }
	  echo json_encode($response);
	  exit();
	}


	public function add_category(){
		$request_body = get_json_data($token = true);
		if (!empty($request_body->data)) {
			$request_data = $request_body->data;

			$operate_date = (array)$request_data;
			$this->form_validation->set_rules('name', 'Category name', 'required|callback_category_check');
			$this->form_validation->set_data($operate_date);

			if ($this->form_validation->run() == true) {
				if ($request_data) {
					$data = array(
						'name' => strip_tags($request_data->name),
						'parentId' => (!empty($request_data->parentId)) ? strip_tags($request_data->parentId) : 0,
						'created' => DATE_TIME
					);
					$result = $this->BasicModel->insertRecords('tbl_category', $data);
					$response = array('status' => true, 'msg' => 'Category Created successfully!');
				}
			}else {
				$errors = $this->form_validation->error_array();
				$response = array('status' => false, 'msg' => implode(', ', $errors));
			}
			echo json_encode($response);
			exit();
		}
		else{
			$response = array('status' => false, 'msg' =>'Invalid request');
			echo json_encode($response);
			exit();
		}
	}
	public function update_category(){
		$request_body = get_json_data($token = true);

		if(!empty($request_body))
		{
			$request_data = $request_body->data;
			$operate_date = (array)$request_data;
			$this->form_validation->set_rules('update_name', 'Update Name', 'required|trim|callback_category_check');
			$this->form_validation->set_data($operate_date);
			if ($this->form_validation->run() == true) {
				$data = array(
					"name" => trim($request_data->update_name)
				);
				if (empty($request_data->id)) {
					$response = array('status' => false, 'msg' => 'Invalid category selected !!');
				}else{
					$result = $this->BasicModel->updateRecords('tbl_category', $data, array('id'=>$request_data->id, 'archive' => 0));
					$response = array('status' => true, 'msg' => 'Category Name Updated !!');
				}
			} else{
				$errors = $this->form_validation->error_array();
				$response = array('status' => false, 'msg' => implode(', ', $errors));
			}
			echo json_encode($response);
			exit();
		}
	}
	public function delete_category(){
		$request_body = get_json_data($token = true);
		if(!empty($request_body))
		{
			$request_data = $request_body->data;
			$operate_date = (array) $request_data;
			$data = array(
				"archive" => '1'
			);
			if (empty($request_data->id)) {
				$response = array('status' => false, 'msg' => 'Invalid Id !!');
			}else{
				$result = $this->BasicModel->updateRecords('tbl_category', $data, array('id'=>$request_data->id));
				$response = array('status' => true, 'msg' => 'Category deleted!!');
			}
		} else{
			$response = array('status' => false, 'msg' => 'Invalid request');
		}
		echo json_encode($response);
		exit();
	}
	public function category_check($str){
		$check = array();
		$checkCategory = $this->BasicModel->getRecordWhere('tbl_category', 'name', array('name'=>$str));
		if (!empty($checkCategory))
		{
			$check = array('status' => false);
			$this->form_validation->set_message('category_check', 'The given name already exists.');
			return FALSE;
		} else {
			return TRUE;
		}
	}
	public function view_category_details(){
		$request_body = get_json_data($token = true);

		if(!empty($request_body))
		{
			$request_data = $request_body->data;
			$operate_date = (array) $request_data;
			$this->form_validation->set_rules('id', 'Id', 'required');
			$this->form_validation->set_data($operate_date);
			if ($this->form_validation->run() == true) {
				$result = $this->BasicModel->getRecordWhere('tbl_category',array('id','name', 'parentId'), array('id'=>$request_data->id, 'archive' => 0));
				if (!empty($result)) {
					$response = array('status' => true, 'data' => $result);
				}else{
					$response = array('status' => false, 'msg' => 'Category not found !!');
				}
			}else{
				$errors = $this->form_validation->error_array();
				$response = array('status' => false, 'msg' => implode(', ', $errors));
			}
			echo json_encode($response);
			exit();
		}
	}
	public function category_listing(){
		$request_body = get_json_data($token = true);
		$request_data = $request_body->data;
		$operate_date = (array)$request_data;
		if ($request_body) {
			$result = $this->CategoryModel->getList($request_body);
			if (!empty($result)) {
				$response = array('status' => true, 'data' => $result);
			}else{
				$response = array('status' => false, 'msg' => 'No record found');
			}
		}
		echo json_encode($response);
		exit();
	}
	public function get_all_parents_categories(){

		$request_body = get_json_data($token = true);
		// $request_data = $request_body->data;
		$column = array('id AS value', 'category_name as label');
		$result = $this->CategoryModel->get_all_parents_categories_model($column);
		if($result){
			echo json_encode(array("status" => true, 'data' => $result,"message" => "successfully done."));
		}else{
			echo json_encode(array("status" => false,"message" => "no found data."));
		}
		exit();
	}
}
