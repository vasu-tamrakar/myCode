<?php defined('BASEPATH') || exit('No direct script access allowed');

class AdminDashboardModel extends CI_Model
{
	function __construct()
	{
		parent::__construct(); 
		$this->load->model(['InvoiceModel','ExportModel','BasicModel','StatementModel']);
	}
	
	function getDashboardDetails(){
		$where = array('archive'=>0, 'status'=>1);
		$this->db->where($where);
		$num_rows['user'] = $this->db->count_all_results(TBL_PREFIX.'person');
		return $num_rows; 
	}
	
	public function admin_dashboard_data_model($request_body){

		$request_data = $request_body->data;
		$result = [];

		// vendors
		$this->db->select('COUNT(id) as val');
		$this->db->where("v.status = 1 AND v.archive=0");
		$this->db->from(TBL_PREFIX.'vendor v');
		$this->db->limit(1);
		$pending_vendor_query = $this->db->get();
		$pending_vendor_result = $pending_vendor_query->row();

		$this->db->select('COUNT(id) as val');
		$this->db->where("v.status = 2 AND v.archive=0");
		$this->db->from(TBL_PREFIX.'vendor v');
		$this->db->limit(1);
		$approve_vendor_query = $this->db->get();
		$approve_vendor_result = $approve_vendor_query->row();

		$this->db->select('COUNT(id) as val');
		$this->db->where("v.status = 3 AND v.archive=0");
		$this->db->from(TBL_PREFIX.'vendor v');
		$this->db->limit(1);
		$disapprove_vendor_query = $this->db->get();
		$disapprove_vendor_result = $disapprove_vendor_query->row();

		$result['vendors'] = [
			'pending' => $pending_vendor_result->val,
			'approved' => $approve_vendor_result->val,
			'disapproved' => $disapprove_vendor_result->val
		];


		// Users
		$this->db->select('COUNT(id) as val');
		$this->db->where("u.status = 0 AND u.archive=0");
		$this->db->from(TBL_PREFIX.'user u');
		$this->db->limit(1);
		$pending_users_query = $this->db->get();
		$pending_users_result = $pending_users_query->row();

		$this->db->select('COUNT(id) as val');
		$this->db->where("u.status = 1 AND u.archive=0");
		$this->db->from(TBL_PREFIX.'user u');
		$this->db->limit(1);
		$active_users_query = $this->db->get();
		$active_users_result = $active_users_query->row();


		$this->db->select('COUNT(id) as val');
		$this->db->where("u.status = 2 AND u.archive=0");
		$this->db->from(TBL_PREFIX.'user u');
		$this->db->limit(1);
		$inactive_users_query = $this->db->get();
		$inactive_users_result = $inactive_users_query->row();

		$result['users'] = [
			'pending' => $pending_users_result->val,
			'active' => $active_users_result->val,
			'inactive' => $inactive_users_result->val,
		];


		// Categories
		$this->db->select('COUNT(id) as val');
		$this->db->where("c.status = 1 AND c.archive=0");
		$this->db->from(TBL_PREFIX.'category c');
		$this->db->limit(1);
		$active_category_query = $this->db->get();
		$active_category_result = $active_category_query->row();

		$this->db->select('COUNT(id) as val');
		$this->db->where("c.status = 2 AND c.archive=0");
		$this->db->from(TBL_PREFIX.'category c');
		$this->db->limit(1);
		$inactive_category_query = $this->db->get();
		$inactive_category_result = $inactive_category_query->row();


		$result['categories'] = [
			'active' => $active_category_result->val,
			'inactive' => $inactive_category_result->val,
		];


		// new users
		$year1 = date("Y");
		$year2 = date("Y", strtotime('-1 years'));
		$data = (object)['year1' => $year1, 'year2' => $year2];

		$result['new_users_data'] = $this->get_users_created_graph_data($request_body);

		$result['invoice_statement_graph_data'] = $this->get_invoice_statement_processed_graph_data($request_body);


		return $result;
	}


