<?php defined('BASEPATH') || exit('No direct script access allowed');

class ReportModel extends CI_Model
{
	function __construct()
	{
        parent::__construct(); 
        $this->load->model('CommonModel');
	}
    
    
    public function report_data_model($data){      
        $data->year1 = $data->data->expense_filter->key1??2020;
        $data->year2 =  $data->data->expense_filter->key2??2019;
        $year1 = ($data != null)? $data->year1: date("Y");
        $year2 = ($data != null)? $data->year2: date("Y", strtotime('-1 years'));
        
        $fin_year = get_current_n_previous_financial_year();
        $invoice =  $this->getInvoiceCount($fin_year); 
        $statement = $this->getStatementCount($fin_year);
        $months_no_name = get_interval_month_wise($fin_year['financial_start'],$fin_year['financial_end']);
       
        foreach ($months_no_name as $value) {
            $months[] = $value['name'];
        }
        $labels = $months; 
       
        $datasets=[];
        $datasss= array();
		foreach ($labels as  $value) {
			$arr=[];
            $arr[0] = $value;
			if(isset($invoice[$value])){
				$arr[1] = (integer)$invoice[$value]['value'];
			}else{
				$arr[1] = 0;
			}

			if(isset($statement[$value])){
				$arr[2] = (integer)$statement[$value]['value'];
			}else{
				$arr[2] = 0;
            }
            $d1[] =  $arr[1];
            $d2[] =  $arr[2];
        }
        $datasets['invoice'] = (object) array(
            'label'=> (string)$year1,
            'data' => $d1,
            'backgroundColor'=> "#373b94",
            'labelSuffix'=> "%"
         );

         $datasets['statement'] = (object) array(
            'label'=> (string)$year2,
            'data' => $d2,
            'backgroundColor'=> "#de455e",
            'labelSuffix'=> "%"
         );
        $datasss['labels'] = $labels; 
        $datasss['datasets'] = $datasets;
        return $datasss; 
    }

    function getInvoiceCount($fin_year){

        $this->db->select(['COUNT(id) as value', "DATE_FORMAT(u.created, '%M') as month"]);
		$this->db->group_by('MONTH(created)'); 
		$this->db->where('DATE(u.created) BETWEEN "'.$fin_year["financial_start"].'" AND "'.$fin_year["financial_end"].'"');
        $this->db->from(TBL_PREFIX.'invoice u');
        $invoice_query = $this->db->get();
        $invoice_result = $invoice_query->result();
        return (!empty($invoice_result))?pos_index_change_array_data($invoice_result,'month'):[];
    
    }

    function getStatementCount($fin_year){
        $this->db->select(['COUNT(id) as value', "DATE_FORMAT(u.created, '%M') as month"]);
		$this->db->group_by('MONTH(created)'); 
		$this->db->where('DATE(u.created) BETWEEN "'.$fin_year["financial_start"].'" AND "'.$fin_year["financial_end"].'"');
        $this->db->from(TBL_PREFIX.'statement u');
        $statement_query = $this->db->get();
        $statement_result = $statement_query->result();
       return (!empty($statement_result))?pos_index_change_array_data($statement_result,'month'):[];
 
    }

    function getMonthsCount($all_status, $monthNumber) {
        $values = array();
        foreach ($all_status as $status) {
            if ($monthNumber == $status->m) {
                $values = $status->total_count;
            }
        }
        return $values;
    }

