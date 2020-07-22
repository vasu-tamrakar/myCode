<?php
use Gufy\PdfToHtml\Config;
require_once APPPATH.'third_party/pdf_to_html_converter/vendor/autoload.php';
 
function get_json_data($token="",$extraPrams=[])
{	 
	$request_body = file_get_contents('php://input');
	$request_body = json_decode($request_body);
	$userType = $extraPrams['user_type'] ?? 'user';
	$extraPrams['user_type'] = $userType;
	if(!empty($request_body))
	{
		if($token)
		{
			$call_token = token_check($request_body,$extraPrams);
			
			if(isset($call_token) && $call_token['status'])
			{

				$request_body->user_id =  getUserIdFromLoginToken($request_body->token,$extraPrams);

				// get timezone area
			
					$getTimezone = getUserTimezone($request_body->user_id, $userType);
					$request_body->time_zone = $getTimezone->time_zone_name ?? 'UTC';	
					$request_body->time_zone_mysql = my_sql_timezone_utc_to_offset($request_body->time_zone);	

			

				return $request_body;
			}
			else{
				echo json_encode($call_token);
				exit();
			}
		}
		else{
			return $request_body;
		}
	}
	else{
		$data = array('status'=> false, 'msg'=>'request data not found');
		echo json_encode($data);
		exit();
	}
}


function get_form_data($token="",$extraPrams=[]){
	$request_body = $_POST;
	$request_body = (object) $request_body;
	$userType = $extraPrams['user_type'] ?? 'user';
	$extraPrams['user_type'] = $userType;
	if($token){
		$call_token = token_check($request_body,$extraPrams);
		if(isset($call_token) && $call_token['status'])
		{
			$request_body->user_id =  getUserIdFromLoginToken($request_body->token,$extraPrams);
			// get timezone area
		
				$getTimezone = getUserTimezone($request_body->user_id, $userType);
				$request_body->time_zone = $getTimezone->time_zone_name ?? 'UTC';	
				$request_body->time_zone_mysql = my_sql_timezone_utc_to_offset($request_body->time_zone);	
			
			return $request_body;
		}
		else{
			echo json_encode($call_token);
			exit();
		}
	}
	else{
		return $request_body;
	}
}

function getUserIdFromLoginToken($token,$userType=[]){
	$tableName = ($userType['user_type']=='admin') ? TBL_PREFIX.'admin_user_login_history' : TBL_PREFIX.'user_login_history';
	$CI = &get_instance();
	$data = $CI->CommonModel->getDataWhere(
		['user_id'],
		['token'=>$token, 'archive' => 0],
		 $tableName,
		['result_type'=>3]
	);
	return $data->user_id;
}


function getUserTimezone($id, $userType){
		
	$TableName = ($userType == 'admin')? 'admin_user' : 'user';

	$CI = &get_instance();
	$CI->db->select(['ct.time_zone_name', 'u.user_timezone']);
	$CI->db->where(["u.id"=>$id, "u.archive"=>0]);
	$CI->db->join(TBL_PREFIX.'country_timezone ct', "ct.id=u.user_timezone AND ct.archive=0");
	$CI->db->limit(1);
	$CI->db->from(TBL_PREFIX.$TableName.' u');
	$query = $CI->db->get();
	$result = $query->num_rows() > 0 ? $query->row():[];
	return $result;
}



function request_handlerFile($token="", $extraPrams=[])
{	
    $CI = &get_instance();
	$request_body = (object) $CI->input->post();
	$userType = $extraPrams['user_type'] ?? 'user';
	$extraPrams['user_type'] = $userType;
    if (!empty($request_body)) {
		$response = token_check($request_body,$extraPrams);
        if ($response['status']) {
        	 $request_body->user_id =  getUserIdFromLoginToken($request_body->token,$extraPrams);
            return $request_body;
        } else {
            echo json_encode($response);
            exit();
        }
    } else {
        echo json_encode(array('status' => false, 'token_status' => true, 'error' => system_msgs('verfiy_token_error')));
        exit();
    }
}

