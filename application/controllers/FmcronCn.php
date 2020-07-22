<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . 'traits/callBackGroundProcess.php';
class FmcronCn extends CI_Controller 
{
	use callBackGroundProcess;
	function __construct() {
		parent::__construct();
		$this->load->library('GmailMessageFetch');
		$this->load->model(['InvoiceModel','ExportModel','BasicModel','StatementModel','GoogleFetchModel']);
	}

	public function invoice_ai_review_mapping_report(int $numberOfDays=AI_REVIEW_MAPPING_REPORT_DEFAULT_DAY, $call_by=CRON_CALL_DEFAULT_TYPE){
		ini_set('display_errors', '1');
		error_reporting(E_ALL);
		$parms = [		
			'call_days'=>$numberOfDays>0?$numberOfDays:AI_REVIEW_MAPPING_REPORT_DEFAULT_DAY,
			'call_type'=>$call_by>0?$call_by:CRON_CALL_DEFAULT_TYPE 
	    ];
		$response = $this->call_background_process('invoice_ai_review_mapping',['interval_minute'=>25,'method_call'=>'invoice_ai_review_mapping_call','method_params'=>$parms]);
		echo json_encode($response);
		exit;

	}

	private function invoice_ai_review_mapping_call($cronId,$extraParms=[]){
	
		$callDays = $extraParms['call_days'] ?? AI_REVIEW_MAPPING_REPORT_DEFAULT_DAY;
		$callType = $extraParms['call_type'] ?? CRON_CALL_DEFAULT_TYPE;
		
		$currentDate = change_one_timezone_to_another_timezone(DATE_TIME,null,'UTC','Y-m-d');
		if($callType>0 && $callDays==AI_REVIEW_MAPPING_REPORT_DEFAULT_DAY){
			$fromDate = date('Y-m-d 00:00:00',strtotime($currentDate));
        	$toDate =date('Y-m-d 23:59:59',strtotime($currentDate)); 
		} else {
			$fromDate = date('Y-m-d 00:00:00',strtotime($currentDate.' -'.$callDays.' day'));
        	$toDate =date('Y-m-d 23:59:59',strtotime($currentDate.' -1 day')); 
		}
		$record = $this->InvoiceModel->invoice_ai_review_mapping_call(['from_date'=>$fromDate,'to_date'=>$toDate]);
		
        $responseMsg=[];	
		if (!empty($record)) 
		{ 
			$res = $this->ExportModel->export_invoice_data($record);
			if($res['status']==true){
				$file = $res['filename']; 
				$type='Invoice Export';
				$base_url = getenv('FINANCE_MANAGER_BASE_URL');
			    $url  = $base_url.'user/mediaShow/xls/'.urlencode(base64_encode(0)).'/'.urlencode(base64_encode($file));
				$data = [
						'name' =>'AI Team',
						'email'=> AI_REPORT_EMAIL,
						'cc' => AI_REPORT_EMAIL_TO_CC,
						'type' =>$type,
						'url'=>$url
				];
				$mail = ai_export_data_mail($data);
				$reviewData= array();
				if($mail){
					$reviewData = array(
						'from_date' => $fromDate,
						'to_date'=> $toDate,
						'complete_url'=> $url,
						'type' => 1, 
						'source_type'=>	1,
						'created' => $currentDate,
						'archive' => 0
					); 
					$this->BasicModel->insertRecords(TBL_PREFIX.'ai_review_url',$reviewData);
				}
			$responseMsg = ['status'=>true,'msg'=>count($record).' data found for review.'];
			}
		}else{
			$responseMsg  = ['status'=>false,'msg'=>'No data for review'];   
        }
        $this->BasicModel->updateRecords(TBL_PREFIX.'cron_status',['status'=>'1','response'=>json_encode($responseMsg)],['id'=>$cronId]);
        return ['status'=>true,'msg'=>'cron run successfully','parms'=>$extraParms, 'result'=>$responseMsg];
	}

