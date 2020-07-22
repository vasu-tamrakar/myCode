<?php defined('BASEPATH') || exit('No direct script access allowed');

class AdminReportModel extends CI_Model
{
	function __construct()
	{
		parent::__construct(); 
	}
	
	private function getDaysAr(){
		return ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
	}

	private function getMonthsAr(){
		return ["January", "February", "March", "April", "May","June", "July", "August", "September", "October", "November", "December"];
	}

	private function getDatesArr($date = null){
		$dates = get_all_dates_of_month($date);
		return $dates;
	}


	public function admin_report_data_model($request_body){

		$data = [];
		$data['users_data'] = $this->admin_report_users_data($request_body);
		$data['invoice_statement_data'] = $this->admin_report_invoice_and_statement_processed_data($request_body);

		return $data;


	}
	
	public function admin_report_users_data($request_body){

		$request_data = $request_body->data;
		$userTimeZone = $request_body->time_zone??'UTC';
		$getUserTimezone_sql = $request_body->time_zone_mysql?? '+00:00';

		$key = $request_data->key ?? 'current_week';


		$callParm = [
			'return_format'=>'Y-m-d',
			'user_timezone'=>$userTimeZone
		];

		if($key == "selected_month"){
			$callParm['user_date'] = date('Y-m-d', strtotime($request_data->month." +1 day"));
		}
		if($key == "selected_year"){
			$callParm['user_date'] =date('Y-m-d', strtotime($request_data->year." +1 day"));
		}

		$fin_year = get_common_graph_filter_year($callParm);



		$Days = $this->getDaysAr();
		$Months = $this->getMonthsAr();
		$CurrentMonthDates = $this->getDatesArr($fin_year['financial_month_start']);

		$this->db->select([
			"COUNT(u.id) as value"]);   
		$this->db->where("u.archive=0");

		if($key == "current_week"){

			$this->db->select("DAYNAME(CONVERT_TZ(u.created,'+00:00', '".$getUserTimezone_sql."')) as day");
			$this->db->group_by("DAYNAME(CONVERT_TZ(u.created,'+00:00', '".$getUserTimezone_sql."'))"); 

            $this->db->where("DATE(CONVERT_TZ(u.created,'+00:00', '".$getUserTimezone_sql."')) BETWEEN '"  . $fin_year['financial_week_start'] . "' AND '" . $fin_year['financial_week_end'] . "'", NULL, false);
        }

        if($key == "current_month" || $key == "selected_month"){


        	$this->db->select("DAY(CONVERT_TZ(u.created,'+00:00', '".$getUserTimezone_sql."')) as day");
        	$this->db->group_by("DAY(CONVERT_TZ(u.created,'+00:00', '".$getUserTimezone_sql."'))"); 
        	 $this->db->where("DATE(CONVERT_TZ(u.created,'+00:00', '".$getUserTimezone_sql."')) BETWEEN '" . $fin_year['financial_month_start'] . "' AND '" . $fin_year['financial_month_end'] . "'", NULL, false);
        }

        if($key == "current_year" || $key == "selected_year"){

			$this->db->select("DATE_FORMAT(CONVERT_TZ(u.created,'+00:00', '".$getUserTimezone_sql."'), '%M') as month");
			$this->db->group_by("MONTH(CONVERT_TZ(u.created,'+00:00', '".$getUserTimezone_sql."'))");
			$this->db->where("DATE(CONVERT_TZ(u.created,'+00:00', '".$getUserTimezone_sql."')) BETWEEN '" . $fin_year['financial_start'] . "' AND '" . $fin_year['financial_end'] . "'", NULL, false);
        }

       

        $this->db->from(TBL_PREFIX.'user u');
		$users_query = $this->db->get();
		$users_result = $users_query->result();

		$data = [];
		$users_data = [];

		if($key == "current_week"){

			$userData = (!empty($users_result))?pos_index_change_array_data($users_result,'day'):[];
			$users_data = [
				['Days', 'Users']
			];

			foreach ($Days as $key => $value) {
				if(isset($userData[$value])){
					$users_data[] = [$value, (integer)$userData[$value]['value']];
				}else{
					$users_data[] = [$value, 0];
				}
				
			}
		}


		if($key == "current_month" || $key == "selected_month" ){

			$Month = date('M', strtotime($fin_year['financial_month_start']));
			
			$userData = (!empty($users_result))?pos_index_change_array_data($users_result,'day'):[];
			$users_data = [
				['Days', 'Users']
			];

			foreach ($CurrentMonthDates as $key => $value) {
				if(isset($userData[$value])){
					$users_data[] = [(string)$value .' ' .$Month, (integer)$userData[$value]['value']];
				}else{
					$users_data[] = [(string)$value .' ' .$Month, 0];
				}
				
			}

		}

		if($key == "current_year" || $key == "selected_year"){
			$Year = date('Y', strtotime($fin_year['financial_start']));
			$userData = (!empty($users_result))?pos_index_change_array_data($users_result,'month'):[];
			$users_data = [
				['Month', 'Users']
			];

			foreach ($Months as $key => $value) {
				if(isset($userData[$value])){
					$users_data[] = [$value . " $Year", (integer)$userData[$value]['value']];
				}else{
					$users_data[] = [$value. " $Year", 0];
				}
				
			}
		}

		return $users_data;
	}



