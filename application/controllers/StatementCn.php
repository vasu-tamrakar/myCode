<?php
defined('BASEPATH') || exit('No direct script access allowed');
require_once APPPATH.'traits/formCustomValidation.php';

class StatementCn extends CI_Controller {

	use formCustomValidation;
	function __construct()
	{
		parent::__construct();
		$this->load->library('form_validation','pagination');
		$this->load->model('StatementModel');
		$this->load->model('UserModel');
	}

  function index(){

  } 
  	function statmentBankList(){
		$request_body = get_json_data(true);
		if(!empty($request_body->data))
		{
			$result = $this->StatementModel->statmentBankList($request_body);
			if(!empty($result)){
				$response_ary = array('status'=>true,'data' => $result );
			} else {
				$response_ary = array('status'=>false,'msg' => "No Record Found" );
			}
			echo json_encode($response_ary);
			exit();
		}
	}

	function list_statement(){
		$request_body = get_json_data(true);
		if(!empty($request_body->data))
		{
			$result = $this->StatementModel->list_statement($request_body);
			if(!empty($result)){
				$response_ary = array('status'=>true,'data' => $result );
			} else {
				$response_ary = array('status'=>false,'msg' => "No Record Found" );
			}
			echo json_encode($response_ary);
			exit();
		}
	}

	function add_statement_upload(){
		$request_body = get_form_data(true);
		if (!empty($request_body)) {
			$stmtType = $request_body->stmtType;
			$passwordVal = (!empty($request_body->passwordval))?$request_body->passwordval:'';
			$password = ($request_body->protected=='yes')?$passwordVal:'';
			if($stmtType!='manual'){
				if(!empty($_FILES) && ($request_body->user_id)){
					$user_id=$request_body->user_id;
					$original_file_name = $_FILES['stmt']['name']; 
					$file_name = date('Ymd').'_'.$user_id.'_'.time().".".pathinfo($_FILES['stmt']['name'], PATHINFO_EXTENSION);
					$config['upload_path'] = FCPATH . USER_STATEMENT_PATH;
					$config['input_name'] = 'stmt';
					$config['directory_name'] = $user_id;
					$config['max_size'] ='2048000';
					$config['file_name'] = $file_name;
					$config['allowed_types'] = 'pdf';
				 	$path = FCPATH.USER_STATEMENT_PATH . $user_id; 
					make_path($path);
					$is_upload = do_upload($config);
						if (isset($is_upload['error'])) {
							echo json_encode(array('status' => false, 'error' => strip_tags($is_upload['error'])));
						} else {
							$stmtData = array(
								'statement_file_name' => $file_name,
								'created'=> DATE_TIME,
								'user_id' => $user_id,
								'status'=>0,
								'change_status'=>1,
								'source_type' => 1,
								'statement_for'=>2
							);
						$id = $this->StatementModel->insertStatement($stmtData);
							if(!empty($id)){
								$result =	$this->StatementModel->readUploadStatement($id,$password,$original_file_name);
								if($result){
									echo json_encode(array('status' => true, 'error' => '', 'success'=>'Successfully Updated the Statement'));
								}
							}
						}
				} else {
					echo json_encode(array('status' => false, 'error' => 'Failure while uploading the statement'));
				}
			} else {
				echo json_encode(array('status' => false, 'error' => 'Please Select the type as Upload Statement'));
			}
		} else {
			echo json_encode(array('status' => false, 'error' => 'Sorry no data found'));
        }

	}

	function viewStatement(){
		$request_body = get_json_data(true);
		if(!empty($request_body) && !empty($request_body->data)){
			$requestData = $request_body;
			$result =	$this->StatementModel->viewDetailStatement($requestData);
			if(!empty($result)){
				$response_ary = array('status'=>true,'data' => $result );
			} else {
				$response_ary = array('status'=>false,'msg' => "No Transactions Found" );
			}
			echo json_encode($response_ary);
			exit();
		}

	}

	function addorupdateStatement(){
		$request_body = get_json_data(true);
		if(!empty($request_body) && !empty($request_body->data)){
			$requestData = $request_body;
			$this->statementValidation($requestData);
			$result =	$this->StatementModel->addOrUpdateStatementData($requestData);
			if(!empty($result)){
				$response_ary = array('status'=>true,'data' => $result, 'msg'=>'Statement Details Updated Successfully' );
			} else {
				$response_ary = array('status'=>false,'msg' => "No Transactions Found" );
			}
			echo json_encode($response_ary);
			exit();
		}
	}