	public function statement_ai_review_mapping_report(int $numberOfDays=AI_REVIEW_MAPPING_REPORT_DEFAULT_DAY,$call_by=CRON_CALL_DEFAULT_TYPE){
		ini_set('display_errors', '1');
		error_reporting(E_ALL);
		$parms = [		
			'call_days'=>$numberOfDays>0?$numberOfDays:AI_REVIEW_MAPPING_REPORT_DEFAULT_DAY,
			'call_type'=>$call_by>0?$call_by:CRON_CALL_DEFAULT_TYPE 
		];
		 
		$response = $this->call_background_process('statement_ai_review_mapping',['interval_minute'=>25,'method_call'=>'statement_ai_review_mapping_call','method_params'=>$parms]);
		echo json_encode($response);
		exit;

	}

	private function statement_ai_review_mapping_call($cronId,$extraParms=[]){
		 
		$callDays = $extraParms['call_days'] ?? AI_REVIEW_MAPPING_REPORT_DEFAULT_DAY;
		$currentDate = change_one_timezone_to_another_timezone(DATE_TIME,null,'UTC','Y-m-d');
		$callType = $extraParms['call_type'] ?? CRON_CALL_DEFAULT_TYPE;
		if($callType>0 && $callDays==AI_REVIEW_MAPPING_REPORT_DEFAULT_DAY){
			$fromDate = date('Y-m-d 00:00:00',strtotime($currentDate));
        	$toDate =date('Y-m-d 23:59:59',strtotime($currentDate)); 
		} else {
			$fromDate = date('Y-m-d 00:00:00',strtotime($currentDate.' -'.$callDays.' day'));
        	$toDate =date('Y-m-d 23:59:59',strtotime($currentDate.' -1 day')); 
		} 
		$record = $this->StatementModel->statement_ai_review_mapping_call(['from_date'=>$fromDate,'to_date'=>$toDate]);
        $responseMsg=[];	
		if (!empty($record)) 
		{  
			$res = $this->ExportModel->export_statement_data($record);	
			if($res['status']==true){
				$file = $res['filename']; 
				$type='Statement Export';
				$base_url = getenv('FINANCE_MANAGER_BASE_URL');
				$url  = $base_url.'user/mediaShow/xls/'.urlencode(base64_encode(0)).'/'.urlencode(base64_encode($file));
				$data = [
						'name' =>'AI Team',
						'email'=> AI_REPORT_EMAIL,
						'cc' => AI_REPORT_EMAIL_TO_CC,
						'type' =>$type,
						'url'=>$url
				];
				$mail = ai_export_data_mail($data);
				$reviewData= array();
				if($mail){
					$reviewData = array(
						'from_date' => $fromDate,
						'to_date'=> $toDate,
						'complete_url'=> $url,
						'type' => 2, 
						'source_type'=>	1,
						'created' => $currentDate,
						'archive' => 0
					); 
					$this->BasicModel->insertRecords(TBL_PREFIX.'ai_review_url',$reviewData);
				}
				$responseMsg = ['status'=>true,'msg'=>count($record).' data found for review.'];
			}
		}else{
			$responseMsg  = ['status'=>false,'msg'=>'No data for review'];   
        }
        $this->BasicModel->updateRecords(TBL_PREFIX.'cron_status',['status'=>'1','response'=>json_encode($responseMsg)],['id'=>$cronId]);
        return ['status'=>true,'msg'=>'cron run successfully','parms'=>$extraParms, 'result'=>$responseMsg];
	}

	public function forecasting_invoice(int $user_id=0,float $invoiceAmountIncrease=FORECASTING_INVOICE_AMOUNT){
		ini_set('display_errors', '1');
		error_reporting(E_ALL);
		$currentDate = change_one_timezone_to_another_timezone(DATE_TIME,null,'UTC','Y-m-d');
		$dataDate =get_filter_type('month',['user_date'=>$currentDate,'return_format'=>'Y-m-d']);
		$parms = [		
			'invoice_amount_increase'=>$invoiceAmountIncrease>0?$invoiceAmountIncrease:FORECASTING_INVOICE_AMOUNT,
			'user_id'=>$user_id,
			'previous_date_from'=>	date('Y-m-d',strtotime($dataDate['fromDate'] .'-1 years + 1 month')),
			'previous_date_to'=>$dataDate['toDate']
	    ];
		$response = $this->call_background_process('forecasting_invoice',['interval_minute'=>25,'method_call'=>'forecasting_invoice_call','method_params'=>$parms]);
		echo json_encode($response);
		exit;

	}

