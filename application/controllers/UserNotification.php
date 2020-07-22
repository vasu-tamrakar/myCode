<?php
defined('BASEPATH') || exit('No direct script access allowed');

class UserNotification extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->library('form_validation','pagination');
		$this->load->model('UserNotificationModel');
	}

	
	function get_notification_list(){
		$request_body = get_json_data(true);
		if(!empty($request_body->data))
		{
			$result = $this->UserNotificationModel->get_notification_list_model($request_body);
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
    	$request_body = get_json_data(true);
    	$notificationData = $this->UserNotificationModel->get_notification_detail_model($request_body);
    	if($notificationData){
			$response_ary = array('status'=>true, 'data' => $notificationData);
		} else {
			$response_ary = array('status'=>false, 'msg' => "No Data found." );
		}
		echo json_encode($response_ary);
		exit();
    }
    

	public function delete_notification(){
    	$request_body = get_json_data(true);
        $request_data = $request_body->data;
    	$notificationData = $this->UserNotificationModel->delete_notification_model($request_data, $request_body->user_id);
    	if($notificationData){
			$response_ary = array('status'=>true, 'msg' => 'Deleted Notification Successfully' );
		} else {
			$response_ary = array('status'=>false, 'msg' => "Something went wrong." );
		}
		echo json_encode($response_ary);
		exit();
    }
    

    public function read_all_notification(){
        $request_body = get_json_data(true);
        $result = $this->UserNotificationModel->read_all_notification_model($request_body);
        if($result){
            echo json_encode(array("status"=>true, "msg"=>"Successfully readed."));
        }else{
            echo json_encode(array("status"=>false, "msg"=>"process failed."));
        }
        
    }

    public function get_header_data(){
        $request_body = get_json_data(true);
        
        $data = $this->UserNotificationModel->header_data_model($request_body->user_id);
        if($data){
            echo json_encode(['status'=> true, 'data'=>$data]);
            exit();
        }else{
            echo json_encode(['status'=> true, 'msg'=>'No data found.']);
            exit();
        }

    }

}