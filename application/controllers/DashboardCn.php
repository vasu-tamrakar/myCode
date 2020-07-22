<?php
defined('BASEPATH') or exit('No direct script access allowed');

class DashboardCn extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('DashboardModel');
	}

	function dashboard_data(){
		$request_body = get_json_data(true);
		$request_data = $request_body;

        $data = $this->DashboardModel->dashboard_data_model($request_data);
        if($data){
            echo json_encode(['status'=> true, 'data'=>$data]);
            exit();
        }else{
            echo json_encode(['status'=> true, 'msg'=>'No data found.']);
            exit();
		}
	}
	function dashboard_graph_data(){
		$request_body = get_json_data(true);
		$request_data = $request_body;
		$response=[];
		if(!empty($request_data)){
			$response['status']=true;
			$callType= $request_data->data->call_type??'';
			$userId= $request_data->user_id??0;
			$userTimeZone= $request_data->time_zone??'UTC';

			switch($callType){
				case 'invoice_pie_graph_call':
					$optionType= $request_data->data->option_type??'week';
					$response['data']= $this->DashboardModel->get_invoice_categories_pie_data($userId,['user_timezone'=>$userTimeZone,'type'=>$optionType]);
				break;
				case 'statement_credit_debit_graph_call':
					$optionType= $request_data->data->option_type??date('Y');
					$response['data']= $this->DashboardModel->get_statment_credit_debit_graph_data($userId,['user_timezone'=>$userTimeZone,'year'=>$optionType]);
				break;
				case 'invoice_expenses_graph_call':
					$optionType= $request_data->data->option_type??date('Y');
					$response['data']= $this->DashboardModel->get_invoice_expenses_graph_data($userId,['user_timezone'=>$userTimeZone,'year'=>$optionType]);
				break;
				case 'compare_expenses_graph_call':
					$optionType= $request_data->data->option_type??'invoice';
					$yearFrom= $request_data->data->expenses_from??date('Y');
					$yearto= $request_data->data->expenses_to??date('Y');
					$response['data']= $this->DashboardModel->get_compare_invoice_statement_expenses_graph_data($userId,$optionType,['user_timezone'=>$userTimeZone,'year_from'=>$yearFrom,'year_to'=>$yearto]);
				break;
				default:
					$response['data'] = [];
				break;
			}
			echo json_encode($response);
            exit();

		}

        
	}



}
