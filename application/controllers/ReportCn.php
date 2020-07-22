<?php
defined('BASEPATH') or exit('No direct script access allowed');

class ReportCn extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('ReportModel');
	}

	function report_data(){
		$request_body = get_json_data(true);
        $request_data = $request_body;
        $data = $this->ReportModel->report_data_model($request_data);
        if($data){
            echo json_encode(['status'=> true, 'data'=>$data]);
            exit();
        }else{
            echo json_encode(['status'=> true, 'msg'=>'No data found.']);
            exit();
		}
	}
	function get_report_data(){
		$request_body = get_json_data(true);
		$request_data = $request_body;
		$response = $this->get_report($request_data);
		$res = $response['res'];
		echo json_encode(['status'=>true,'data'=>$res]);
		exit();	
	}

	private function get_report($request_data){
		$optionType = $request_data->data->view_type??'current_year';
		$userId= $request_data->user_id??0;
		$userTimeZone= $request_data->time_zone??'UTC';
		$utcTimeZone= 'UTC';
		$res=[];
		switch($optionType){
			case 'current_year':
				$title='Current Year '.DateFormate(change_one_timezone_to_another_timezone(DATE_TIME,$utcTimeZone,$userTimeZone), 'Y');
				$resInvoiceCategories = $this->ReportModel->get_invoice_categories_section_data($userId,['user_timezone'=>$userTimeZone,'type'=>'year']);
				$resInvoice= $this->ReportModel->get_invoice_categories_pie_data($userId,['user_timezone'=>$userTimeZone,'type'=>'year']);
				$responseStatement= $this->ReportModel->get_statment_credit_debit_graph_data($userId,['user_timezone'=>$userTimeZone,'type'=>'year']);
				$responseStatementDebit= $this->ReportModel->get_statment_credit_debit_graph_data($userId,['user_timezone'=>$userTimeZone,'type'=>'year','debit_only'=>true]);
				$responseStatementCategoryDebit= $this->ReportModel->get_statment_credit_debit_graph_data($userId,['user_timezone'=>$userTimeZone,'type'=>'year','debit_category_only'=>true]);
				$responseStatementBigGraph= $this->ReportModel->get_statment_credit_debit_graph_data($userId,['user_timezone'=>$userTimeZone,'type'=>'year','big_graph_only'=>true]);
				$res = array_merge($res,$resInvoiceCategories,$resInvoice,$responseStatement,$responseStatementDebit,$responseStatementCategoryDebit,$responseStatementBigGraph);
			break;
			case 'current_month':
				$title='Current Month '.DateFormate(change_one_timezone_to_another_timezone(DATE_TIME,$utcTimeZone,$userTimeZone), 'M Y');
				$resInvoiceCategories = $this->ReportModel->get_invoice_categories_section_data($userId,['user_timezone'=>$userTimeZone,'type'=>'month']);
				$resInvoice= $this->ReportModel->get_invoice_categories_pie_data($userId,['user_timezone'=>$userTimeZone,'type'=>'month']);
				$responseStatement= $this->ReportModel->get_statment_credit_debit_graph_data($userId,['user_timezone'=>$userTimeZone,'type'=>'month']);
				$responseStatementDebit= $this->ReportModel->get_statment_credit_debit_graph_data($userId,['user_timezone'=>$userTimeZone,'type'=>'month','debit_only'=>true]);
				$responseStatementCategoryDebit= $this->ReportModel->get_statment_credit_debit_graph_data($userId,['user_timezone'=>$userTimeZone,'type'=>'month','debit_category_only'=>true]);
				$responseStatementBigGraph= $this->ReportModel->get_statment_credit_debit_graph_data($userId,['user_timezone'=>$userTimeZone,'type'=>'month','big_graph_only'=>true]);
				$res = array_merge($res,$resInvoiceCategories,$resInvoice,$responseStatement,$responseStatementDebit,$responseStatementCategoryDebit,$responseStatementBigGraph);
			break;
			case 'current_week':
				$title='Current Week '.get_filter_type('week',['user_timezone'=>$userTimeZone,'return_format'=>'d M Y'])['fromDate'];
				$resInvoiceCategories = $this->ReportModel->get_invoice_categories_section_data($userId,['user_timezone'=>$userTimeZone,'type'=>'week']);
				$resInvoice= $this->ReportModel->get_invoice_categories_pie_data($userId,['user_timezone'=>$userTimeZone,'type'=>'week']);
				$responseStatement= $this->ReportModel->get_statment_credit_debit_graph_data($userId,['user_timezone'=>$userTimeZone,'type'=>'week']);
				$responseStatementDebit= $this->ReportModel->get_statment_credit_debit_graph_data($userId,['user_timezone'=>$userTimeZone,'type'=>'week','debit_only'=>true]);
				$responseStatementCategoryDebit= $this->ReportModel->get_statment_credit_debit_graph_data($userId,['user_timezone'=>$userTimeZone,'type'=>'week','debit_category_only'=>true]);
				$responseStatementBigGraph= $this->ReportModel->get_statment_credit_debit_graph_data($userId,['user_timezone'=>$userTimeZone,'type'=>'week','big_graph_only'=>true]);
				$res = array_merge($res,$resInvoiceCategories,$resInvoice,$responseStatement,$responseStatementDebit,$responseStatementCategoryDebit,$responseStatementBigGraph);
			break;
			case 'selected_month':
				$user_date = $request_data->data->specific_month;
				$title='Selected Month '.DateFormate($user_date, 'M Y');
				$resInvoiceCategories = $this->ReportModel->get_invoice_categories_section_data($userId,['user_timezone'=>$userTimeZone,'type'=>'month','user_date'=>$user_date]);
				$resInvoice= $this->ReportModel->get_invoice_categories_pie_data($userId,['user_timezone'=>$userTimeZone,'type'=>'month','user_date'=>$user_date]);
				$responseStatement= $this->ReportModel->get_statment_credit_debit_graph_data($userId,['user_timezone'=>$userTimeZone,'type'=>'month','user_date'=>$user_date]);
				$responseStatementDebit= $this->ReportModel->get_statment_credit_debit_graph_data($userId,['user_timezone'=>$userTimeZone,'type'=>'month','user_date'=>$user_date,'debit_only'=>true]);
				$responseStatementCategoryDebit= $this->ReportModel->get_statment_credit_debit_graph_data($userId,['user_timezone'=>$userTimeZone,'type'=>'month','user_date'=>$user_date,'debit_category_only'=>true]);
				$responseStatementBigGraph= $this->ReportModel->get_statment_credit_debit_graph_data($userId,['user_timezone'=>$userTimeZone,'type'=>'month','user_date'=>$user_date,'big_graph_only'=>true]);
				$res = array_merge($res,$resInvoiceCategories,$resInvoice,$responseStatement,$responseStatementDebit,$responseStatementCategoryDebit,$responseStatementBigGraph);
			break;
			case 'selected_quarter':
				$user_date = $request_data->data->specific_quarter;
				$title='Selected Quarter '.ceil(DateFormate($user_date,'n')/3) .' '.DateFormate($user_date, 'Y');
				$resInvoiceCategories = $this->ReportModel->get_invoice_categories_section_data($userId,['user_timezone'=>$userTimeZone,'type'=>'quarter','user_date'=>$user_date]);
				$resInvoice= $this->ReportModel->get_invoice_categories_pie_data($userId,['user_timezone'=>$userTimeZone,'type'=>'quarter','user_date'=>$user_date]);
				$responseStatement= $this->ReportModel->get_statment_credit_debit_graph_data($userId,['user_timezone'=>$userTimeZone,'type'=>'quarter','user_date'=>$user_date]);
				$responseStatementDebit= $this->ReportModel->get_statment_credit_debit_graph_data($userId,['user_timezone'=>$userTimeZone,'type'=>'quarter','user_date'=>$user_date,'debit_only'=>true]);
				$responseStatementCategoryDebit= $this->ReportModel->get_statment_credit_debit_graph_data($userId,['user_timezone'=>$userTimeZone,'type'=>'quarter','user_date'=>$user_date,'debit_category_only'=>true]);
				$responseStatementBigGraph= $this->ReportModel->get_statment_credit_debit_graph_data($userId,['user_timezone'=>$userTimeZone,'type'=>'quarter','user_date'=>$user_date,'big_graph_only'=>true]);
				$res = array_merge($res,$resInvoiceCategories,$resInvoice,$responseStatement,$responseStatementDebit,$responseStatementCategoryDebit,$responseStatementBigGraph);
			break;
			case 'custom_compare':
				$from_date = $request_data->data->from;
				$to_date = $request_data->data->to;
				$selectType = $request_data->data->custom_select;
				$title='Selected Custom Comapre ';
				if($selectType=='quarter'){
					$title .='Quarter (From quarter'. ceil(DateFormate($from_date,'n')/3) .' '.DateFormate($from_date, 'Y') . ' To quarter' .ceil(DateFormate($to_date,'n')/3) .' '.DateFormate($to_date, 'Y').')';
				}elseif($selectType=='year'){
					$title .='Year (From '. DateFormate($from_date, 'Y') . ' To ' .DateFormate($to_date, 'Y').')';
				}else{
					$title .='Month (From '. DateFormate($from_date, 'M Y') . ' To ' .DateFormate($to_date, 'M Y').')';
				}
				$resInvoice= $this->ReportModel->get_invoice_categories_pie_data($userId,['user_timezone'=>$userTimeZone,'type'=>'custom_compare','select_type'=>$selectType,'user_date_from'=>$from_date,'user_date_to'=>$to_date]);
				$responseStatement= $this->ReportModel->get_statment_credit_debit_graph_data($userId,['user_timezone'=>$userTimeZone,'type'=>'custom_compare','select_type'=>$selectType,'user_date_from'=>$from_date,'user_date_to'=>$to_date]);
				$responseStatementDebit= $this->ReportModel->get_statment_credit_debit_graph_data($userId,['user_timezone'=>$userTimeZone,'type'=>'custom_compare','select_type'=>$selectType,'user_date_from'=>$from_date,'user_date_to'=>$to_date,'debit_only'=>true]);
				$responseStatementCategoryDebit= $this->ReportModel->get_statment_credit_debit_graph_data($userId,['user_timezone'=>$userTimeZone,'type'=>'custom_compare','select_type'=>$selectType,'user_date_from'=>$from_date,'user_date_to'=>$to_date,'debit_category_only'=>true]);
				$responseStatementBigGraph= $this->ReportModel->get_statment_credit_debit_graph_data($userId,['user_timezone'=>$userTimeZone,'type'=>'custom_compare','select_type'=>$selectType,'user_date_from'=>$from_date,'user_date_to'=>$to_date,'big_graph_only'=>true]);
				$resInvoiceCategories = $this->ReportModel->get_invoice_categories_section_data($userId,['user_timezone'=>$userTimeZone,'type'=>'custom_compare','select_type'=>$selectType,'user_date_from'=>$from_date,'user_date_to'=>$to_date]);
				$res = array_merge($res,$resInvoiceCategories,$resInvoice,$responseStatement,$responseStatementDebit,$responseStatementCategoryDebit,$responseStatementBigGraph);
			break;
			default:
				$title='';
				$res= [];
		}
		return ['res'=>$res,'title'=>$title,'type'=>$optionType,'selected_type'=>$selectType??''];
	}

	private function export_report_data_excel($res=[],$extraPram=[]) {
		$title = $extraPram['title'] ?? '';
		$type = $extraPram['type'] ?? '';
		$user_id = $extraPram['user_id'] ?? 0;
		$fileData = $extraPram['file_data'] ?? [];
		$dataHearder = $type=='custom_compare'?['graph_type' => 'Graph type','option_selectable1'=>'Graph Option('.$title.')', 'option_selectable2' => '', 'option_selectable3' => '', 'option_selectable4' => '', 'option_selectable5' => '','option_selectable6' => '','option_selectable7' => '']:['graph_type' => 'Graph type','option_selectable1'=>'Graph Option('.$title.')', 'option_selectable2' => '', 'option_selectable3' => '', 'option_selectable4' => '', 'option_selectable5' => ''];
		$result['data']['Invoice expenses category'] = $this->invoice_piedata_report($res,['type'=>$type]);
		$result['data']['Statement credit and debit'] = $this->statement_credit_debit_report($res,['type'=>$type]);
		$result['data']['Statement expenses'] = $this->statement_expenses_report($res,['type'=>$type]);
		$result['data']['Statement expenses category'] = $this->statement_expenses_category_report($res,['type'=>$type]);
		$result['data']['Expenses'] = $this->statement_expenses_big_graph_report($res,['type'=>$type]);
		return $this->ReportModel->export_excel($dataHearder, $result['data'], ['file_name' => 'Report_'.$user_id.'_' . date('d_m_Y_H_i_s') . '.xls','user_id'=>$user_id,'file_data'=>$fileData,'file_dir_path'=>USER_REPORT_UPLOAD_PATH]);
    }


	function download_report_data(){
		$request_body = get_form_data(true);
		$request_data = $request_body;
		$request_data->data= (object)$request_data->data;
		$user_id = $request_data->user_id;
		$report_view_type = $request_data->data->report_view_type??'pdf';
		$response = $this->get_report($request_data);
		$res = $response['res'];
		$title = $response['title'];
		$optionType = $response['type'];
		$downloadRequest = true;
		$fileData = [];
		foreach($_FILES as $key=>$val){
			$respFile = $this->fileUpload($user_id,$_FILES,$key);
			if($respFile['status']){
				$fileData[$key]= $respFile['data']['file_name'];
			}else{
				$fileData[$key]=[];
			}
		}
		
		if($downloadRequest){
			if($report_view_type=='pdf'){
				$data = $this->export_report_data_pdf($res,['type'=>$optionType,'title'=>$title,'user_id'=>$user_id,'file_data'=>$fileData]);
			}
			if($report_view_type=='excel'){
				$data = $this->export_report_data_excel($res,['type'=>$optionType,'title'=>$title,'user_id'=>$user_id,'file_data'=>$fileData]);
			}
			$this->image_unlink_report($fileData,$user_id);
			echo json_encode($data);
			exit();	
		}
	}
	private function image_unlink_report($fileData,$user_id){
		if(!empty($fileData)){
			foreach($fileData as $row){
				$filePath= FCPATH.USER_REPORT_TEMP_PATH.$user_id.'/'.$row;
				if(file_exists($filePath) && is_file($filePath)){
					unlink($filePath);
				}
			}
		}
	}

	function fileUpload($user_id,$file,$inputName){
		  $config['upload_path'] = FCPATH . USER_REPORT_TEMP_PATH;
		  $config['input_name'] = $inputName;
		  $config['directory_name'] = $user_id;
		  $config['max_size'] ='2048000';
		  $config['encrypt_name'] = true;
		  $config['allowed_types'] = 'png';
		  $config['overwrite'] = false;
		  $path = FCPATH.USER_REPORT_TEMP_PATH . $user_id;
		  make_path($path);
		  $is_upload = do_upload($config);
			  if (isset($is_upload['error'])) {
				  return ['status'=>false,'error'=>$is_upload['error']];
			  } else {
				   return ['status'=>true,'data'=>$is_upload['upload_data']];
			  }
	  }

	  function get_report_forecast_data(){
		$request_body = get_json_data(true);
		$request_data = $request_body;
		$response = $this->get_report_forecast($request_data);
		$res = $response['res'];
		echo json_encode(['status'=>true,'data'=>$res]);
		exit();	
	}

	private function get_report_forecast($request_data){
		$optionType = $request_data->data->view_type??'current_year';
		$userId= $request_data->user_id??0;
		$userTimeZone= $request_data->time_zone??'UTC';
		$res=[];
		$extraPram=['user_timezone'=>$userTimeZone,'type'=>$optionType];
		if($optionType== 'selected_month'){
			$user_date = $request_data->data->specific_month ?? '';
			$extraPram['user_date']=$user_date;
		}
		if($optionType== 'selected_quarter'){
			$user_date = $request_data->data->specific_quarter?? '';
			$extraPram['user_date']=$user_date;
		}	
		$resInvoice = $this->ReportModel->get_invoice_categories_forecast_pie_data($userId,$extraPram);
		$resInvoiceNew = $this->ReportModel->get_invoice_categories_forecast_pie_data_new($userId,$extraPram);
		$responseStatement= $this->ReportModel->get_statment_credit_forecast_graph_data($userId,$extraPram);
		$res= array_merge($res,$resInvoice,$responseStatement,$resInvoiceNew);
		return ['res'=>$res,'type'=>$optionType];
	}

	public function update_report_forecast_data(){
		$request_body = get_json_data(true);
		$userId = $request_body->user_id;
		$this->load->library('CustomCurlCall');
		if(!empty($userId)){
			$urlInvoice = base_url('FmcronCn/forecasting_invoice/'.$userId);
			$urlStatment = base_url('FmcronCn/forecasting_statement/'.$userId);
			$this->customcurlcall->requestWithoutWait('POST', $urlInvoice);
			$this->customcurlcall->requestWithoutWait('POST', $urlStatment);
			sleep(3);
		}
		echo json_encode(['status'=>true,'message'=>'User invoice/statement forecasting has been successfully.']);
		exit();

	}
	private function export_report_data_pdf($res=[],$extraPram=[]) {
		$this->load->library('table');
		$title = $extraPram['title'] ?? '';
		$type = $extraPram['type'] ?? '';
		$user_id = $extraPram['user_id'] ?? 0;
		$fileData = $extraPram['file_data'] ?? [];
		$tmpl = array (
			'table_open'          => '<table style="text-align: center; margin:0 auto;" border="5" >',

			'heading_row_start'   => '<tr style="border:none; background-color: #5e64dd; font-weight:bold; color:white; align:center;">',
			'heading_row_end'     => '</tr>',
			'heading_cell_start'  => '<th style="align:center;border:none; color:white;text-align: center; font-size: 14px;" height=30 width=200>',
			'heading_cell_end'    => '</th>',

			'row_start'           => '<tr style="background-color: #e4e4e4; font-weight:bold;">',
			'row_end'             => '</tr>',
			'cell_start'          => '<td style="border-right: solid 1px #dadada;padding: 7px 5px;	padding-left: 15px;	font-weight: normal;font-size: 13px;align-items: center;display: flex;" align=center height=30>',
			'cell_end'            => '</td>',

			'row_alt_start'       => '<tr style="background-color: white; font-weight:bold;">',
			'row_alt_end'         => '</tr>',
			'cell_alt_start'      => '<td style="border:none;" align=center height=30>',
			'cell_alt_end'        => '</td>',

			'table_close'         => '</table>'
	  );
		$this->table->set_template($tmpl);
		$invoicePieDataReport = $this->invoice_piedata_report($res,['type'=>$type]);
		$statementCreditDebitReport = $this->statement_credit_debit_report($res,['type'=>$type]);
		$statementExpensesReport = $this->statement_expenses_report($res,['type'=>$type]);
		$statementExpensesCategoryReport = $this->statement_expenses_category_report($res,['type'=>$type]);
		$expensesReport = $this->statement_expenses_big_graph_report($res,['type'=>$type]);
		$html ='<html><head></head><body><h1>Report</h1>';
		$html .='<p><b>Filter: '.$title.'</b></p>';
		$html .='<p><h3 style="text-decoration:underline;text-underline-position: under;">Invoice Expenses Category</h3></p>';
		$html .='<p style="text-align: center;"><img src="'.base_url(USER_REPORT_TEMP_PATH . $user_id.'/'.$fileData['my-file0']).'" width="430px" height="300px"/></p>';
		$html .= '<div  style="width:100%; margin:0px auto; text-algin:center;">'.$this->table->generate(array_values($invoicePieDataReport)).'</div>';
		$html .='<pagebreak />';
		$html .='<p><h3 style="text-decoration:underline;text-underline-position: under;">Statement Credit And Debit</h3></p>';
		$html .='<p style="text-align: center;"><img src="'.base_url(USER_REPORT_TEMP_PATH . $user_id.'/'.$fileData['my-file1']).'"  width="50%" height="25%"/></p>';
		$html .= '<div  style="width:100%; margin:0px auto; text-algin:center;">'.$this->table->generate(array_values($statementCreditDebitReport)).'</div>';
		$html .='<pagebreak />';
		$html .='<p><h3 style="text-decoration:underline;text-underline-position: under;">Statement Expenses</h3></p>';
		$html .='<p style="text-align: center;"><img src="'.base_url(USER_REPORT_TEMP_PATH . $user_id.'/'.$fileData['my-file2']).'"  width="50%" height="25%"/></p>';
		$html .= '<div  style="width:100%; margin:0px auto; text-algin:center;">'.$this->table->generate(array_values($statementExpensesReport)).'</div>';
		$html .='<pagebreak />';
		$html .='<p><h3 style="text-decoration:underline;text-underline-position: under;">Statement Expenses Category</h3></p>';
		$html .='<p style="text-align: center;"><img src="'.base_url(USER_REPORT_TEMP_PATH . $user_id.'/'.$fileData['my-file3']).'"  width="430px" height="300px"/></p>';
		$html .= '<div  style="width:100%; margin:0px auto; text-algin:center;">'.$this->table->generate(array_values($statementExpensesCategoryReport)).'</div>';
		$html .='<pagebreak />';
		$html .='<p><h3 style="text-decoration:underline;text-underline-position: under;">Expenses</h3></p>';
		$html .='<p style="text-align: center;"><img src="'.base_url(USER_REPORT_TEMP_PATH . $user_id.'/'.$fileData['my-file4']).'"  width="50%" height="25%"/></p>';
		$html .= '<div  style="width:100%; margin:0px auto; text-algin:center;">'.$this->table->generate(array_values($expensesReport)).'</div>';
		$html .='</body></html>';
		ob_clean();
    	error_reporting(0);
		$this->load->library('MpdfLib');
		$pdf = $this->mpdflib->load();
		$pdf->setAutoTopMargin='stretch';
        $pdf->setHeader('<div style="text-align: left; font-weight: bold;">
		<img height="60px" width="auto" src="'.base_url("assets/images/logo2.png").'"/>
	 </div>');
        $pdf->setFooter('<table width="100%">
		<tr>
			<td width="33%">{DATE j/m/Y}</td>
			<td width="33%" align="center">{PAGENO}/{nbpg}</td>
			<td width="33%" style="text-align: right;">Report('.$title.')</td>
		</tr>
	</table>');
        $pdf->AddPage('L');
        $pdf->WriteHTML($html);
        $filename = 'Reprt_'.$user_id.'_' . date('d_m_Y_H_i_s') . '.pdf';
        $pdfFilePath = USER_REPORT_UPLOAD_PATH . $filename;
		$pdf->Output($pdfFilePath, 'F');
		if (file_exists($pdfFilePath)) {
            return ['status' => true, 'filename' => $filename];
        }
        return ['status' => false, 'error' => 'Pdf file not exist'];	
	}
	
	private function invoice_piedata_report($res=[],$extraPram=[]){
		$type = $extraPram['type']??'week';
		$response =[];
		$response[]= $type=='custom_compare'?['option_selectable1'=>'Category Name ','option_selectable2'=>'Amount From ','option_selectable3'=>'Amount To'] :['option_selectable1'=>'Category Name ','option_selectable2'=>'Amount'];
		if(!empty($res['invoice_pieData']['labels'])){
			foreach($res['invoice_pieData']['labels'] as $key=>$val){
				if($type=='custom_compare' && (!empty($res['invoice_pieData']['datasets'][0]['data'][$key]) || !empty($res['invoice_pieData']['datasets'][1]['data'][$key]))){
					$response[]= ['option_selectable1'=>$val,'option_selectable2'=>$res['invoice_pieData']['datasets'][0]['data'][$key],'option_selectable3'=>$res['invoice_pieData']['datasets'][1]['data'][$key]];
				}else if($type!='custom_compare' && !empty($res['invoice_pieData']['datasets'][0]['data'][$key])){
					$response[]= ['option_selectable1'=>$val,'option_selectable2'=>$res['invoice_pieData']['datasets'][0]['data'][$key]];
				}
			}
		}
		return $response;
	}
	private function statement_credit_debit_report($res=[],$extraPram=[]){
		$type = $extraPram['type']??'week';
		$response =[];
		$response[]= $type=='custom_compare'? ['option_selectable1'=>'Duration of Transaction','option_selectable2'=>'Credit Transaction From','option_selectable3'=>'Credit Transaction To','option_selectable4'=>'Debit Transaction From','option_selectable5'=>'Debit Transaction To']:['option_selectable1'=>'Duration of Transaction','option_selectable2'=>'Credit Transaction','option_selectable3'=>'Debit Transaction'];
			foreach($res['statementData'] as $key=>$val){
				if($key==0){
					continue;
				}
				if($type=='custom_compare' && (!empty($val[1]) || !empty($val[2]) || !empty($val[3]) || !empty($val[4]))){
					$response[]= ['option_selectable1'=>(string)$val[0],'option_selectable2'=>$val[3],'option_selectable3'=>$val[1],'option_selectable4'=>$val[4],'option_selectable5'=>$val[2]];
				}else if($type!='custom_compare' && (!empty($val[1]) || !empty($val[2]))){
					$response[]= ['option_selectable1'=>(string)$val[0],'option_selectable2'=>$val[1],'option_selectable3'=>$val[2]];
				}
				
			}
		return $response;
	}
	private function statement_expenses_report($res=[],$extraPram=[]){
		$type = $extraPram['type']??'week';
		$response =[];
		$response[]= $type=='custom_compare'? ['option_selectable1'=>'Duration of Transaction','option_selectable2'=>'Debit Transaction From','option_selectable3'=>'Debit Transaction To']:['option_selectable1'=>'Duration of Transaction','option_selectable2'=>'Debit Transaction'];
			foreach($res['debit_statementData'] as $key=>$val){
				if($key==0){
					continue;
				}
				if($type=='custom_compare' && (!empty($val[1]) || !empty($val[2]))){
					$response[]= ['option_selectable1'=>$val[0],'option_selectable2'=>$val[1],'option_selectable3'=>$val[2]];
				}else if($type!='custom_compare' && !empty($val[1])){
					$response[]= ['option_selectable1'=>$val[0],'option_selectable2'=>$val[1]];
				}
			}
		return $response;
	}
	private function statement_expenses_category_report($res=[],$extraPram=[]){
		$type = $extraPram['type']??'week';
		$response =[];
		$response[]= $type=='custom_compare'?['option_selectable1'=>'Category Name ','option_selectable2'=>'Amount From','option_selectable3'=>'Amount To']:['option_selectable1'=>'Category Name ','option_selectable2'=>'Amount'];
			foreach($res['category_statementData']['labels'] as $key=>$val){
				if($type=='custom_compare' && (!empty($res['category_statementData']['datasets'][0]['data'][$key]) || !empty($res['category_statementData']['datasets'][1]['data'][$key]))){
					$response[]= ['option_selectable1'=>$val,'option_selectable2'=>$res['category_statementData']['datasets'][0]['data'][$key],'option_selectable3'=>$res['category_statementData']['datasets'][1]['data'][$key]];
				}elseif($type!='custom_compare' && !empty($res['category_statementData']['datasets'][0]['data'][$key])){
					$response[]= ['option_selectable1'=>$val,'option_selectable2'=>$res['category_statementData']['datasets'][0]['data'][$key]];
				}
			}
		return $response;
	}
	private function statement_expenses_category($res=[],$category=[],$extraPram=[]){
		$type = $extraPram['type']??'week';
		$response =[];
		if(!empty($category)){
			foreach($category as $k=>$cat){
				if($type=='custom_compare' && !empty($res[$k])){
					$response[]= ['option_selectable1'=>'','option_selectable2'=>'','option_selectable3'=>'','option_selectable4'=>'','option_selectable5'=>'','option_selectable6'=>$cat,'option_selectable7'=>$res[$k]??''];
				}else if($type!='custom_compare' && !empty($res[$k])){
					$response[]= ['option_selectable1'=>'','option_selectable2'=>'','option_selectable3'=>'','option_selectable4'=>$cat,'option_selectable5'=>$res[$k]??''];
				}
			}
		}
		return $response;
	}
	private function statement_expenses_big_graph_report($res=[],$extraPram=[]){
		$type = $extraPram['type']??'week';
		$response =[];
		$response[]= $type=='custom_compare'?['option_selectable1'=>'Transaction Duration ','option_selectable2'=>'Credit Amount From','option_selectable3'=>'Credit Amount To','option_selectable4'=>'Debit Amount From','option_selectable5'=>'Debit Amount To', 'option_selectable6'=>'Breakup Expense category name','option_selectable7'=>'Breakup Expense Category Amount']:['option_selectable1'=>'Transaction Duration ','option_selectable2'=>'Credit Amount','option_selectable3'=>'Debit Amount', 'option_selectable4'=>'Breakup Expense category name','option_selectable5'=>'Breakup Expense Category Amount'];
		$catSlice = $type=='custom_compare' ? 5:3;
		$category=[];
			foreach($res['big_graph_statementData'] as $key=>$val){
				$cat =[];
				if($key==0){
					$category=array_slice($val,$catSlice,count($val),true);
					continue;
				}
				if($type=='custom_compare' && (!empty($val[1]) || !empty($val[2]) || !empty($val[3]) || !empty($val[4]))){
					$response[]= ['option_selectable1'=>$val[0],'option_selectable2'=>$val[3],'option_selectable3'=>$val[1],'option_selectable4'=>$val[4],'option_selectable5'=>$val[2]];
					$cat = $this->statement_expenses_category($val,$category,$extraPram);
				}else if($type!='custom_compare' && (!empty($val[1]) || !empty($val[2]))){
					$response[]= ['option_selectable1'=>$val[0],'option_selectable2'=>$val[1],'option_selectable3'=>$val[2]];
					$cat = $this->statement_expenses_category($val,$category,$extraPram);
				}
				$response = !empty($cat) ? array_merge($response,$cat):$response;
			}
		return $response;
	}


}