function do_upload($config_ary) {
    $CI = & get_instance();
    $response = array();
    if (!empty($config_ary)) {
        $directory_path = $config_ary['upload_path'] . $config_ary['directory_name'];
        $config['upload_path'] = $directory_path;
        $config['allowed_types'] = isset($config_ary['allowed_types']) ? $config_ary['allowed_types'] : '';
        $config['max_size'] = isset($config_ary['max_size']) ? $config_ary['max_size'] : '';
        $config['max_width'] = isset($config_ary['max_width']) ? $config_ary['max_width'] : '';
        $config['max_height'] = isset($config_ary['max_height']) ? $config_ary['max_height'] : '';
        $config['file_name'] = isset($config_ary['file_name']) ? $config_ary['file_name'] : '';
		$config['overwrite'] = isset($config_ary['overwrite']) ? $config_ary['overwrite'] : '';
		$config['encrypt_name'] = isset($config_ary['encrypt_name']) ? $config_ary['encrypt_name'] : FALSE;
        create_directory($directory_path);
        $CI->load->library('upload', $config);
        if (!$CI->upload->do_upload($config_ary['input_name'])) {
            $response = array('error' => $CI->upload->display_errors());
        } else {
            $response = array('upload_data' => $CI->upload->data());
        }
    }
    return $response;
}

function create_directory($directoryName) {
    if (!is_dir($directoryName)) {
        mkdir($directoryName, 0755);
        fopen($directoryName . "/index.html", "w");
    }
}

 function make_path($path) {
			 $dir = pathinfo($path, PATHINFO_DIRNAME);
			 if (is_dir($dir)) {
					 return true;
			 } else {
					 if (make_path($dir)) {
							 if (mkdir($dir)) {
									 chmod($dir, 0777);
									 return true;
							 }
					 }
			 }
			 return false;
	 }


function token_check($request_body,$userType=[])
{
	$tableName = ($userType['user_type']=='admin') ? TBL_PREFIX.'admin_user_login_history' : TBL_PREFIX.'user_login_history';
	if(!empty($request_body->token)){
		$CI = get_instance();
		$CI->load->model('BasicModel');
		$result = $CI->BasicModel->getRecordWhere(
			$tableName,
			array("token","ip_address","logout_time"),
			array("token"=>$request_body->token, 'archive'=>0)
		);
		$ip = get_client_ip_server();
		if(!empty($result)){
			if(/*($result->ip_address) == $ip &&*/ $result->token ==$request_body->token)
			{
				$diff = (strtotime(DATE_TIME) - strtotime($result->logout_time));
				if ($diff>0) {
					$res = $CI->BasicModel->deleteRecords($tableName, array('token'=> $result->token));
					if ($res===true){
						$data = array('status' => false, 'token_status' => true, 'msg' => 'You have been Logged out');
					}
				}
				else {
					$data_ = array(
						"logout_time" => date(DATE_TIME_FORMAT, strtotime("+60 minutes"))
					);
					$result = $CI->BasicModel->updateRecords($tableName, $data_, array('token' => $result->token));
					$data = array('status' => true, 'token_status' => false, 'msg' => 'Token updated');
				}
			}
			else
			{
				$data = array('status'=> false,'server_status'=>true, 'msg'=>'IP does not match');
			}
		}
		else{
			$data = array('status'=> false, 'token_status'=> true, 'msg'=>'Token does not exist');
		}
	}
	else
	{
		$data = array('status'=> false, 'token_status'=> true, 'msg'=>'Token does not exist');
	}
	return $data;
}

function pr($data)
{
	echo '<pre>';
	print_r($data);
	die;
}

function last_query($die = 0)
{
    $ci = &get_instance();
    echo $ci->db->last_query();
    if ($die == 1) {
        die;
    }
}

function encrypt_decrypt($action, $string) {
 
	$output = false;
	$encrypt_method = "AES-256-CBC";
	$secret_key = 'This is my secret key';
	$secret_iv = 'This is my secret iv';
  // hash
	$key = hash('sha256', $secret_key);
  // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
	$iv = substr(hash('sha256', $secret_iv), 0, 16);
	if ($action == 'encrypt') {
		$output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
		$output = encrypt_decrypt_special_cond($output, $action);
	} elseif ($action == 'decrypt') {
		$string = encrypt_decrypt_special_cond($string, $action);
		$output = openssl_decrypt($string, $encrypt_method, $key, 0, $iv);
	}
	return $output;
}

