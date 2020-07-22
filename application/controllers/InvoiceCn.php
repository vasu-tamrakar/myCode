<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH.'traits/formCustomValidation.php';
class InvoiceCn extends CI_Controller {
	use formCustomValidation;
	function __construct()
	{
		parent::__construct();
		$this->load->library('form_validation','pagination');
		$this->load->model('InvoiceModel');
		$this->load->model('UserModel');
	}

  function index(){

  }

	function listInvoice(){
		$request_body = get_json_data($token = true);
		if(!empty($request_body->data))
		{
			$result = $this->InvoiceModel->list_invoice($request_body);
			if(!empty($result)){
				$response_ary = array('status'=>true,'data' => $result );
			} else {
				$response_ary = array('status'=>false,'msg' => "No Record Found" );
			}
			echo json_encode($response_ary);
			exit();
		}
	}

	function fileUpload($user_id,$file){
		$file_name = date('Ymd').'_'.$user_id.'_'.time().".".pathinfo($file['invoice']['name'], PATHINFO_EXTENSION);
		$config['upload_path'] = FCPATH . USER_INVOICE_PATH;
		$config['input_name'] = 'invoice';
		$config['directory_name'] = $user_id;
		$config['max_size'] ='2048000';
		$config['file_name'] = $file_name;
		$config['allowed_types'] = 'pdf';
		$config['overwrite'] = TRUE;
		$path = FCPATH.USER_INVOICE_PATH . $user_id;
		make_path($path);
		$is_upload = do_upload($config);
			if (isset($is_upload['error'])) {
				echo json_encode(array('status' => false, 'error' => strip_tags($is_upload['error'])));
			} else {
				 return $is_upload['upload_data'];
			}
	}

	function add_invoice_upload(){
				$request_body = request_handlerFile();
				 if (!empty($request_body)) {
					 $invoiceType = $request_body->invoiceType;
					 if($invoiceType!='manual'){
					 	if(!empty($_FILES) && ($request_body->user_id)){
							$user_id=$request_body->user_id;
							$uploaded_file_name = $this->fileUpload($user_id,$_FILES);
							if($uploaded_file_name['file_name']) {
									$invoiceData = array(
										'invoice_file' => $uploaded_file_name['file_name'],
										'created'=> DATE_TIME,
										'user_id' => $user_id,
										'status'=>0,
										'change_status'=>1
									);
 							    $id = $this->InvoiceModel->insertInvoice($invoiceData);
								    if(!empty($id)){
										echo json_encode(array('status' => true, 'error' => '', 'success'=>'Invoice uploaded successfully.'));
									}
								}

						} else {
							echo json_encode(array('status' => false, 'error' => 'Failure while uploading the statement'));
						}
					} else {
						echo json_encode(array('status' => false, 'error' => 'Please Select the type as Upload Invoice'));
					}
				 } else {
            echo json_encode(array('status' => false, 'error' => 'Sorry no data found'));
        }
	}

	function viewInvoice(){
		$request_body = get_json_data($token = true);
		if(!empty($request_body) && !empty($request_body->data)){
			$requestData = $request_body;
			$result =	$this->InvoiceModel->viewDetailInvoice($requestData);
			if(!empty($result)){
				$response_ary = array('status'=>true,'data' => $result );
			} else {
				$response_ary = array('status'=>false,'msg' => "No Transactions Found" );
			}
			echo json_encode($response_ary);
			exit();
		}

	}

	function addorupdateInvoice(){
		$request_body = get_json_data($token=true);
		if(!empty($request_body) && !empty($request_body->data)){
			$requestData = $request_body;
			$validation = $this->invoiceValidation($requestData);
			if($validation['status']==true){
				$result = $this->InvoiceModel->addOrUpdateInvoiceData($requestData);
				if($result['status']){
					$response_ary = $result;
				} else {
					$response_ary = $result;
				}
			} else {
				$response_ary = $validation;
			}
			echo json_encode($response_ary);
			exit();
		}
	}

		function  vendor_exist_check($str){
			// $where = array('vendor_name'=>$str);
	    // $vendor =  $this->BasicModel->getRecordWhere('tbl_fm_vendor','id',$where);
			// if(!empty($vendor)){
			// 	$this->form_validation->set_message('vendor_exist_check', 'Vendor Name is Already Existed.');
			//  return FALSE;
			// } else{
				return true;
			//}
		}