	public function get_users_created_graph_data($request_body){

		$request_data = $request_body->data;
		$data = $request_data->new_users;

		// new users
		$year1 = ($data != null)? $data->year1: date("Y");
		$year2 = ($data != null)? $data->year2: date("Y", strtotime('-1 years'));


		$year1_start_end_dates = get_year_start_end_date_from_year($year1);
		$year2_start_end_dates = get_year_start_end_date_from_year($year2);


		$getUserTimezone = $request_body->time_zone;
		$getUserTimezone_sql = $request_body->time_zone_mysql?? '+00:00';

		$year1_dates = get_common_graph_filter_year(
			[ 
			'return_format' => 'Y-m-d',
			'user_date'=>date('Y-m-d',strtotime(date($year1.'-01-01')))
			]
		);
		$year2_dates = get_common_graph_filter_year(
			[
			'return_format' => 'Y-m-d',
			'user_date'=>date('Y-m-d',strtotime(date($year2.'-01-01')))
			]
		);

		// pr([$getUserTimezone,$getUserTimezone_sql, $year1_dates, $year2_dates]);

		$this->db->select(['COUNT(id) as value', "DATE_FORMAT(CONVERT_TZ(u.created,'+00:00', '".$getUserTimezone_sql."'), '%M') as month"]);
		$this->db->group_by("MONTH(CONVERT_TZ(u.created,'+00:00', '".$getUserTimezone_sql."'))"); 
		$this->db->where("DATE(CONVERT_TZ(u.created,'+00:00', '".$getUserTimezone_sql."')) BETWEEN '".$year1_dates["financial_start"]."' AND '".$year1_dates["financial_end"]."'");
		$this->db->from(TBL_PREFIX.'user u');
		$year1_users_query = $this->db->get();
		$year1_users_result = $year1_users_query->result();
 
		$year1_userData = (!empty($year1_users_result))?pos_index_change_array_data($year1_users_result,'month'):[];


		$this->db->select(['COUNT(id) as value', "DATE_FORMAT(CONVERT_TZ(u.created,'+00:00', '".$getUserTimezone_sql."'), '%M') as month"]);
		$this->db->group_by("MONTH(CONVERT_TZ(u.created,'+00:00', '".$getUserTimezone_sql."'))"); 
		$this->db->where("DATE(CONVERT_TZ(u.created,'+00:00', '".$getUserTimezone_sql."')) BETWEEN '".$year2_dates["financial_start"]."' AND '".$year2_dates["financial_end"]."'");
		$this->db->from(TBL_PREFIX.'user u');
		$year2_users_query = $this->db->get();
		$year2_users_result = $year2_users_query->result();
		$year2_userData = (!empty($year2_users_result))?pos_index_change_array_data($year2_users_result,'month'):[];


		$data = [];

		$labels = ["January", "February", "March", "April", "May","June", "July", "August", "September", "October", "November", "December"];

		$datasets = [
			['Month', (string)$year1, (string)$year2]
		];

		foreach ($labels as $key => $value) {
			$arr=[];

			$arr[0] = $value;

			if(isset($year1_userData[$value])){
				$arr[1] = (integer)$year1_userData[$value]['value'];
			}else{
				$arr[1] = 0;
			}

			if(isset($year2_userData[$value])){
				$arr[2] = (integer)$year2_userData[$value]['value'];
			}else{
				$arr[2] = 0;
			}

			$datasets[] = $arr;
		}

		$data['datasets'] = $datasets;

		return $data;

	}


	public function get_users_created_graph_data_financial($request_body){

		$request_data = $request_body->data;
		$data = $request_data->new_users;

		// new users
		$year1 = ($data != null)? $data->year1: date("Y");
		$year2 = ($data != null)? $data->year2: date("Y", strtotime('-1 years'));


		$year1_finanial_start_end_dates = get_financial_year_start_end_date_from_year($year1);
		$year2_finanial_start_end_dates = get_financial_year_start_end_date_from_year($year2);


		$this->db->select(['COUNT(id) as value', "DATE_FORMAT(u.created, '%M') as month"]);
		$this->db->group_by('MONTH(created)'); 
		$this->db->where('DATE(u.created) BETWEEN "'.$year1_finanial_start_end_dates["financial_start"].'" AND "'.$year1_finanial_start_end_dates["financial_end"].'"');
		$this->db->from(TBL_PREFIX.'user u');
		$year1_users_query = $this->db->get();
		$year1_users_result = $year1_users_query->result();
 
		$year1_userData = (!empty($year1_users_result))?pos_index_change_array_data($year1_users_result,'month'):[];


		$this->db->select(['COUNT(id) as value', "DATE_FORMAT(u.created, '%M') as month"]);	
		$this->db->group_by('MONTH(created)'); 
		$this->db->where('DATE(u.created) BETWEEN "'.$year2_finanial_start_end_dates["financial_start"].'" AND "'.$year2_finanial_start_end_dates["financial_end"].'"');
		$this->db->from(TBL_PREFIX.'user u');
		$year2_users_query = $this->db->get();
		$year2_users_result = $year2_users_query->result();
		$year2_userData = (!empty($year2_users_result))?pos_index_change_array_data($year2_users_result,'month'):[];


		$data = [];

		$years_data = [];
		$years_data['year1'] = [
			'start'=>$year1_finanial_start_end_dates["financial_start"],
			'end' => $year1_finanial_start_end_dates["financial_end"]
		];
		$years_data['year2'] = [
			'start'=>$year2_finanial_start_end_dates["financial_start"],
			'end' => $year2_finanial_start_end_dates["financial_end"]
		];

		$data['years_data'] = $years_data;

		$labels = ["July", "August", "September", "October", "November", "December","January", "February", "March", "April", "May","June"];

		$datasets = [
			['Month', (string)$year1, (string)$year2]
		];

		foreach ($labels as $key => $value) {
			$arr=[];

			$arr[0] = $value;

			if(isset($year1_userData[$value])){
				$arr[1] = (integer)$year1_userData[$value]['value'];
			}else{
				$arr[1] = 0;
			}

			if(isset($year2_userData[$value])){
				$arr[2] = (integer)$year2_userData[$value]['value'];
			}else{
				$arr[2] = 0;
			}

			$datasets[] = $arr;
		}

		$data['datasets'] = $datasets;

		return $data;

	}