	private function forecasting_invoice_call($cronId,$extraParms=[]){
		$invoice_amount_increase = $extraParms['invoice_amount_increase'] ?? FORECASTING_INVOICE_AMOUNT;
		$userId = $extraParms['user_id']??0;
        $previous_date_from = $extraParms['previous_date_from']??0;
		$previous_date_to =$extraParms['previous_date_to']??0; 
		$this->BasicModel->updateRecords(TBL_PREFIX.'forecasting_invoice_line_item',['archive'=>'1'],['user_id'=>$userId]);
		$this->db->from(TBL_PREFIX."forecasting_invoice_line_item as sub_if");
		$this->db->where("sub_if.user_id=i.user_id and sub_if.category_id=cm.category_id and sub_if.archive=i.archive",null,false);
		$subQuery= $this->db->get_compiled_select();
		$this->db->select(["ROUND((sum(ili.amount) +((sum(ili.amount) * ".$invoice_amount_increase.")/100)),2) as amount","DATE_ADD(ili.invoice_date, INTERVAL 1 YEAR) as invoice_date","i.user_id","cm.category_id ","UTC_TIMESTAMP() as created "]);
		$this->db->from(TBL_PREFIX."invoice_line_item as ili");
		$this->db->join(TBL_PREFIX."invoice as i","i.id=ili.invoice_id and i.archive=ili.archive and i.user_id=".$userId,"inner");
		$this->db->join(TBL_PREFIX."category_mapping as cm","cm.id=ili.mapping_id and cm.archive=ili.archive","inner");
		$this->db->where(["ili.archive"=>0]);
		$this->db->where("ili.invoice_date between '".$previous_date_from."' and '".$previous_date_to."'",null,false);
		$this->db->where("not exists (".$subQuery.")",null,false);
		$this->db->group_by("DATE_FORMAT(ili.invoice_date,'%M %Y'),cm.category_id");
		$subQueryAll= $this->db->get_compiled_select();
		$this->db->query("INSERT INTO ".TBL_PREFIX."forecasting_invoice_line_item (amount,invoice_date,user_id,category_id,created) (".$subQueryAll.")");
		$rowCount = $this->db->affected_rows();
		$this->BasicModel->updateRecords(TBL_PREFIX.'cron_status',['status'=>'1','response'=>json_encode(['message'=>$rowCount. ' row added in invoice for user id '.$userId])],['id'=>$cronId]);
        return ['status'=>true,'msg'=>'cron run successfully','parms'=>$extraParms];
	}

	public function forecasting_statement(int $user_id=0,float $statementAmountIncrease=FORECASTING_STATEMENT_AMOUNT){
		ini_set('display_errors', '1');
		error_reporting(E_ALL);
		$currentDate = change_one_timezone_to_another_timezone(DATE_TIME,null,'UTC','Y-m-d');
		$dataDate =get_filter_type('month',['user_date'=>$currentDate,'return_format'=>'Y-m-d']);
		$parms = [		
			'statement_amount_increase'=>$statementAmountIncrease>0?$statementAmountIncrease:FORECASTING_STATEMENT_AMOUNT,
			'user_id'=>$user_id,
			'previous_date_from'=>	date('Y-m-d',strtotime($dataDate['fromDate'] .'-1 years + 1 month')),
			'previous_date_to'=>$dataDate['toDate']
	    ];
		$response = $this->call_background_process('forecasting_statement',['interval_minute'=>25,'method_call'=>'forecasting_statement_call','method_params'=>$parms]);
		echo json_encode($response);
		exit;

	}

