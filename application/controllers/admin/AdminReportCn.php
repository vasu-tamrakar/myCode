<?php
defined('BASEPATH') || exit('No direct script access allowed');

require_once APPPATH . 'traits/formCustomValidation.php';

class AdminReportCn extends CI_Controller
{
    function __construct()
	{
		parent::__construct();
		$this->load->library('form_validation');
        $this->load->model('admin/AdminReportModel');
    }
    
    


    public function admin_report_data(){

        $request_body = get_json_data(true,['user_type'=>'admin']);
        $request_data = $request_body->data;

        $data = $this->AdminReportModel->admin_report_data_model($request_body);
        if($data){
            echo json_encode(['status'=> true, 'data'=>$data]);
            exit();
        }else{
            echo json_encode(['status'=> true, 'msg'=>'No data found.']);
            exit();
        }

    }


    
}