    public function get_invoice_categories_pie_data(int $userId=0,$extraParm=[]){    
        $userTimezone = $extraParm['user_timezone']??'UTC';   
        $type = $extraParm['type']??'week';   
        $select_type = $extraParm['select_type']??'month';   
        $userDate = $extraParm['user_date']??'';   
        $callParm = ['return_format'=>'Y-m-d','user_timezone'=>$userTimezone];
        if(!empty($userDate) && $type!='custom_compare'){
            $callParm['user_date'] =$userDate;
        }else if($type=='custom_compare'){
            $useDateFrom = $extraParm['user_date_from']??'';
            $useDateTo = $extraParm['user_date_to']??'';
            $callParm['user_date_from'] =$useDateFrom;
            $callParm['user_date_to'] =$useDateTo;
            $callParm['compare_date_filter'] =true;
        }
        $fin_year = get_common_graph_filter_year($callParm);
        $this->db->select(["c.category_name", "sum(ili.amount) as total_amount","ili.invoice_date","ili.invoice_id","c.id as category_id"]);
        $this->db->from(TBL_PREFIX."invoice as i");
        $this->db->join(TBL_PREFIX."invoice_line_item as ili","i.id=ili.invoice_id and i.archive=ili.archive","inner");
        $this->db->join(TBL_PREFIX."category_mapping as cm","cm.id=ili.mapping_id","inner");
        $this->db->join(TBL_PREFIX."category as c","c.id=cm.category_id","inner");
        $this->db->where('i.user_id',$userId);
        $this->db->where('i.archive',0);
        if(INVOICE_PROCESS_ONLY){
            $this->db->where('ili.read_status',2);
        }
        $this->db->group_by('c.id');
        if($type == "week"){
            $this->db->where("ili.invoice_date BETWEEN '" . $fin_year['financial_week_start'] . "' AND '" . $fin_year['financial_week_end'] . "'", NULL, false);
        }
        
        if($type == "month"){
            $this->db->where("ili.invoice_date BETWEEN '" . $fin_year['financial_month_start'] . "' AND '" . $fin_year['financial_month_end'] . "'", NULL, false);
        }

        if($type == "year"){
            $this->db->where("ili.invoice_date BETWEEN '" . $fin_year['financial_start'] . "' AND '" . $fin_year['financial_end'] . "'", NULL, false);
        }

        if($type == "quarter"){
            $this->db->where("ili.invoice_date BETWEEN '" . $fin_year['financial_quarter_start'] . "' AND '" . $fin_year['financial_quarter_end'] . "'", NULL, false);
        }
        if($type == "custom_compare"){
            $fromStart = $fin_year['previous_financial_month_start'];
            $fromEnd = $fin_year['previous_financial_month_end'];
            $toStart = $fin_year['financial_month_start'];
            $toEnd = $fin_year['financial_month_end'];
            if($select_type=='quarter'){
                $fromStart = $fin_year['previous_financial_quarter_start'];
                $fromEnd = $fin_year['previous_financial_quarter_end'];
                $toStart = $fin_year['financial_quarter_start'];
                $toEnd = $fin_year['financial_quarter_end'];
            }else if($select_type=='year'){
                $fromStart = $fin_year['previous_financial_start'];
                $fromEnd = $fin_year['previous_financial_end'];
                $toStart = $fin_year['financial_start'];
                $toEnd = $fin_year['financial_end'];
            }
            $this->db->select([
                "SUM(CASE WHEN (ili.invoice_date BETWEEN '" . $fromStart . "' AND '" . $fromEnd . "') THEN ili.amount ELSE 0 END) as previous_amount",
                "SUM(CASE WHEN (ili.invoice_date BETWEEN '" . $toStart . "' AND '" . $toEnd . "') THEN ili.amount ELSE 0 END) as current_amount",
                "CONCAT('from-',c.category_name) as from_cate_name", 
                "CONCAT('to-',c.category_name) as to_cate_name" 
            ]);
            $this->db->group_start();
            $this->db->or_where("(ili.invoice_date BETWEEN '" . $fromStart . "' AND '" . $fromEnd . "')",NULL,false);
            $this->db->or_where("(ili.invoice_date BETWEEN '" . $toStart . "' AND '" . $toEnd . "')",NULL,false);
            $this->db->group_end();
        }
        
        $query = $this->db->get();
        $res=  $query->result_array();
    
       
        $categoriesColorData = get_category_color_for_graph(array_column($res,'category_id'));
        $catgoriesProcessedResult= [];
        if($type=='custom_compare'){
       
            $catgoriesProcessedResult['labels'] = array_column($res,'category_name');
            $catgoriesProcessedResult['datasets'][0]['data'] =array_column($res,'previous_amount');
            $catgoriesProcessedResult['datasets'][0]['label'] =array_column($res,'from_cate_name');
           
            $catgoriesProcessedResult['datasets'][0]['backgroundColor']=$categoriesColorData;
            $catgoriesProcessedResult['datasets'][1]['data'] =array_column($res,'current_amount');
            $catgoriesProcessedResult['datasets'][0]['label'] =array_column($res,'to_cate_name');
            $catgoriesProcessedResult['datasets'][1]['backgroundColor']=$categoriesColorData;
           

        }else{

            $catgoriesProcessedResult['labels'] = array_column($res,'category_name');
            $catgoriesProcessedResult['datasets'][0]['data'] =array_column($res,'total_amount');
            $catgoriesProcessedResult['datasets'][0]['backgroundColor']=$categoriesColorData;
            
        }
        return ['invoice_pieData'=> $catgoriesProcessedResult,'invoice_expenses_total'=>array_sum(array_column($res,'total_amount'))];
    }
    