	public function admin_report_invoice_and_statement_processed_data($request_body){

		$request_data = $request_body->data;
		$userTimeZone = $request_body->time_zone??'UTC';
		$getUserTimezone_sql = $request_body->time_zone_mysql?? '+00:00';

		$key = $request_data->key ?? 'current_week';

		$callParm = ['return_format'=>'Y-m-d','user_timezone'=>$userTimeZone];

		if($key == "selected_month"){
			$callParm['user_date'] =date('Y-m-d', strtotime($request_data->month." +1 day"));
		}
		if($key == "selected_year"){
			$callParm['user_date'] =date('Y-m-d', strtotime($request_data->year." +1 day"));
		} 

		$fin_year = get_common_graph_filter_year($callParm);

		$Days = $this->getDaysAr();
		$Months = $this->getMonthsAr();
		$CurrentMonthDates = $this->getDatesArr($fin_year['financial_month_start']);

		// processed Invoice
		$this->db->select("COUNT(i.id) as value");  
		$this->db->where("i.change_status=2 AND i.archive=0");

		if($key == "current_week"){

			$this->db->select("DAYNAME(CONVERT_TZ(i.created,'+00:00', '".$getUserTimezone_sql."')) as day");
			$this->db->group_by("DAYNAME(CONVERT_TZ(i.created,'+00:00', '".$getUserTimezone_sql."'))"); 

            $this->db->where("DATE(CONVERT_TZ(i.created,'+00:00', '".$getUserTimezone_sql."')) BETWEEN '"  . $fin_year['financial_week_start'] . "' AND '" . $fin_year['financial_week_end'] . "'", NULL, false);

        }


        if($key == "current_month" || $key == "selected_month"){

        	$this->db->select("DAY(CONVERT_TZ(i.created,'+00:00', '".$getUserTimezone_sql."')) as day");
        	$this->db->group_by("DAY(CONVERT_TZ(i.created,'+00:00', '".$getUserTimezone_sql."'))"); 
        	$this->db->where("DATE(CONVERT_TZ(i.created,'+00:00', '".$getUserTimezone_sql."')) BETWEEN '" . $fin_year['financial_month_start'] . "' AND '" . $fin_year['financial_month_end'] . "'", NULL, false);

        }

        if($key == "current_year" || $key == "selected_year"){

        	$this->db->select("DATE_FORMAT(CONVERT_TZ(i.created,'+00:00', '".$getUserTimezone_sql."'), '%M') as month");
			$this->db->group_by("MONTH(CONVERT_TZ(i.created,'+00:00', '".$getUserTimezone_sql."'))");
			$this->db->where("DATE(CONVERT_TZ(i.created,'+00:00', '".$getUserTimezone_sql."')) BETWEEN '" . $fin_year['financial_start'] . "' AND '" . $fin_year['financial_end'] . "'", NULL, false);
		
        }


        $this->db->from(TBL_PREFIX."invoice i");
        $query = $this->db->get();
        $invoiceProcessedResult= $query->result();



        // processed statements
        $this->db->select("COUNT(s.id) as value");   
		$this->db->where("s.change_status=2 AND s.archive=0");

		if($key == "current_week"){

			$this->db->select("DAYNAME(CONVERT_TZ(s.created,'+00:00', '".$getUserTimezone_sql."')) as day");
			$this->db->group_by("DAYNAME(CONVERT_TZ(s.created,'+00:00', '".$getUserTimezone_sql."'))"); 

            $this->db->where("DATE(CONVERT_TZ(s.created,'+00:00', '".$getUserTimezone_sql."')) BETWEEN '"  . $fin_year['financial_week_start'] . "' AND '" . $fin_year['financial_week_end'] . "'", NULL, false);

        }


        if($key == "current_month" || $key == "selected_month"){

        	$this->db->select("DAY(CONVERT_TZ(s.created,'+00:00', '".$getUserTimezone_sql."')) as day");
        	$this->db->group_by("DAY(CONVERT_TZ(s.created,'+00:00', '".$getUserTimezone_sql."'))"); 
        	$this->db->where("DATE(CONVERT_TZ(s.created,'+00:00', '".$getUserTimezone_sql."')) BETWEEN '" . $fin_year['financial_month_start'] . "' AND '" . $fin_year['financial_month_end'] . "'", NULL, false);

        }

        if($key == "current_year" || $key == "selected_year"){

        	$this->db->select("DATE_FORMAT(CONVERT_TZ(s.created,'+00:00', '".$getUserTimezone_sql."'), '%M') as month");
			$this->db->group_by("MONTH(CONVERT_TZ(s.created,'+00:00', '".$getUserTimezone_sql."'))");
			$this->db->where("DATE(CONVERT_TZ(s.created,'+00:00', '".$getUserTimezone_sql."')) BETWEEN '" . $fin_year['financial_start'] . "' AND '" . $fin_year['financial_end'] . "'", NULL, false);
		
        }

        $this->db->from(TBL_PREFIX."statement s");
        $statementQuery = $this->db->get();
        $statementProcessedResult= $statementQuery->result();

        // create datasets for both invoice and statement
		$invoice_statement_data = [];

		if($key == "current_week"){

			$invoiceData = (!empty($invoiceProcessedResult))?pos_index_change_array_data($invoiceProcessedResult,'day'):[];
			$statementData = (!empty($statementProcessedResult))?pos_index_change_array_data($statementProcessedResult,'day'):[];

			$invoice_statement_data = [
				['Days', 'Invoices', 'Statements']
			];

			foreach ($Days as $key => $value) {

				$arr = [$value, 0, 0];
				if(isset($invoiceData[$value])){
					$arr[1] = (integer)$invoiceData[$value]['value'];
				}
				if(isset($statementData[$value])){
					$arr[2] = (integer)$statementData[$value]['value'];
				}
				$invoice_statement_data[] = $arr;
			
			}
		}

		if($key == "current_month" || $key == "selected_month"){

			$invoiceData = (!empty($invoiceProcessedResult))?pos_index_change_array_data($invoiceProcessedResult,'day'):[];
			$statementData = (!empty($statementProcessedResult))?pos_index_change_array_data($statementProcessedResult,'day'):[];

			$invoice_statement_data = [
				['Days', 'Invoices', 'Statements']
			];
			$Month = date('M', strtotime($fin_year['financial_month_start']));
			foreach ($CurrentMonthDates as $key => $value) {

				$arr = [(string)$value .' ' .$Month, 0, 0];
				if(isset($invoiceData[$value])){
					$arr[1] = (integer)$invoiceData[$value]['value'];
				}
				if(isset($statementData[$value])){
					$arr[2] = (integer)$statementData[$value]['value'];
				}
				$invoice_statement_data[] = $arr;
			
			}
		}

		if($key == "current_year" || $key == "selected_year"){
			$Year = date('Y', strtotime($fin_year['financial_start']));
			$invoiceData = (!empty($invoiceProcessedResult))?pos_index_change_array_data($invoiceProcessedResult,'month'):[];
			$statementData = (!empty($statementProcessedResult))?pos_index_change_array_data($statementProcessedResult,'month'):[];

			$invoice_statement_data = [
				['Days', 'Invoices', 'Statements']
			];

			foreach ($Months as $key => $value) {

				$arr = [(string)$value . " $Year", 0, 0];
				if(isset($invoiceData[$value])){
					$arr[1] = (integer)$invoiceData[$value]['value'];
				}
				if(isset($statementData[$value])){
					$arr[2] = (integer)$statementData[$value]['value'];
				}
				$invoice_statement_data[] = $arr;
			
			}
		}

		return $invoice_statement_data;

	}
	
}