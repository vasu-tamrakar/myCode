<?php
defined('BASEPATH') || exit('No direct script access allowed');

class AdminNotificationCn extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->library('form_validation','pagination');
		$this->load->model('admin/AdminNotificationModel');
	}

	
	function get_notification_list(){
		$request_body = get_json_data(true,['user_type'=>'admin']);
		if(!empty($request_body->data))
		{
			$result = $this->AdminNotificationModel->get_notification_list_model($request_body);
			if(!empty($result)){
				$response_ary = array('status'=>true,'data' => $result );
			} else {
				$response_ary = array('status'=>false,'msg' => "No Record Found" );
			}
			echo json_encode($response_ary);
			exit();
		}
	}
	
	public function get_notification_detail(){
    	$request_body = get_json_data(true,['user_type'=>'admin']);
        $request_data = $request_body->data;
    	$result = $this->AdminNotificationModel->get_notification_detail_model($request_body);
    	if($result){
			$response_ary = array('status'=>true, 'data' => $result);
		} else {
			$response_ary = array('status'=>false, 'msg' => "No Data found." );
		}
		echo json_encode($response_ary);
		exit();
    }
    

	public function delete_notification(){
    	$request_body = get_json_data(true,['user_type'=>'admin']);
        $request_data = $request_body->data;
    	$notificationData = $this->AdminNotificationModel->delete_notification_model($request_data, $request_body->user_id);
    	if($notificationData){
			$response_ary = array('status'=>true, 'msg' => 'Deleted Notification Successfully' );
		} else {
			$response_ary = array('status'=>false, 'msg' => "Something went wrong." );
		}
		echo json_encode($response_ary);
		exit();
    }
    

    public function read_all_notification(){
        $request_body = get_json_data(true,['user_type'=>'admin']);
        $request_data = $request_body->data;
        $result = $this->AdminNotificationModel->read_all_notification_model($request_data);
        if($result){
            echo json_encode(array("status"=>true, "msg"=>"Successfully readed."));
        }else{
            echo json_encode(array("status"=>false, "msg"=>"process failed."));
        }
        
    }

}