		function invoiceValidation($requestData){

	    	$invoiceDetails =  (array) $requestData->data->invoice_details;
			$vendorDetails =  array('vendor_id' => $requestData->data->vendor_id);
			$transactions_details = json_decode(json_encode($requestData->data->transactions_details), true);

	    	$this->form_validation->set_rules('vendor_id', 'Vendor', 'required');
			$this->form_validation->set_rules('invoice_number', 'Invoice Number', 'required|max_length[50]|callback_alpha_num_nospace');
			$this->form_validation->set_rules('order_number', 'Order Number', 'max_length[50]|callback_alpha_num_nospace');
			$this->form_validation->set_rules('invoice_date', 'Invoice Date', 'trim|required');
			$this->form_validation->set_rules('total_amount', 'Total Amount', 'trim|required|max_length[11]|callback_valid_amt|greater_than[0]');
			$this->form_validation->set_rules('paid_amount', 'Paid Amount', 'trim|required|max_length[11]|callback_valid_amt|greater_than_equal_to[1]');
			$this->form_validation->set_rules('due_amount', 'Due Amount', 'trim|required|max_length[11]|callback_valid_amt|greater_than_equal_to[0]');

			$data  = array_merge($invoiceDetails, $vendorDetails,array('transactions_details'=>$transactions_details));
			
			
	 		
			if(!empty($transactions_details))
			{
			    foreach($transactions_details as $id => $value)
			    {
			        $this->form_validation->set_rules('transactions_details[' . $id . '][item_description]', 'item '.($id+1), 'required|trim|max_length[100]');
					$this->form_validation->set_rules('transactions_details[' . $id . '][category_id]', 'category', 'required|trim');
					$this->form_validation->set_rules('transactions_details[' . $id . '][qty]', 'quantity', 'required|trim|max_length[7]|greater_than[0]');
					$this->form_validation->set_rules('transactions_details[' . $id . '][unit_price]', 'unit price', 'required|trim|max_length[12]|greater_than[0]|callback_valid_amt');
					$this->form_validation->set_rules('transactions_details[' . $id . '][total]', 'total', 'required|trim|max_length[11]|greater_than[0]|callback_valid_amt');
			    }
			}
			$this->form_validation->set_data($data);
	    if($this->form_validation->run() == true){
	      $response_ary = array('status'=>true,'msg' => '' );
	    } else{
	      $errors = $this->form_validation->error_array();
	      $response_ary = array('status'=>false,'msg' => implode(', ', $errors) );
		}
	    return $response_ary;
	  }

	function deleteInvoice(){
		$request_body = get_json_data($token=true);

		if(!empty($request_body) && !empty($request_body->data)){
			$data = (array)$request_body->data;
			$this->form_validation->set_rules('invoice_id', 'invoice id', 'required');
			$this->form_validation->set_data($data);
			if($this->form_validation->run() == true){
				$requestData = $request_body;
				$result = $this->InvoiceModel->deleteInvoiceData($requestData);
				if($result){
					$response_ary = array('status'=>true,'data' => $result, 'msg'=>'Invoice Details Deleted Successfully' );
				} else {
					$response_ary = array('status'=>false,'msg' => "No Transactions Found" );
				}
				echo json_encode($response_ary);
				exit();
			} else{
				$errors = $this->form_validation->error_array();
				echo json_encode( array('status'=>false,'msg' => implode(', ', $errors)) ); exit();
			}
		}
	}

	function deleteInvoiceTransaction(){
		$request_body = get_json_data($token=true);
		if(!empty($request_body) && !empty($request_body->data)){
			$requestData = $request_body;
			$result =	$this->InvoiceModel->deleteInvoiceTransactionData($requestData);
			if(!empty($result)){
				$response_ary = array('status'=>true,'data' => $result, 'msg'=>'Invoice Item Deleted Successfully' );
			} else {
				$response_ary = array('status'=>false,'msg' => "No Transactions Found" );
			}
			echo json_encode($response_ary);
			exit();
		}
	}


	/*----------------------------------------------------------
	|	get_vandorDD_NameList()
	-----------------------------------------------------------*/
	public function get_vandorDD_NameList()
	{
		$request_body = get_json_data($token=true);
		$result = $this->InvoiceModel->get_DDVendorNameList($request_body);
		if($result){
			echo json_encode(array('status' =>true, 'data' => $result, 'message' =>'Successfully done.'));
    		exit();
		}else{
			echo json_encode(array('status' =>false, 'message' =>'no found data.'));
		    exit();
		}
	}


	/*----------------------------------------------------------
	|	add_New_Vandor()
	-----------------------------------------------------------*/
	public function add_New_Vandor()
	{
		$request_body = get_json_data($token=true);
		$request_data = $request_body->data;
		$request_user_id = $request_body->user_id;

		$process_data = (array)$request_data;
		$this->form_validation->set_rules('vendorname','vandor name','trim|required|min_length[2]|max_length[100]');
		$this->form_validation->set_rules('gst','gst','trim|required|min_length[2]|max_length[20]');
		$this->form_validation->set_rules('pincode','pincode','trim|required|numeric|min_length[3]|max_length[6]');
		$this->form_validation->set_data($process_data);
		if($this->form_validation->run()){
			$result = $this->InvoiceModel->add_new_vendor_model($process_data,$request_user_id);
			if($result['status']){
				$this->loges->setSpecificTitle('Add New vendor.');
				$this->loges->setActivityType('add_new_vendor');
				$this->loges->setDescription(json_encode($request_body));
				$this->loges->setTableID($result['data']);
				$this->loges->setCreatedBy($request_user_id);
				$this->loges->setCreatedType(2);
				$this->loges->createLog();
				echo json_encode($result); exit();
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

	/*----------------------------------------------------------
	|	get_invoice_data_by_id()
	-----------------------------------------------------------*/
	public function get_invoice_data_by_id()
	{
		$request_body = get_json_data($token=true);
		$request_data = $request_body->data;
		$process_data = (array)$request_data;
		$this->form_validation->set_rules('invoice_id','invoice id','required');
		$this->form_validation->set_data($process_data);
		if($this->form_validation->run()){
			$result = $this->InvoiceModel->getInvoiceDetailsbyId($request_body);
			if($result['status']){
				echo json_encode($result); exit();
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
}