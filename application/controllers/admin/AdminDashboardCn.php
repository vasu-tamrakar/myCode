<?php
defined('BASEPATH') || exit('No direct script access allowed');

require_once APPPATH . 'traits/formCustomValidation.php';

class AdminDashboardCn extends CI_Controller
{
    function __construct()
	{
		parent::__construct();
		$this->load->library('form_validation');
        $this->load->model('admin/AdminDashboardModel');
        $this->load->model('admin/AdminModel');
    }
    
    function getAllCounts(){
        $request_body = get_json_data(true,['user_type'=>'admin']);
        $dashboardDetails['count'] = $this->AdminDashboardModel->getDashboardDetails($request_body);
        $dashboardDetails['admin']=$this->AdminModel->getAdminUserDetails($request_body);
        if(!empty($dashboardDetails)){
            $response = array('status' => true, 'data' => $dashboardDetails);
        }
         else {
            $response = array('status' => false, 'data' =>[]);
        }
        echo json_encode($response);
        exit();
    }


    public function admin_dashboard_data(){

        $request_body = get_json_data(true,['user_type'=>'admin']);
        $request_data = $request_body->data;

        $data = $this->AdminDashboardModel->admin_dashboard_data_model($request_body);
        if($data){
            echo json_encode(['status'=> true, 'data'=>$data]);
            exit();
        }else{
            echo json_encode(['status'=> true, 'msg'=>'No data found.']);
            exit();
        }

    }


    public function get_dashboard_single_stats_data(){
        $request_body = get_json_data(true,['user_type'=>'admin']);
        $request_data = $request_body->data;

        $data = null;
        if($request_data->key == 'new_users'){
            $data = $this->AdminDashboardModel->get_users_created_graph_data($request_body);
        }
        if($request_data->key == 'invoice_statement'){
            $data = $this->AdminDashboardModel->get_invoice_statement_processed_graph_data($request_body);
        }


        if($data){
            echo json_encode(['status'=> true, 'data'=>$data]);
            exit();
        }else{
            echo json_encode(['status'=> true, 'msg'=>'No data found.']);
            exit();
        }
    }

    public function admin_header_data(){
        $request_body = get_json_data(true,['user_type'=>'admin']);
        $data = $this->AdminDashboardModel->admin_header_data_model($request_body);
        if($data){
            echo json_encode(['status'=> true, 'data'=>$data]);
            exit();
        }else{
            echo json_encode(['status'=> true, 'msg'=>'No data found.']);
            exit();
        }

    }

    public function admin_ai_review_data(){
        $request_body = get_json_data(true,['user_type'=>'admin']);
        $this->load->library('CustomCurlCall'); 
        $reviewType = $request_body->data->type; 
        $callDays = $request_body->data->duration;
        $base_url = base_url();
        $callType = ($callDays==1)?1:CRON_CALL_DEFAULT_TYPE;
        if($reviewType=='Statement'){
         $url = $base_url . 'FmcronCn/statement_ai_review_mapping_report/'.$callDays.'/'.$callType;
        } else {
          $url = $base_url . 'FmcronCn/invoice_ai_review_mapping_report/'.$callDays.'/'.$callType;
        }
        $result=$this->customcurlcall->request('POST', $url);
        $response = !empty($result) && is_json($result)? json_decode($result,true):[]; 
        if(!empty($response) && !empty($response['result'])){
            echo json_encode(['status'=> $response['result']['status'], 'msg'=>$response['result']['msg']]);
            exit();
        }else{
            echo json_encode(['status'=> false, 'msg'=>'Something Went Wrong']);
            exit();
        }
	}

}