    public function get_statment_credit_debit_graph_data(int $userId=0,$extraParm=[]){
        $userTimezone = $extraParm['user_timezone']??'UTC';   
        $type = $extraParm['type']??'week';   
        $select_type = $extraParm['select_type']??'month';   
        $userDate = $extraParm['user_date']??'';   
        $debitOnly = $extraParm['debit_only']??false;   
        $debitCategoryOnly = $extraParm['debit_category_only']??false;   
        $bigGraphOnly = $extraParm['big_graph_only']??false;   
        $callParm = ['return_format'=>'Y-m-d','user_timezone'=>$userTimezone];
        $querterCase=false;
        $z=1;
        $zMax=12;
        if(!empty($userDate)&& $type!='custom_compare'){
            $callParm['user_date'] =$userDate;
        }else if($type=='custom_compare'){
            $useDateFrom = $extraParm['user_date_from']??'';
            $useDateTo = $extraParm['user_date_to']??'';
            $callParm['user_date_from'] =$useDateFrom;
            $callParm['user_date_to'] =$useDateTo;
            $callParm['compare_date_filter'] =true;
        }
        $fin_year = get_common_graph_filter_year($callParm);
        $this->db->select(["sum(sli.amount) as amount,sli.transaction_type"]);
        $this->db->from(TBL_PREFIX."statement as s");
        $this->db->join(TBL_PREFIX."statement_line_item as sli","s.id=sli.statement_id and s.archive=sli.archive","inner");
        $this->db->where('s.user_id',$userId);
        $this->db->where('s.archive',0);
        $this->db->where('sli.read_status',2);
        if($debitOnly || $debitCategoryOnly){
            $this->db->where('sli.transaction_type',2);
        }
        if($debitCategoryOnly){
            $this->db->group_by("c.id");
        }
        $dateType = '%d %b %Y';
        if($type == "week"){
            $z=(int)DateFormate($fin_year['financial_week_start'],'d');
            $zMax=(int)DateFormate($fin_year['financial_week_end'],'d');
            if($zMax<$z){
                $zMax= $z+6;
            }
            $dateTypeFormatLable = 'D (d M)';
            $dateTypeFormatCheck = 'd M Y';
            $dateRange =getDatesFromRange($fin_year['financial_week_start'],$fin_year['financial_week_end'],'Y-m-d',$z);
            $this->db->where("sli.transaction_date BETWEEN '" . $fin_year['financial_week_start'] . "' AND '" . $fin_year['financial_week_end'] . "'", NULL, false);
        }
        
        if($type == "month"){
            
            $z=(int)DateFormate($fin_year['financial_month_start'],'d');
            $zMax=(int)DateFormate($fin_year['financial_month_end'],'d');
            $dateTypeFormatLable = 'd';
            $dateTypeFormatCheck = 'd M Y';
            $dateRange =getDatesFromRange($fin_year['financial_month_start'],$fin_year['financial_month_end'],'Y-m-d',$z);
            $this->db->where("sli.transaction_date BETWEEN '" . $fin_year['financial_month_start'] . "' AND '" . $fin_year['financial_month_end'] . "'", NULL, false);
        }

        if($type == "year"){
            $dateType = '%b %Y';
            $dateTypeFormatLable = 'M Y';
            $dateTypeFormatCheck = 'M Y';
            $dateRange =getMonthFromRange($fin_year['financial_start'],$fin_year['financial_end'],'Y-m-d',$z);
            
            $this->db->where("sli.transaction_date BETWEEN '" . $fin_year['financial_start'] . "' AND '" . $fin_year['financial_end'] . "'", NULL, false);
        }

        if($type == "quarter"){
            $dateType = '%b %Y';
            $dateTypeFormatLable = 'M Y';
            $dateTypeFormatCheck = 'M Y';
            $z=(int)DateFormate($fin_year['financial_quarter_start'],'m');
            $zMax=(int)DateFormate($fin_year['financial_quarter_end'],'m');
            $dateRange =getMonthFromRange($fin_year['financial_quarter_start'],$fin_year['financial_quarter_end'],'Y-m-d',$z);
            $this->db->where("sli.transaction_date BETWEEN '" . $fin_year['financial_quarter_start'] . "' AND '" . $fin_year['financial_quarter_end'] . "'", NULL, false);
        }

        if($type == "custom_compare"){
            
            $fromStart = $fin_year['previous_financial_month_start'];
            $fromEnd = $fin_year['previous_financial_month_end'];
            $toStart = $fin_year['financial_month_start'];
            $toEnd = $fin_year['financial_month_end'];
            $dateType = '%d %b %Y';
            $dateTypeFormatLable = 'd';
            $dateTypeFormatCheck = 'd M Y';
            $z=(int)DateFormate($fin_year['financial_month_start'],'d');
            $zMax=(int)DateFormate($fin_year['financial_month_end'],'d');
            $dateRange =getDatesFromRange($fin_year['financial_month_start'],$fin_year['financial_month_end'],'Y-m-d',$z);
            $dateRangeCompare =getDatesFromRange($fin_year['previous_financial_month_start'],$fin_year['previous_financial_month_end'],'Y-m-d',$z);
            if($select_type=='quarter'){
                $dateType = '%b %Y';
                $dateTypeFormatLable = 'M';
                $dateTypeFormatCheck = 'M Y';
                $ch = DateFormate($fin_year['financial_quarter_start'],'m');
                $chp = DateFormate($fin_year['previous_financial_quarter_start'],'m');
                if($ch==$chp){
                    $z=(int)DateFormate($fin_year['financial_quarter_start'],'m');
                    $zMax=(int)DateFormate($fin_year['financial_quarter_end'],'m');
                    $dateRange =getMonthFromRange($fin_year['financial_quarter_start'],$fin_year['financial_quarter_end'],'Y-m-d',$z);
                    $dateRangeCompare =getMonthFromRange($fin_year['previous_financial_quarter_start'],$fin_year['previous_financial_quarter_end'],'Y-m-d',$z);
                }else{
                    $querterCase=true;
                    $dateTypeFormatLable = 'M Y';
                   
                    $z=0;
                    $zMax=5;
                    $zp = $ch >$chp ? 0:3;
                    $zf = $ch <$chp ? 0:3;
                    $dateRange =getMonthFromRange($fin_year['financial_quarter_start'],$fin_year['financial_quarter_end'],'Y-m-d',$zf);
                    $dateRangeCompare =getMonthFromRange($fin_year['previous_financial_quarter_start'],$fin_year['previous_financial_quarter_end'],'Y-m-d',$zp);
                    $dateRangeNew = $zp>$zf ? array_merge($dateRange,$dateRangeCompare) :array_merge($dateRangeCompare,$dateRange);
                
                }

                $fromStart = $fin_year['previous_financial_quarter_start'];
                $fromEnd = $fin_year['previous_financial_quarter_end'];
                $toStart = $fin_year['financial_quarter_start'];
                $toEnd = $fin_year['financial_quarter_end'];
            }else if($select_type=='year'){
                $dateType = '%b %Y';
                $dateTypeFormatLable = 'M';
                $dateTypeFormatCheck = 'M Y';
                $z=1;
                $zMax=12;
                $dateRange =getMonthFromRange($fin_year['financial_start'],$fin_year['financial_end'],'Y-m-d',$z);
                $dateRangeCompare =getMonthFromRange($fin_year['previous_financial_start'],$fin_year['previous_financial_end'],'Y-m-d',$z);
                $fromStart = $fin_year['previous_financial_start'];
                $fromEnd = $fin_year['previous_financial_end'];
                $toStart = $fin_year['financial_start'];
                $toEnd = $fin_year['financial_end'];
            }
    
            $this->db->group_start();
            $this->db->or_where("(sli.transaction_date BETWEEN '" . $fromStart . "' AND '" . $fromEnd . "')",NULL,false);
            $this->db->or_where("(sli.transaction_date BETWEEN '" . $toStart . "' AND '" . $toEnd . "')",NULL,false);
            $this->db->group_end();
        }
        $this->db->select(["CONCAT(DATE_FORMAT(sli.transaction_date,'".$dateType."'),'_',sli.transaction_type) as concat_transaction_type","DATE_FORMAT(sli.transaction_date,'".$dateType."') as format_transaction_date"]);
        if(!$debitCategoryOnly){
        $extraGroupBy = $bigGraphOnly ? ',c.id':'';
            $this->db->group_by("DATE_FORMAT(sli.transaction_date,'".$dateType."'),sli.transaction_type".$extraGroupBy);
        }
        if($debitCategoryOnly || $bigGraphOnly){
            $this->db->select(["c.category_name","c.id as category_id","CONCAT(DATE_FORMAT(sli.transaction_date,'".$dateType."'),'_',sli.transaction_type,'_',c.id) as concat_transaction_type_category"]);
            $this->db->join(TBL_PREFIX."vendor_mapping as vm","vm.id=sli.mapped_id","inner");
            $this->db->join(TBL_PREFIX."category as c","c.id=vm.category_id","inner");

            if($type == "custom_compare"){
                $fromStart = $fin_year['previous_financial_month_start'];
                $fromEnd = $fin_year['previous_financial_month_end'];
                $toStart = $fin_year['financial_month_start'];
                $toEnd = $fin_year['financial_month_end'];
                if($select_type=='quarter'){
                    $fromStart = $fin_year['previous_financial_quarter_start'];
                    $fromEnd = $fin_year['previous_financial_quarter_end'];
                    $toStart = $fin_year['financial_quarter_start'];
                    $toEnd = $fin_year['financial_quarter_end'];
                }else if($select_type=='year'){
                    $fromStart = $fin_year['previous_financial_start'];
                    $fromEnd = $fin_year['previous_financial_end'];
                    $toStart = $fin_year['financial_start'];
                    $toEnd = $fin_year['financial_end'];
                }
                $this->db->select([
                    "SUM(CASE WHEN (sli.transaction_date BETWEEN '" . $fromStart . "' AND '" . $fromEnd . "') THEN sli.amount ELSE 0 END) as previous_amount",
                    "SUM(CASE WHEN (sli.transaction_date BETWEEN '" . $toStart . "' AND '" . $toEnd . "') THEN sli.amount ELSE 0 END) as current_amount",
                    "CONCAT('from-',c.category_name) as from_cate_name", 
                    "CONCAT('to-',c.category_name) as to_cate_name" 
                ]);
                $this->db->group_start();
                $this->db->or_where("(sli.transaction_date BETWEEN '" . $fromStart . "' AND '" . $fromEnd . "')",NULL,false);
                $this->db->or_where("(sli.transaction_date BETWEEN '" . $toStart . "' AND '" . $toEnd . "')",NULL,false);
                $this->db->group_end();
            }
            
        }
        $query = $this->db->get();
        $res=  $query->num_rows()>0?$query->result_array():[];
        $resAmount = $res;
        if($debitCategoryOnly){
            $categoriesColorData = get_category_color_for_graph(array_column($res,'category_id'));
            $data= [];
            
            if($type=='custom_compare'){
       
                $data['labels'] = array_column($res,'category_name');
                $data['datasets'][0]['data'] =array_column($res,'previous_amount');
                $data['datasets'][0]['label'] =array_column($res,'from_cate_name');
               
                $data['datasets'][0]['backgroundColor']=$categoriesColorData;
                $data['datasets'][1]['data'] =array_column($res,'current_amount');
                $data['datasets'][0]['label'] =array_column($res,'to_cate_name');
                $data['datasets'][1]['backgroundColor']=$categoriesColorData;
               
            }else{
                $data['labels'] = array_column($res,'category_name');
                $data['datasets'][0]['data'] =array_column($res,'amount');
                $data['datasets'][0]['backgroundColor']=$categoriesColorData;
            }
            $stringData = 'category_';
        }else{
            $resCat=[];
            if($bigGraphOnly){
                $resNew = !empty($res) ? merge_multidimensional_array_values_by_key_in_array_format($res,'concat_transaction_type'):[];
                $resCat = !empty($res) ? array_column($res,'category_name','category_id'):[];
                $categoriesColorData = get_category_color_for_graph(array_keys($resCat));
                $resCatData = !empty($res) ? pos_index_change_array_data($res,'concat_transaction_type_category'):[];
                $res = $resNew;
            }else{
                $res = !empty($res) ? pos_index_change_array_data($res,'concat_transaction_type'):[];
            }

            if($debitOnly){
                $data= $type == "custom_compare" ? [['Month','Debit From','Debit To']] : [['Month','Debit']];
            }else{
                $catMixed = $this->cate_name_mixed($resCat);
                
                $data= $type == "custom_compare" ? [array_merge(['Month','Credit To','Debit To','Credit From','Debit Form'], $catMixed)]:[array_merge(['Month','Credit','Debit'],$resCat)];
               
            }
            
            for($i = $z ; $i <= $zMax; $i++)
            {
                $temp=[];
                $dataView = $dateRange[$i]??'';
                $monthStringView =date($dateTypeFormatLable,strtotime($dataView));
                $monthString =date($dateTypeFormatCheck,strtotime($dataView));
                if($querterCase){
                    $dataViewNew = $dateRangeNew[$i]??'';
                    $monthStringView =date($dateTypeFormatLable,strtotime($dataViewNew));
                }
                if($type=='custom_compare'){
                    $dataViewFrom = $dateRangeCompare[$i]??'';
                    $monthStringFrom =date($dateTypeFormatCheck,strtotime($dataViewFrom));
                }
                $temp[$i][0]=$monthStringView;
                if($debitOnly){
                    if($type=='custom_compare'){
                        $temp[$i][1]=isset($res[$monthStringFrom.'_2']['amount'])?(float)$res[$monthStringFrom.'_2']['amount']:0;
                        $temp[$i][2]=isset($res[$monthString.'_2']['amount'])?(float)$res[$monthString.'_2']['amount']:0;
                    }else{

                        $temp[$i][1]=isset($res[$monthString.'_2']['amount'])?(float)$res[$monthString.'_2']['amount']:0;
                    }
                }elseif($bigGraphOnly){
                    $temp[$i][1]=isset($res[$monthString.'_1'])?(float)array_sum(array_column($res[$monthString.'_1'],'amount')):0;
                    $temp[$i][2]=isset($res[$monthString.'_2'])?(float)array_sum(array_column($res[$monthString.'_2'],'amount')):0;
                    if($type=='custom_compare'){
                        $temp[$i][3]=isset($res[$monthStringFrom.'_1'])?(float)array_sum(array_column($res[$monthStringFrom.'_1'],'amount')):0;
                        $temp[$i][4]=isset($res[$monthStringFrom.'_2'])?(float)array_sum(array_column($res[$monthStringFrom.'_2'],'amount')):0;
                    }
                    if(!empty($resCat)){
                        $k=$type=='custom_compare' ? 5:3;
                        foreach($resCat as $key=>$val){
                            if($type=='custom_compare'){
                                $temp[$i][$k]=isset($resCatData[$monthStringFrom.'_2_'.$key]['amount'])?(float)$resCatData[$monthStringFrom.'_2_'.$key]['amount']:0;
                                $k++;
                            }
                            $temp[$i][$k]=isset($resCatData[$monthString.'_2_'.$key]['amount'])?(float)$resCatData[$monthString.'_2_'.$key]['amount']:0;
                            $k++;
                        }
                    }
                    
                }else{
                    $temp[$i][1]=isset($res[$monthString.'_1']['amount'])?(float)$res[$monthString.'_1']['amount']:0;
                    $temp[$i][2]=isset($res[$monthString.'_2']['amount'])?(float)$res[$monthString.'_2']['amount']:0;
                    if($type=='custom_compare'){
                        $temp[$i][3]=isset($res[$monthStringFrom.'_1']['amount'])?(float)$res[$monthStringFrom.'_1']['amount']:0;
                        $temp[$i][4]=isset($res[$monthStringFrom.'_2']['amount'])?(float)$res[$monthStringFrom.'_2']['amount']:0;
                    }
                }
                
                $data[]=$temp[$i];
            }
            $stringData = $debitOnly ? 'debit_':($bigGraphOnly?'big_graph_':'');
        }
        $maxtick =find_max_tick($resAmount,['amount']);
        $dataResponse = [$stringData.'statementData'=> $data,$stringData.'statement_max_tick'=>$maxtick];
        if($debitCategoryOnly){
            $dataResponse[$stringData.'statementData_expenses_total'] =array_sum(array_column($res,'amount'));
        }
        if($bigGraphOnly){
            $dataResponse[$stringData.'color'] =$type=='custom_compare'? array_merge(['#7FFF00','#de455e', '#13bd65','#ff6376'],$categoriesColorData,$categoriesColorData):array_merge(['#5bba6e','#de455e'],$categoriesColorData);
            $dataResponse[$stringData.'series'] = $type=='custom_compare';
        }
        return $dataResponse;
    }