	private function forecasting_statement_call($cronId,$extraParms=[]){
		$statement_amount_increase = $extraParms['statement_amount_increase'] ?? FORECASTING_STATEMENT_AMOUNT;
		$userId = $extraParms['user_id']??0;
        $previous_date_from = $extraParms['previous_date_from']??0;
		$previous_date_to =$extraParms['previous_date_to']??0; 
		$this->BasicModel->updateRecords(TBL_PREFIX.'forecasting_statement_line_item',['archive'=>'1'],['user_id'=>$userId]);
		$this->db->from(TBL_PREFIX."forecasting_statement_line_item as sub_if");
		$this->db->where("sub_if.user_id=s.user_id and sub_if.category_id=vm.category_id and sub_if.archive=s.archive",null,false);
		$subQuery= $this->db->get_compiled_select();
		$this->db->select(["ROUND((sum(sli.amount) +((sum(sli.amount) * ".$statement_amount_increase.")/100)),2) as amount","DATE_ADD(sli.transaction_date, INTERVAL 1 YEAR) as transaction_date","s.user_id","vm.category_id ","UTC_TIMESTAMP() as created,sli.transaction_type "]);
		$this->db->from(TBL_PREFIX."statement_line_item as sli");
		$this->db->join(TBL_PREFIX."statement as s","s.id=sli.statement_id and s.archive=sli.archive and s.user_id=".$userId,"inner");
		$this->db->join(TBL_PREFIX."vendor_mapping as vm","vm.id=sli.mapped_id and vm.archive=sli.archive","inner");
		$this->db->where(["sli.archive"=>0]);
		$this->db->where("sli.transaction_date between '".$previous_date_from."' and '".$previous_date_to."'",null,false);
		$this->db->where("not exists (".$subQuery.")",null,false);
		$this->db->group_by("DATE_FORMAT(sli.transaction_date,'%M %Y'),vm.category_id,sli.transaction_type");
		$subQueryAll= $this->db->get_compiled_select();
		$this->db->query("INSERT INTO ".TBL_PREFIX."forecasting_statement_line_item (amount,transaction_date,user_id,category_id,created,transaction_type) (".$subQueryAll.")");
		$rowCount = $this->db->affected_rows();
		$this->BasicModel->updateRecords(TBL_PREFIX.'cron_status',['status'=>'1','response'=>json_encode(['message'=>$rowCount. ' row added in statement for user id '.$userId])],['id'=>$cronId]);
        return ['status'=>true,'msg'=>'cron run successfully','parms'=>$extraParms];
	}

	public function fetch_statement_from_mail(int $numberOfDays=AI_REVIEW_MAPPING_REPORT_DEFAULT_DAY){
		ini_set('display_errors', '1');
		error_reporting(E_ALL);
		$parms = [		
			'limit'=>CRON_GMAIL_FETCH_USER_LIMIT,
			'page'=>0,
			'response'=>[]
	    ];
		$response = $this->call_background_process('fetch_statement',['interval_minute'=>25,'method_call'=>'fetch_statement_call','method_params'=>$parms]);
		echo json_encode($response);
		exit;
	}


	private function fetch_statement_call($cronId,$extraParms=[]){
		$limit = $extraParms['limit'] ?? CRON_GMAIL_FETCH_USER_LIMIT;
		$page  =  $extraParms['page'] ?? 0;
		$record = $this->GoogleFetchModel->get_gmail_access_request(['limit' => $limit , 'page' => $page]);
		$responseMsg=[];	
		if (!empty($record)) 
		{   
			foreach($record as $row){
				$userId = $row['id'];
				$this->gmailmessagefetch->setUserId($userId);
				$res = $this->gmailmessagefetch->checkAuthToken();
				if($res['status']){
					$result = $this->GoogleFetchModel->fetchCommonFunction($userId,2);
				}
			}
			if(!empty($result)){
				$responseMsg  = $result;  
				$extraParms['response'][$userId] = array_merge($extraParms['response'],$responseMsg);
			}
			$extraParms['page'] = $page+1;
			return $this->fetch_statement_call($cronId,$extraParms);
		}else{
			$responseMsg  = ['status'=>false,'msg'=>'No gmail for review'];  
			$this->BasicModel->updateRecords(TBL_PREFIX.'cron_status',['status'=>'1','response'=>json_encode($extraParms['response'])],['id'=>$cronId]);
        	return ['status'=>true,'msg'=>'cron run successfully','parms'=>$extraParms]; 
		}
	}
}