function encrypt_decrypt_special_cond($data, $type = '') {

	if ($type == 'encrypt') {
		$data = str_replace(array('+', '/', '='), array('94231', '24356', ''), $data);
	} elseif ($type == 'decrypt') {
		$data = str_replace(array('94231', '24356'), array('+', '/'), $data);
		$mod4 = strlen($data) % 4;
		if ($mod4) {
			$data .= substr('====', $mod4);
		}
	}
	return $data;
}

function get_client_ip_server() {
	$ipaddress = '';
	if (array_key_exists('HTTP_CLIENT_IP', @$_SERVER)) {
		$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (array_key_exists('HTTP_X_FORWARDED_FOR', @$_SERVER)) {
		$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} elseif (array_key_exists('HTTP_X_FORWARDED', @$_SERVER)) {
		$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
	} elseif (array_key_exists('HTTP_FORWARDED_FOR', @$_SERVER)) {
		$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
	} elseif (array_key_exists('HTTP_FORWARDED', @$_SERVER)) {
		$ipaddress = $_SERVER['HTTP_FORWARDED'];
	} elseif (array_key_exists('REMOTE_ADDR', @$_SERVER)) {
		$ipaddress = $_SERVER['REMOTE_ADDR'];
	} else {
		$ipaddress = 'UNKNOWN';
	}
	$ipaddresses = explode(',', $ipaddress);
	return isset($ipaddresses[0]) ? $ipaddresses[0] : 0;

}

function get_user_agent()
{
	$ci =& get_instance();
	$ci->load->library('user_agent');
	return  $ci->agent->agent_string();
}



function user_directory($spath,$id){
	make_path($spath.$id);
	$newdir=$spath;
	$subdir=$id;
	$curdir= getcwd();
	$path=$curdir."$newdir";
	if(is_dir($path))
	{ mkdir($path."$subdir",0777,true);	}
	return  $subdir;
}
 
/* function checkothertimezoneexample($date)
{
	echo 'assume given date in Australia '.br().$date.br();
	echo 'Australia/Melbourne to utc'.br();
	echo $utcTime =  change_one_timezone_to_another_timezone($date,'Australia/Melbourne',);
	echo br().'utc to Australia/Melbourne'.br();
	echo  change_one_timezone_to_another_timezone($utcTime,'UTC','Australia/Melbourne');
	# code...
} */

function create_date_store_in_db(){
	return change_one_timezone_to_another_timezone(DATE_TIME,null,'UTC');
}


function re_map_arr_index($val, $keys)
{
    if (isset($val[$keys]) && strtolower($keys) == 'date') {
        return !empty($val[$keys]) ? date('Y-m-d', strtotime($val[$keys])) : $val[$keys];
    } elseif (isset($val[$keys])) {
        return $val[$keys];
    }
}

function pos_index_change_array_data($arr, $keys)
{
    $data = json_decode(json_encode($arr), true);
    $fill_data = array_map('re_map_arr_index', $data, array_fill(0, count($data), $keys));
    if (!empty($fill_data)) {
        $fill_data = array_combine($fill_data, $data);
    }
    return $fill_data;
}

function get_current_n_previous_financial_year() {
    $years = [];
    $current_month = date('m');

    if (date('m') > 6) {
        $start_year = $financial_start = date('Y');
        $end_year = $financial_end = date('Y') + 1;
    } else {    
        $start_year = $financial_start = date('Y') - 1;
        $end_year = $financial_end = date('Y');
    }

    $years['financial_start'] = $financial_start = DateFormate($financial_start . '-07-01', 'Y-m-d');
    $years['financial_end'] = $financial_end = DateFormate($financial_end . '-06-30', 'Y-m-d');

    return $years;
}

function DateFormate($date, $formate = '')
{
    if ($formate != '')
        return date($formate, strtotime($date));
    else
        return date('Y-m-d H:i:s', strtotime($date));
}

function simple_array_from_key_value($request_array, $keyVal){


	$array =array();
	foreach ($request_array as $key => $value) {
		$array[] = $value[$keyVal];   
    }
    return $array;
}

function get_interval_month_wise($fromDate, $toDate) {
    $startDate = new DateTime($fromDate);
    $endDate = new DateTime($toDate);
    $dateInterval = new DateInterval('P1M');
    $datePeriod = new DatePeriod($startDate, $dateInterval, $endDate);
    $monthData = [];
    foreach ($datePeriod as $date) {
        $temp = ['no' => $date->format('m'), 'name' => $date->format('F'), 'year' => $date->format('Y')];
        // ['from_date' => $date->format('Y-m-01'), 'to_date' => $date->format('Y-m-t')];
        $monthData[] = $temp;
    }
    return $monthData;
}


function get_financial_year_start_end_date_from_year($year=null){
	$years = [];
    $current_month = date('m');

    $request_year = ($year!==null)? $year: date('Y');

    if (date('m') > 6) {

        $start_year = $financial_start = $request_year;
        $end_year = $financial_end = $request_year + 1;
    } else {

        $start_year = $financial_start = $request_year - 1;
        $end_year = $financial_end = $request_year;
    }

    $years['financial_start'] = $financial_start = DateFormate($financial_start . '-07-01', 'Y-m-d');
    $years['financial_end'] = $financial_end = DateFormate($financial_end . '-06-30', 'Y-m-d');

    return $years;
}


if(!function_exists('change_one_timezone_to_another_timezone')){
    function change_one_timezone_to_another_timezone($dateString,$timeZoneSource = null, $timeZoneTarget = null,$returnFormat='Y-m-d H:i:s')
  {
    if (empty($timeZoneSource)) {
      $timeZoneSource = date_default_timezone_get();
    }
    if (empty($timeZoneTarget)) {
      $timeZoneTarget = date_default_timezone_get();
    }

    $dt = new DateTime($dateString, new DateTimeZone($timeZoneSource));
    $dt->setTimezone(new DateTimeZone($timeZoneTarget));

    return $dt->format($returnFormat);
  }
}

function getPrimaryTimezoneFromCountryId($countryId){
	$ci =& get_instance();
	$data = $ci->CommonModel->getDataWhere(
		['id', 'time_zone_name'],
		['primary_timezone'=> 1, 'archive'=>0, 'country_id' =>$countryId ],
		TBL_PREFIX.'country_timezone',
		['result_type'=>3]
	);
	return $data;
}
function get_common_graph_filter_year($extarPram=[]) {
	$utcConversion = $extarPram['utc_timezone']??false;
	$userTimeZone = $extarPram['user_timezone']??'UTC';
	$compareDateFilter = $extarPram['compare_date_filter']??false;
	$returnFormat = $extarPram['return_format']??'Y-m-d H:i:s';
	$utcTimeZone ='UTC';
    $years = [];


	$financial_start =$financial_end= date('Y');
	$financial_week =$financial_week= date('Y-m-d');


	if($utcConversion){
		$years['financial_start'] = $financial_start = DateFormate(change_one_timezone_to_another_timezone($financial_start . '-01-01 00:00:00',$userTimeZone,$utcTimeZone), $returnFormat);
		$years['financial_end'] = $financial_end = DateFormate(change_one_timezone_to_another_timezone($financial_end . '-12-31 23:59:59',$userTimeZone,$utcTimeZone), $returnFormat);
	}else if($compareDateFilter){
		
		$yearFrom = get_filter_type('year',array_merge($extarPram,['user_date'=>$extarPram['user_date_from']]));
		$yearTo = get_filter_type('year',array_merge($extarPram,['user_date'=>$extarPram['user_date_to']]));
		$years['previous_financial_start'] = $yearFrom['fromDate'];
		$years['previous_financial_end'] =  $yearFrom['toDate'];
		$years['financial_start'] = $yearTo['fromDate'];
		$years['financial_end'] =  $yearTo['toDate'];
		$monthFrom = get_filter_type('month',array_merge($extarPram,['user_date'=>$extarPram['user_date_from']]));
		$monthTo = get_filter_type('month',array_merge($extarPram,['user_date'=>$extarPram['user_date_to']]));
		$years['previous_financial_month_start'] = $monthFrom['fromDate']; 
		$years['previous_financial_month_end'] = $monthFrom['toDate']; 
		$years['financial_month_start'] = $monthTo['fromDate']; 
		$years['financial_month_end'] = $monthTo['toDate']; 
		$quarterFrom = get_filter_type('quarter',array_merge($extarPram,['user_date'=>$extarPram['user_date_from']]));
		$quarterTo = get_filter_type('quarter',array_merge($extarPram,['user_date'=>$extarPram['user_date_to']]));
		$years['previous_financial_quarter_start'] = $quarterFrom['fromDate']; 
		$years['previous_financial_quarter_end'] = $quarterFrom['toDate']; 
		$years['financial_quarter_start'] = $quarterTo['fromDate']; 
		$years['financial_quarter_end'] = $quarterTo['toDate']; 
	}else{
		
		$year = get_filter_type('year',$extarPram);
		$years['financial_start'] = $financial_start = $year['fromDate'];
		$years['financial_end'] = $financial_end = $year['toDate'];

		$week = get_filter_type('week',$extarPram);
		$years['financial_week_start'] = $week['fromDate']; 
		$years['financial_week_end'] = $week['toDate']; 
		$month = get_filter_type('month',$extarPram);
		$years['financial_month_start'] = $month['fromDate']; 
		$years['financial_month_end'] = $month['toDate']; 
		$quarter = get_filter_type('quarter',$extarPram);
		$years['financial_quarter_start'] = $quarter['fromDate']; 
		$years['financial_quarter_end'] = $quarter['toDate']; 
		$years['previous_financial_start'] = date("Y-m-d", strtotime("-1 year", strtotime($financial_start)));	 
		$years['previous_financial_end'] = date("Y-m-d", strtotime("-1 year", strtotime($financial_end)));
		
		$years['previous_financial_week_start'] = date('Y-m-d', strtotime("-1 year", strtotime($week['fromDate'])));
		$years['previous_financial_week_end'] =  date('Y-m-d', strtotime("-1 year", strtotime($week['toDate'])));
		
		$years['previous_financial_month_start'] = date('Y-m-d', strtotime("-1 year", strtotime($month['fromDate'])));
		$years['previous_financial_month_end'] =  date('Y-m-d', strtotime("-1 year", strtotime($month['toDate'])));
		
		$years['previous_financial_quarter_start'] = date('Y-m-d', strtotime("-1 year", strtotime($quarter['fromDate']))); 
		$years['previous_financial_quarter_end'] = date('Y-m-d', strtotime("-1 year", strtotime($quarter['toDate'])));
		$nextQuarterFilter = $extarPram['next_quarter_filter']??false;
		if($nextQuarterFilter){
			$years['financial_next_quarter_start'] = date($returnFormat,strtotime($quarter['toDate'] .'first day of next month')); 
			$years['financial_next_quarter_end'] = date($returnFormat,strtotime($quarter['toDate'] .'last day of +3 month')); 
		}
		$futureYearFilter = $extarPram['future_year_filter']??false;
		if($futureYearFilter){
			$years['financial_future_start'] = date($returnFormat,strtotime($month['fromDate'] .'first day of next month')); 
			$years['financial_future_end'] = date($returnFormat,strtotime($month['toDate'] .'last day of +12 month')); 
		}
		$nextMonthFilter = $extarPram['next_month_filter']??false;
		if($nextMonthFilter){
			$years['financial_next_month_start'] = date($returnFormat,strtotime($month['fromDate'] .'first day of next month')); 
			$years['financial_next_month_end'] = date($returnFormat,strtotime($month['toDate'] .'last day of next month')); 
		}

	}

    return $years;
}

function stats_percentage_calculation($x, $y,$c,$l) {
    $x = (float) $x;
    $y = (float) $y;
    $percent = 0;
    if ($x != 0 || $y != 0) {
        if ($y == 0) {
            $percent = ($x * 100);
        } else {
            $percent = (($x - $y) / $y) * 100;
        }
        $percent = number_format((float) $percent, 2, '.', '');
    }
    #1 +,2-,3=
    $data = array(
		'label' => $l, 
        'current' => $x,
        'previous' => $y,
		'category' => $c,
		'percent' => ($y == 0) ? 100 : abs($percent),
        'status' => ($percent > 0) ? 1 : (($percent == 0) ? 3 : 2),
    );
    return $data;
}

function get_filter_type($viewType = 'week', $extarPram = [])
{
	$userTimeZone = $extarPram['user_timezone']??'UTC';
	$returnFormat = $extarPram['return_format']??'Y-m-d H:i:s';
	$userDate = $extarPram['user_date']??DATE_TIME;
	$currentDate = change_one_timezone_to_another_timezone($userDate,'UTC',$userTimeZone,$returnFormat);
	$currentDate = new DateTime($currentDate);
	$fromDate = firstDayOf($viewType,$currentDate);
	$toDate = lastDayOf($viewType,$currentDate);
	$fromDateString =$fromDate->format($returnFormat);
	$toDateString =$toDate->format($returnFormat);
    return ['fromDate' => $fromDateString, 'toDate' => $toDateString];
}

 /**
 * Return the first day of the Week/Month/Quarter/Year that the
 * current/provided date falls within
 *
 * @param string   $period The period to find the last day of. ('year', 'quarter', 'month', 'week')
 * @param DateTime $date   The date to use instead of the current date
 *
 * @return DateTime
 * @throws InvalidArgumentException
 */
function firstDayOf($period, DateTime $date = null) {
	$period = strtolower($period);
	//$validPeriods = array('year', 'quarter', 'month', 'week');
  
	$newDate = ($date === null) ? new DateTime() : clone $date;
  
	switch ($period) {
	case 'year':
	  $newDate->modify('first day of january ' . $newDate->format('Y'));
	  break;
	case 'quarter':
	  $month = $newDate->format('n');
  
	  if ($month < 4) {
		$newDate->modify('first day of january ' . $newDate->format('Y'));
	  } elseif ($month > 3 && $month < 7) {
		$newDate->modify('first day of april ' . $newDate->format('Y'));
	  } elseif ($month > 6 && $month < 10) {
		$newDate->modify('first day of july ' . $newDate->format('Y'));
	  } elseif ($month > 9) {
		$newDate->modify('first day of october ' . $newDate->format('Y'));
	  }
	  break;
	case 'month':
	  $newDate->modify('first day of this month');
	  break;
	case 'week':
	  //$newDate->modify(($newDate->format('w') === '0') ? 'monday last week' : 'monday this week');
	  $newDate->modify('monday this week');
	  break;
	}
  
	return $newDate;
  }

  /**
 * Return the last day of the Week/Month/Quarter/Year that the
 * current/provided date falls within
 *
 * @param string   $period The period to find the last day of. ('year', 'quarter', 'month', 'week')
 * @param DateTime $date   The date to use instead of the current date
 *
 * @return DateTime
 * @throws InvalidArgumentException
 */
function lastDayOf($period, DateTime $date = null) {
	$period = strtolower($period);
	//$validPeriods = array('year', 'quarter', 'month', 'week');
	$newDate = ($date === null) ? new DateTime() : clone $date;
  
	switch ($period) {
	case 'year':
	  $newDate->modify('last day of december ' . $newDate->format('Y'));
	  break;
	case 'quarter':
	  $month = $newDate->format('n');
  
	  if ($month < 4) {
		$newDate->modify('last day of march ' . $newDate->format('Y'));
	  } elseif ($month > 3 && $month < 7) {
		$newDate->modify('last day of june ' . $newDate->format('Y'));
	  } elseif ($month > 6 && $month < 10) {
		$newDate->modify('last day of september ' . $newDate->format('Y'));
	  } elseif ($month > 9) {
		$newDate->modify('last day of december ' . $newDate->format('Y'));
	  }
	  break;
	case 'month':
	  $newDate->modify('last day of this month');
	  break;
	case 'week':
	  $newDate->modify(($newDate->format('w') === '0') ? 'now' : 'sunday this week');
	  break;
	}
  
	return $newDate;
  }
  function get_category_color(){
	return json_decode(CATEGORY_COLOR,true);
  }
  function get_category_color_for_graph($ids=[]){
	  $data = get_category_color();
	  $color = [];
	  if(!empty($ids)){
		  foreach($ids as $val){
			$color[]=$data[$val]??DEFAULT_CATEGORY_COLOR;
		  }
	  }
	  return $color;

  }

  function find_max_tick(array $result, array $params) {
    $finalmaxData = [];
    $returnData = 0;
    if (!empty($params) && !empty($result)) {
        foreach ($params as $val) {
            $index_key = explode("+", $val);
            if (count($index_key) > 1) {
                $maxData = [];
                foreach ($index_key as $valkey) {
                    $maxData[] = max(array_column($result, $valkey));
                }
                $finalmaxData[] = array_sum($maxData);
            } else {
                $finalmaxData[] = max(array_column($result, $index_key[0]));
            }
        }
        $returnData = max($finalmaxData);
    }

    return $returnData > DAFAULT_TICKS ? $returnData : DAFAULT_TICKS;
}



function get_year_start_end_date_from_year($year=null){
	$years = [];

    $request_year = ($year!==null)? $year: date('Y');
   
    $years['start'] = DateFormate($request_year . '-01-01', 'Y-m-d');
    $years['end'] = DateFormate($request_year . '-12-31', 'Y-m-d');

    return $years;
}


if (!function_exists('getDatesFromRange')) {
    function getDatesFromRange($startDate, $endDate, $format = DATE_FORMAT_DB,$indexStartOn=0)
    {
        $startDate = $startDate != '' ? $startDate : date($format);
        $endDate = $endDate != '' ? $endDate : date($format);
        $array = array();
        $interval = new DateInterval('P1D');
        $realEnd = new DateTime($endDate);
        $realEnd->add($interval);
        $period = new DatePeriod(new DateTime($startDate), $interval, $realEnd);
        foreach ($period as $date) {
			$array[$indexStartOn] = $date->format($format);
			$indexStartOn++;
        }
        return $array;
    }
}

if (!function_exists('getMonthFromRange')) {
    function getMonthFromRange($startDate, $endDate, $format = DATE_FORMAT_DB,$indexStartOn=0)
    {
        $startDate = $startDate != '' ? $startDate : date($format);
        $endDate = $endDate != '' ? $endDate : date($format);
        $array = array();
        $realStart = (new DateTime($startDate))->modify('first day of this month');
		$realEnd = (new DateTime($endDate))->modify('first day of this month');
        $interval = new DateInterval('P1M');
		$realEnd->add($interval);
        
        $period = new DatePeriod($realStart, $interval, $realEnd);
        foreach ($period as $date) {
			$array[$indexStartOn] = $date->format("Y-m-01");
			$indexStartOn++;
        }
        return $array;
    }
}
function merge_multidimensional_array_values_by_key_in_array_format($arr, $keys)
{
    $data_return = array();
    if (!empty($arr)) {
        foreach ($arr as $key => $value) {
            if (isset($value[$keys])) {
                $data_return[$value[$keys]][] = $value;
            }
        }
    }
    return $data_return;

}




function get_all_dates_of_month($request_date = null){
    $date = isset($request_date)? $request_date: date(DATE_TIME_FORMAT);
    $last_date = date("t", strtotime($date));

    $arr = [];
    for ($i=1; $i <=$last_date ; $i++) { 
        $arr[] = $i;
    }
    return $arr;

}
function getEcxcelColumnNameGetByIndex($index = 0)
{
    $ci = &get_instance();
    $ci->load->library("excel");
    if ($index > 0) {
        $index--;
    }
    return PHPExcel_Cell::stringFromColumnIndex($index);
}

function cellsToMergeByColsRow($start = -1, $end = -1, $row = -1){
    $merge = 'A1:A1';
    if($start>=0 && $end>=0 && $row>=0){
        $start = PHPExcel_Cell::stringFromColumnIndex($start);
        $end = PHPExcel_Cell::stringFromColumnIndex($end);
        $merge = "$start{$row}:$end{$row}";
    }
    return $merge;
}



function my_sql_timezone_utc_to_offset($requested_timezone = "UTC"){
	$gmtTimezone = new DateTimeZone($requested_timezone);
	$myDateTime = new DateTime(DATE_TIME, $gmtTimezone);
	return $myDateTime->format('P');
}
function getMiscellaneousCatdetail(){
	$ci = &get_instance();
	$ci->db->select('id,parent_id,category_name,key_name,status');
    $ci->db->where(array('key_name'=>'Miscellaneous','status'=>1,'archive'=>0));
	$queryMiscell = $ci->db->get(TBL_PREFIX.'category');
	if($queryMiscell->num_rows() > 0){
		return $queryMiscell->row();
	}else{
		return false;
	}
}
function is_json($string)
{
    $data = json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE) ? true : FALSE;
}

function getSortBy($sorted, $dateType= [], $defaultParam=['orderBy'=> '', 'direction'=>'DESC']){
	$data=array();
  if (!empty($sorted)) {
	  if (!empty($sorted[0]->id)) {
		$data['orderBy'] = $dateType[$sorted[0]->id] ?? $sorted[0]->id;
		$data['direction'] = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
	  }
	} else {
	  $data['orderBy'] = $defaultParam['orderBy'] ?? '';
	  $data['direction'] = $defaultParam['direction'] ?? 'DESC';
	}
	return $data;
}
?>