    public function get_invoice_categories_section_data(int $userId=0,$extraParm=[]){
        $userTimezone = $extraParm['user_timezone']??'UTC';   
        $type = $extraParm['type']??'week';   
        $select_type = $extraParm['select_type']??'month';   
        $userDate = $extraParm['user_date']??'';   
        $callParm = ['return_format'=>'Y-m-d','user_timezone'=>$userTimezone];
        if(!empty($userDate)){
            $callParm['user_date'] =$userDate;    
        }else if($type=='custom_compare'){
            $useDateFrom = $extraParm['user_date_from']??'';
            $useDateTo = $extraParm['user_date_to']??'';
            $callParm['user_date_from'] =$useDateFrom;
            $callParm['user_date_to'] =$useDateTo;
            $callParm['compare_date_filter'] =true;
        }

        $fin_year = get_common_graph_filter_year($callParm);
 
        if($type == "year" || ($type=='custom_compare' && $select_type=='year')){
        $this->db->select("sum(CASE WHEN ili.invoice_date BETWEEN '" . $fin_year['financial_start'] . "' AND '" . $fin_year['financial_end'] . "' THEN ili.amount  ELSE '0' END) as current_year_amount", false);
        $this->db->select("sum(CASE WHEN ili.invoice_date BETWEEN '" . $fin_year['previous_financial_start'] . "' AND '" . $fin_year['previous_financial_end'] . "' THEN ili.amount  ELSE '0' END) as previous_year_amount", false);
        }
        if($type == "month" || ($type=='custom_compare' && $select_type=='month')){
            $this->db->select("sum(CASE WHEN ili.invoice_date BETWEEN '" . $fin_year['financial_month_start'] . "' AND '" . $fin_year['financial_month_end'] . "' THEN ili.amount  ELSE '0' END) as current_year_amount", false);
            $this->db->select("sum(CASE WHEN ili.invoice_date BETWEEN '" . $fin_year['previous_financial_month_start'] . "' AND '" . $fin_year['previous_financial_month_end'] . "' THEN ili.amount  ELSE '0' END) as previous_year_amount", false);
        }
        if($type == "week" ){
            $this->db->select("sum(CASE WHEN ili.invoice_date BETWEEN '" . $fin_year['financial_week_start'] . "' AND '" . $fin_year['financial_week_end'] . "' THEN ili.amount  ELSE '0' END) as current_year_amount", false);
            $this->db->select("sum(CASE WHEN ili.invoice_date BETWEEN '" . $fin_year['previous_financial_week_start'] . "' AND '" . $fin_year['previous_financial_week_end'] . "' THEN ili.amount  ELSE '0' END) as previous_year_amount", false);
        }
        if($type == "quarter" || ($type=='custom_compare' && $select_type=='quarter')){
            $this->db->select("sum(CASE WHEN ili.invoice_date BETWEEN '" . $fin_year['financial_quarter_start'] . "' AND '" . $fin_year['financial_quarter_end'] . "' THEN ili.amount  ELSE '0' END) as current_year_amount", false);
            $this->db->select("sum(CASE WHEN ili.invoice_date BETWEEN '" . $fin_year['previous_financial_quarter_start'] . "' AND '" . $fin_year['previous_financial_quarter_end'] . "' THEN ili.amount  ELSE '0' END) as previous_year_amount", false);
        }
        $this->db->select(["c.category_name", "sum(ili.amount) as total_amount","ili.invoice_date","ili.invoice_id","c.id as category_id"]);
        $this->db->from(TBL_PREFIX."invoice as i");
        $this->db->join(TBL_PREFIX."invoice_line_item as ili","i.id=ili.invoice_id and i.archive=ili.archive","inner");
        $this->db->join(TBL_PREFIX."category_mapping as cm","cm.id=ili.mapping_id","inner");
        $this->db->join(TBL_PREFIX."category as c","c.id=cm.category_id","inner");
        $this->db->where('i.user_id',$userId);
        $this->db->where('i.archive',0);
        if(INVOICE_PROCESS_ONLY){
            $this->db->where('ili.read_status',2);
        }
        $this->db->group_by('c.id');
        $query = $this->db->get();
        $res=  $query->result_array();
        $labels =  $type=='custom_compare' ? $this->getLabelsforSection($select_type,$userDate,['fin_year'=>$fin_year]):$this->getLabelsforSection($type,$userDate,['fin_year'=>$fin_year]);
        $result =$this->sectionLoop($res,$labels);
        return ['sectionData'=> $result];
    }