	function statementValidation($requestData){
		$stmtDetails =  (array) $requestData->data->statmentData;
		
		$stmtItems = json_decode(json_encode($requestData->data->transactionData),true);
		$this->form_validation->set_rules('statement_for_of', 'Statement of', 'trim|required|max_length[50]');
		$this->form_validation->set_rules('issue_date', 'Issue Date', 'trim|max_length[50]');

		$data = array_merge($stmtDetails, array('transactions_details'=>$stmtItems));
		if(!empty($stmtItems))
		{ 
			$stmtdate  = $stmtDetails['issue_date'];
			foreach($stmtItems as $id => $value)
			{
				$this->form_validation->set_rules('transactions_details[' . $id . '][description]', 'description '.($id+1), 'required|trim|min_length[2]|max_length[100]');
				$this->form_validation->set_rules('transactions_details[' . $id . '][date]', 'transaction date', 'required|trim|callback_check_equal_less['. json_encode(['tdate' => $stmtdate, 'val' =>$id+1]) .']');
				$this->form_validation->set_rules('transactions_details[' . $id . '][transaction_type]', 'transaction type', 'required|trim');
				$this->form_validation->set_rules('transactions_details[' . $id . '][amount]', 'amount', 'required|trim|max_length[11]|callback_valid_amt|greater_than[0]');
				$this->form_validation->set_rules('transactions_details[' . $id . '][main_balance]', 'main balance', 'required|trim|callback_valid_balance|max_length[12]');
			}
		}
		$this->form_validation->set_data($data);
		if(!$this->form_validation->run()){
		$errors = $this->form_validation->error_array();
		$response_ary = array('status'=>false,'msg' => implode(', ', $errors) );
		echo json_encode($response_ary);
		exit;
		}
	  }
	function check_equal_less($second_field,$first_field)
	{
		 
		$arr=(array) json_decode($first_field);
	 	if(!empty($arr)){
			$first_field = date('Y-m-d',strtotime($arr['tdate']));
			$second_field = date('Y-m-d',strtotime($second_field));
			
			if (strtotime($first_field) < strtotime($second_field))
			{	
				$this->form_validation->set_message('check_equal_less', 'The transactions date '.$arr['val'].' should be less than or equal to statement generate date.');
				return false;       
			}
			else
			{
				return true;
			}
	    }
	}

	function deleteStatement(){
		$request_body = get_json_data(true);
		if(!empty($request_body) && !empty($request_body->data)){
			$requestData = $request_body;
			$result =	$this->StatementModel->deleteStatementData($requestData);
			if(!empty($result)){
				$response_ary = array('status'=>true,'data' => $result, 'msg'=>'Statement Details Deleted Successfully' );
			} else {
				$response_ary = array('status'=>false,'msg' => "No Transactions Found" );
			}
			echo json_encode($response_ary);
			exit();
		}
	}

	function deleteStatementTransaction(){
		$request_body = get_json_data(true);
		if(!empty($request_body) && !empty($request_body->data)){
			$requestData = $request_body;
			$result =	$this->StatementModel->deleteStatementTransactionData($requestData);
			if(!empty($result)){
				$response_ary = array('status'=>true,'data' => $result, 'msg'=>'Statement Transaction Deleted Successfully' );
			} else {
				$response_ary = array('status'=>false,'msg' => "No Transactions Found" );
			}
			echo json_encode($response_ary);
			exit();
		}
	}
  
	function readUserStatements(){
			$request_body = get_json_data(true);
			if ($request_body->user_id){
				$user_id = $request_body->user_id;
				$result = $this->StatementModel->readAllStatements($user_id);
				if(!empty($result) && !empty($result['count'])){
					$response_ary = array('status'=>true,  'data'=>'Total '.$result['count'].' Statement Read Completed Successfully' );
				} else {
					$response_ary = array('status'=>false,'data' => "No Statment Found to Read" );
				}
				echo json_encode($response_ary);
				exit();
			}
	}




}