	public function get_invoice_statement_processed_graph_data($request_body){

		$request_data = $request_body->data;
		$request_data = $request_data->invoice_statement;

		$key = isset($request_data->key)? $request_data->key : 'week';

		// $fin_year = get_current_n_previous_financial_year();
		$years = get_year_start_end_date_from_year();
		$getUserTimezone = $request_body->time_zone;
		$getUserTimezone_sql = $request_body->time_zone_mysql?? '+00:00';
		$year1_dates = get_common_graph_filter_year(
			[ 
			'user_timezone'=>$getUserTimezone,
			'return_format' => 'Y-m-d',
			]
		);

		$this->db->select("COUNT(i.id) as count");   
		$this->db->where("i.change_status=2 AND i.archive=0");

		if($key == "week"){
            $this->db->where("DATE(CONVERT_TZ(i.created,'+00:00', '".$getUserTimezone_sql."')) BETWEEN '" . $year1_dates['financial_week_start'] . "' AND '" . $year1_dates['financial_week_end'] . "'", NULL, false);
        }
        if($key == "month"){
           $this->db->where("DATE(CONVERT_TZ(i.created,'+00:00', '".$getUserTimezone_sql."')) BETWEEN '" . $year1_dates['financial_month_start'] . "' AND '" . $year1_dates['financial_month_end'] . "'", NULL, false);
        }
        if($key == "year"){
            $this->db->where("DATE(CONVERT_TZ(i.created,'+00:00', '".$getUserTimezone_sql."')) BETWEEN '".$year1_dates["financial_start"]."' AND '".$year1_dates["financial_end"]."'", NULL, false);
        }

        $this->db->from(TBL_PREFIX."invoice i");
        $query = $this->db->get();
        $invoiceProcessedResult= $query->row();


        $this->db->select("COUNT(s.id) as count");   
		$this->db->where("s.change_status=2 AND s.archive=0");

		if($key == "week"){
            $this->db->where("DATE(CONVERT_TZ(s.created,'+00:00', '".$getUserTimezone_sql."')) BETWEEN '" . $year1_dates['financial_week_start'] . "' AND '" . $year1_dates['financial_week_end'] . "'", NULL, false);
        }
        if($key == "month"){
            $this->db->where("DATE(CONVERT_TZ(s.created,'+00:00', '".$getUserTimezone_sql."')) BETWEEN '" . $year1_dates['financial_month_start'] . "' AND '" . $year1_dates['financial_month_end'] . "'", NULL, false);
        }
        if($key == "year"){
            $this->db->where("DATE(CONVERT_TZ(s.created,'+00:00', '".$getUserTimezone_sql."')) BETWEEN '".$year1_dates["financial_start"]."' AND '".$year1_dates["financial_end"]."'", NULL, false);
        }

        $this->db->from(TBL_PREFIX."statement s");
        $stementquery = $this->db->get();
        $statementProcessedResult= $stementquery->row();


        $datasets = [ 
        	(integer)$invoiceProcessedResult->count,
			(integer)$statementProcessedResult->count
        ];

        $data = $datasets;

        return $data;


	}


	public function admin_header_data_model($request_body){
		$request_data = $request_body->data;
		$user_id = $request_body->user_id;
		$result=array();
		// unread notification
		$this->db->select("COUNT(DISTINCT(n.id)) AS notification");
        $this->db->where(array("n.alert_type" => 2, "n.user_id" => 0, "n.is_read" => 2, "n.created_by_type" => 1,"n.archive" => 0));
        $this->db->from(TBL_PREFIX.'notification as n');
		$query = $this->db->get();
		$unreadNotification = $query->row();
		$userProfileImage = $this->get_profile_img_for_header_data($user_id); 
		$result['count_unread_notification'] = isset($unreadNotification->notification)?$unreadNotification->notification:0;
		$result['profile_image'] = !empty($userProfileImage)?$userProfileImage:'/images/user.svg';
		return $result;
	}

	public function get_profile_img_for_header_data($user_id){
		$whereArr=['status'=>1,'archive'=>0, 'id'=>$user_id];
		$result = $this->CommonModel->getDataWhere(['profile_image'], $whereArr, TBL_PREFIX.'admin_user', ['result_array'=>3]);
		$filename = ADMIN_SMALL_IMAGE.$result[0]->profile_image;
		$image='';
		if (file_exists($filename) && is_file($filename)) {
		   $image = $result[0]->profile_image;
		} 
		return $image;
	}
 
}