    private function sectionLoop($res,$labels){
        $result =array();
        if(!empty($res)){
            foreach($res as $cat){
               $result[] = stats_percentage_calculation($cat['current_year_amount'], $cat['previous_year_amount'],$cat['category_name'],$labels);
           }
       }
       return $result;
    }

    function getLabelsforSection($type,$userDate,$extraParms=[]){
        $fin_year =$extraParms['fin_year']??[];
        
        if($type=='year'){
           $toYear = DateFormate($fin_year['financial_start'],'Y');
           $fromYear = DateFormate($fin_year['previous_financial_start'],'Y');
            $labels = array('line1' =>  'Total amount of '.$toYear, 
            'line2' => 'Total amount of '.$fromYear);
        }
        if($type=='month'){
            $toYear = DateFormate($fin_year['financial_month_start'],'M Y');
            $fromYear = DateFormate($fin_year['previous_financial_month_start'],'M Y');
            $labels = array('line1' =>  'Total amount of '.$toYear, 
            'line2' => 'Total amount of '.$fromYear);
        }
        if($type=='week'){
            $toYear = DateFormate($fin_year['financial_week_start'],'d M Y');
            $fromYear = DateFormate($fin_year['previous_financial_week_start'],'d M Y');
            $labels = array('line1' =>  'Total amount of '.$toYear, 
            'line2' => 'Total amount of '.$fromYear);
        }
        if($type=='month' && !empty($userDate)){
            $toYear = DateFormate($fin_year['financial_month_start'],'M Y');
            $fromYear = DateFormate($fin_year['previous_financial_month_start'],'M Y');
            $labels = array('line1' =>  'Total amount of '.$toYear, 
            'line2' => 'Total amount of '.$fromYear);
        } 
        if($type=='quarter'){
            $toYear = ceil(DateFormate($fin_year['financial_month_start'],'n')/3) .' '.DateFormate($fin_year['financial_quarter_start'],'Y');
            $fromYear = ceil(DateFormate($fin_year['previous_financial_month_start'],'n')/3).' '.DateFormate($fin_year['previous_financial_quarter_start'],'Y');
            $labels = array('line1' =>  'Total amount of quarter'.$toYear, 
            'line2' => 'Total amount of quarter'. $fromYear);
        }
        return $labels;
    }

    function cate_name_mixed($cat){
        $output = array();
        foreach($cat as $value) {
            $output[] = $value.' To';
            $output[] = $value.' From';
        }
        return $output;
    }


    public function export_excel($dataHearder = [], $dataRes = [], $extraParm = []) {
        $this->load->library("Excel");
        $object = new PHPExcel();
        $i=0;
        $object->setActiveSheetIndex(0);
        $fileName = $extraParm['file_name'] ?? time() . 'export.xls';
        $fileDirPath = $extraParm['file_dir_path'] ?? FCPATH ;
        $user_id = $extraParm['user_id'] ?? 0 ;
        $imageData = $extraParm['file_data'] ?? [] ;
        
        if(!empty($dataRes)){

            foreach($dataRes as $keySheet=>$resData){
                if($i!=0){
                    $object->createSheet($i);
                   $object->setActiveSheetIndex($i);
                }
                $lastColumn = getEcxcelColumnNameGetByIndex(count($dataHearder));
                $object->getActiveSheet()->mergeCells('A1:'.$lastColumn.'10');
                $objDrawing = new PHPExcel_Worksheet_Drawing();
                $objDrawing->setName($keySheet);
                $objDrawing->setDescription($keySheet);
                $objDrawing->setPath(FCPATH.USER_REPORT_TEMP_PATH . $user_id.'/'.$imageData['my-file'.$i]);
                $objDrawing->setCoordinates('A1');                      
                //setOffsetX works properly
                $objDrawing->setOffsetX(5); 
                $objDrawing->setOffsetY(5);                
                //set width, height
                $objDrawing->setWidth(382); 
                $objDrawing->setHeight(180); 
                $objDrawing->setWorksheet($object->getActiveSheet());
                $column = 0;
                foreach ($dataHearder as $field) {
                    $object->getActiveSheet()->setCellValueByColumnAndRow($column, 11, $field);
                    $column++;
                }
        
                $object = $this->excel_loop($object,$resData,$dataHearder,12,0);
        
                
                $object->getActiveSheet()
                        ->getStyle('A2:' . $lastColumn . '2')
                        ->applyFromArray(
                                array(
                                    'fill' => array(
                                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                        'color' => array('rgb' => 'C0C0C0:')
                                    )
                                )
                );
                $object->getActiveSheet()->setTitle("$keySheet");
                $i++;
            }
        }
        $object_writer = PHPExcel_IOFactory::createWriter($object, 'Excel5');
        $csv_fileFCpath = $fileDirPath . $fileName;
        $object_writer->save($csv_fileFCpath);
        if (file_exists($csv_fileFCpath)) {
            return ['status' => true, 'filename' => $fileName];
        }
        return ['status' => false, 'error' => 'Excel file not exist'];
    }

    private function excel_loop($object,$resData,$dataHearder,$start_excel_row=1,$start_column=0){
        if (!empty($resData)) {
            foreach ($resData as $row) {
                $startColumn = $start_column;
                foreach ($dataHearder as $key => $value) {
                    $object->getActiveSheet()->setCellValueByColumnAndRow($startColumn, $start_excel_row, $row[$key] ?? '');
                    $startColumn++;
                }
                $start_excel_row++;
            }
        }
        return $object;
    }

    public function get_invoice_categories_forecast_pie_data(int $userId=0,$extraParm=[]){    
        $userTimezone = $extraParm['user_timezone']??'UTC';   
        $type = $extraParm['type']??'future_year';   
        $userDate = $extraParm['user_date']??'';   
        $callParm = ['return_format'=>'Y-m-d','user_timezone'=>$userTimezone];
        if(!empty($userDate)){
            $callParm['user_date'] =$userDate;
        }
        if($type=='future_year'){
            $callParm['future_year_filter'] =true;
        }

        if($type=='next_quarter'){
            $callParm['next_quarter_filter'] =true;
        }
        if($type=='next_month'){
            $callParm['next_month_filter'] =true;
        }
        $fin_year = get_common_graph_filter_year($callParm);
        $this->db->select(["c.category_name", "sum(i.amount) as total_amount","i.invoice_date","c.id as category_id"]);
        $this->db->from(TBL_PREFIX."forecasting_invoice_line_item as i");
        $this->db->join(TBL_PREFIX."category as c","c.id=i.category_id","inner");
        $this->db->where('i.user_id',$userId);
        $this->db->where('i.archive',0);
        $this->db->group_by('c.id');
        
        
        if($type == "next_month"){
            $this->db->where("i.invoice_date BETWEEN '" . $fin_year['financial_next_month_start'] . "' AND '" . $fin_year['financial_next_month_end'] . "'", NULL, false);
        }

        if($type == "future_year"){
            $this->db->where("i.invoice_date BETWEEN '" . $fin_year['financial_future_start'] . "' AND '" . $fin_year['financial_future_end'] . "'", NULL, false);
        }

        if($type == "next_quarter"){
            $this->db->where("i.invoice_date BETWEEN '" . $fin_year['financial_next_quarter_start'] . "' AND '" . $fin_year['financial_next_quarter_end'] . "'", NULL, false);
        }
        if($type == "selected_month"){
            $this->db->where("i.invoice_date BETWEEN '" . $fin_year['financial_month_start'] . "' AND '" . $fin_year['financial_month_end'] . "'", NULL, false);
        }

        if($type == "selected_quarter"){
            $this->db->where("i.invoice_date BETWEEN '" . $fin_year['financial_quarter_start'] . "' AND '" . $fin_year['financial_quarter_end'] . "'", NULL, false);
        }

        $query = $this->db->get();
        $res=  $query->result_array();
        $categoriesColorData = get_category_color_for_graph(array_column($res,'category_id'));
        $catgoriesProcessedResult= [];
        $catgoriesProcessedResult['labels'] = array_column($res,'category_name');
        $catgoriesProcessedResult['datasets'][0]['data'] =array_column($res,'total_amount');
        $catgoriesProcessedResult['datasets'][0]['backgroundColor']=$categoriesColorData;
            
        return ['invoice_pieData'=> $catgoriesProcessedResult,'invoice_expenses_total'=>array_sum(array_column($res,'total_amount'))];
    }
    public function get_invoice_categories_forecast_pie_data_new(int $userId=0,$extraParm=[]){    
        $userTimezone = $extraParm['user_timezone']??'UTC';   
        $type = $extraParm['type']??'future_year';   
        $userDate = $extraParm['user_date']??'';   
        $callParm = ['return_format'=>'Y-m-d','user_timezone'=>$userTimezone];
        if(!empty($userDate)){
            $callParm['user_date'] =$userDate;
        }
        if($type=='future_year'){
            $callParm['future_year_filter'] =true;
        }

        if($type=='next_quarter'){
            $callParm['next_quarter_filter'] =true;
        }
        if($type=='next_month'){
            $callParm['next_month_filter'] =true;
        }
        $fin_year = get_common_graph_filter_year($callParm);
        $this->db->select(["c.category_name", "sum(i.amount) as total_amount","i.invoice_date","c.id as category_id"]);
        $this->db->from(TBL_PREFIX."forecasting_invoice_line_item as i");
        $this->db->join(TBL_PREFIX."category as c","c.id=i.category_id","inner");
        $this->db->where('i.user_id',$userId);
        $this->db->where('i.archive',0);
        $this->db->group_by('c.id');
        
        
        if($type == "next_month"){
            $this->db->where("i.invoice_date BETWEEN '" . $fin_year['financial_next_month_start'] . "' AND '" . $fin_year['financial_next_month_end'] . "'", NULL, false);
        }

        if($type == "future_year"){
            $this->db->where("i.invoice_date BETWEEN '" . $fin_year['financial_future_start'] . "' AND '" . $fin_year['financial_future_end'] . "'", NULL, false);
        }

        if($type == "next_quarter"){
            $this->db->where("i.invoice_date BETWEEN '" . $fin_year['financial_next_quarter_start'] . "' AND '" . $fin_year['financial_next_quarter_end'] . "'", NULL, false);
        }
        if($type == "selected_month"){
            $this->db->where("i.invoice_date BETWEEN '" . $fin_year['financial_month_start'] . "' AND '" . $fin_year['financial_month_end'] . "'", NULL, false);
        }

        if($type == "selected_quarter"){
            $this->db->where("i.invoice_date BETWEEN '" . $fin_year['financial_quarter_start'] . "' AND '" . $fin_year['financial_quarter_end'] . "'", NULL, false);
        }

        $query = $this->db->get();
        $res=  $query->result_array();
        $resCat = $this->CommonModel->getDataWhere(['id as category_id','category_name'],['archive'=>0,'parent_id'=>0],TBL_PREFIX.'category',['result_type'=>2]);
        $categoriesColorData = get_category_color_for_graph(array_column($resCat,'category_id'));
        $catgoriesProcessedResult= [['Category Name','Category']];
        $catgoriesProcessedResultOld= [['Category Name','amount','percentage','amount','percentage']];
        $res = !empty($res) ? pos_index_change_array_data($res,'category_id'):[];
        $total = array_sum(array_column($res,'total_amount'));
        foreach($resCat as $row){
            $val = isset($res[$row['category_id']]['total_amount']) ? $res[$row['category_id']]['total_amount']:0;
            $percentage = $total>0 ? round((($val/$total)*100),1):0;
            $catgoriesProcessedResult[]=[$row['category_name'],(float)$val]; 
            $catgoriesProcessedResultOld[]=[$row['category_name'],(float)$val,(float)$percentage,(float)$val,(float)$percentage]; 
        }
        $catgoriesProcessedResult[]=['Saving Value',0]; 
            
        return ['invoice_pieData_category'=> $catgoriesProcessedResult,'invoice_cat_color'=>$categoriesColorData,'invoice_cat_total'=>$total,'invoice_pieData_category_old'=>$catgoriesProcessedResultOld];
    }

    public function get_statment_credit_forecast_graph_data(int $userId=0,$extraParm=[]){
        $extData=[
            "next_quarter"=> "financial_next_quarter_",
            "selected_quarter"=>"financial_quarter_",
            "future_year"=>"financial_future_",
            "next_month"=>"financial_next_month_",
            "selected_month"=>"financial_month_"
        ];
        $userTimezone = $extraParm['user_timezone']??'UTC';   
        $type = $extraParm['type']??'future_year';   
        $ext= $extData[$type ]??'financial_future_';
        $userDate = $extraParm['user_date']??'';   
        $callParm = ['return_format'=>'Y-m-d','user_timezone'=>$userTimezone];
        if(!empty($userDate)){
            $callParm['user_date'] =$userDate;
        }
        if($type=='future_year'){
            $callParm['future_year_filter'] =true;
            $dateType = '%b %Y';
            $dateTypeFormatLable = 'M Y';
            $dateTypeFormatCheck = 'M Y';
        }

        if($type=='next_quarter' || $type=='selected_quarter'){
            $callParm['next_quarter_filter'] =true;
            $dateType = '%b %Y';
            $dateTypeFormatLable = 'M Y';
            $dateTypeFormatCheck = 'M Y';
        }
        if($type=='next_month' || $type=='selected_month'){
            $callParm['next_month_filter'] =true;
            $dateType = '%d %b %Y';
            $dateTypeFormatLable = 'd';
            $dateTypeFormatCheck = 'd M Y';
        }
        $fin_year = get_common_graph_filter_year($callParm);    
        $this->db->select(["sum(s.amount) as amount,s.transaction_type"]);
        $this->db->from(TBL_PREFIX."forecasting_statement_line_item as s");
        $this->db->where(['s.user_id'=>$userId,'s.archive'=>0,'s.transaction_type'=>1]);
       
        $z=(int)DateFormate($fin_year[$ext.'start'],'d');
        $zMax=(int)DateFormate($fin_year[$ext.'end'],'d');
        $dateRange =getDatesFromRange($fin_year[$ext.'start'],$fin_year[$ext.'end'],'Y-m-d',$z);
        $this->db->where("s.transaction_date BETWEEN '" . $fin_year[$ext.'start'] . "' AND '" . $fin_year[$ext.'end'] . "'", NULL, false);
        
        if($type == "future_year" || $type == "next_quarter" || $type == "selected_quarter"){
            $z=(int)DateFormate($fin_year[$ext.'start'],'m');
            $zMax=(int)DateFormate($fin_year[$ext.'end'],'m');
            $zMax = $z>$zMax ? $z+11 : $zMax;   
            $dateRange =getMonthFromRange($fin_year[$ext.'start'],$fin_year[$ext.'end'],'Y-m-d',$z);      
        }
        $this->db->select(["DATE_FORMAT(s.transaction_date,'".$dateType."') as format_transaction_date"]);
        $this->db->group_by("DATE_FORMAT(s.transaction_date,'".$dateType."')");
     
        $query = $this->db->get();
        $res=  $query->num_rows()>0?$query->result_array():[];
        $resAmount = $res;
        $res = !empty($res) ? pos_index_change_array_data($res,'format_transaction_date'):[];
        $data= [['Month','Credit']];
        for($i = $z ; $i <= $zMax; $i++)
        {
            $temp=[];
            $dataView = $dateRange[$i]??'';
            $monthStringView =date($dateTypeFormatLable,strtotime($dataView));
            $monthString =date($dateTypeFormatCheck,strtotime($dataView));
            $temp[$i][0]=$monthStringView;
            $temp[$i][1]=isset($res[$monthString]['amount'])?(float)$res[$monthString]['amount']:0;
           
            $data[]=$temp[$i];
        }
        $maxtick =find_max_tick($resAmount,['amount']);
        return ['statementData'=> $data,'statement_max_tick'=>$maxtick];
    }

}