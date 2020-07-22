<?php defined('BASEPATH') || exit('No direct script access allowed');

class DashboardModel extends CI_Model
{
	function __construct()
	{
		parent::__construct(); 
	}
	
	function getDashboardDetails(){
		$where = array('archive'=>0, 'status'=>1);
		$this->db->where($where);
		$num_rows['user'] = $this->db->count_all_results(TBL_PREFIX.'person');
		return $num_rows; 
	}
	
	public function dashboard_data_model($request_data){

        $result =array();
        $userId = $request_data->user_id;
        $userTimeZone = $request_data->time_zone??'UTC';
        //INVOICE STATEMENT 
        $this->db->select("COUNT(i.id) as count");   
        $this->db->where("i.status=1 AND i.archive=0 AND user_id=$userId");
        $this->db->from(TBL_PREFIX."invoice i");
        $this->db->limit(1);
        $query = $this->db->get();
        $invoiceCount= $query->row();

        //READ EMAILS STATEMENT 
        //INVOICE 
        $this->db->select("COUNT(i.id) as count");   
        $this->db->where(["i.status"=>1,"i.archive"=>0 ,"i.user_id"=>$userId,"i.source_type"=>3]);
        $this->db->from(TBL_PREFIX."invoice i");
        $query = $this->db->get();
        $invoiceEmailFetchCount= $query->num_rows()>0 ? $query->row()->count:0;

        //STATEMENT 
        $this->db->select("COUNT(s.id) as count");   
        $this->db->where(["s.status"=>1,"s.archive"=>0 ,"s.user_id"=>$userId,"s.source_type"=>3]);
        $this->db->from(TBL_PREFIX."statement s");
        $query = $this->db->get();
        $statementEmailFetchCount= $query->num_rows()>0 ? $query->row()->count:0;
        
        //UNREADABLE FILE 
        $this->db->select("COUNT(up.id) as count,up.source_type");   
        $this->db->where("up.status=1 AND up.archive=0 AND up.user_id=$userId");
        $this->db->from(TBL_PREFIX."unreadable_pdf up");
        $this->db->group_by('up.source_type');
        $query = $this->db->get();
        $unreadableFetchData = $query->num_rows()>0 ? pos_index_change_array_data($query->result_array(),'source_type'):[];
        $unreadableFetchMailCount = ($unreadableFetchCount[1]['count']??0)+($unreadableFetchCount[2]['count']??0);
        $unreadableFetchCount = array_sum(array_column($unreadableFetchData,'count'));
        $readEmailsCount=$invoiceEmailFetchCount+$statementEmailFetchCount+$unreadableFetchMailCount;

        //BANK STATEMENT 
        $this->db->select("COUNT(s.id) as count");   
        $this->db->where("s.status=1 AND s.archive=0 AND user_id=$userId");
        $this->db->from(TBL_PREFIX."statement s");
        $this->db->limit(1); 
        $query = $this->db->get();
        $statementCount= $query->row();

        //VENDOR COUNT 
        $this->db->select("COUNT(v.id) as count");   
        $this->db->where("v.archive=0 AND user_id=$userId");
        $this->db->from(TBL_PREFIX."vendor_user_mapping v");
        $this->db->limit(1);
        $query = $this->db->get();
        $vendorsCount= $query->row();

        $result  = [
			'invoice' => $invoiceCount->count,
			'read_emails' => $readEmailsCount,
			'unreadable_file' => $unreadableFetchCount,
            'bank_statement' => $statementCount->count,
            'vendor' => $vendorsCount->count,
            'categories'=> $this->get_all_categories()
        ];
        $result['statement_year'] = $this->get_statement_year($userId);
        $result['invoice_year'] = $this->get_invoice_year($userId);
        $res = $this->get_invoice_categories_pie_data($userId,['user_timezone'=>$userTimeZone,'type'=>'week']);
        $resStatement = $this->get_statment_credit_debit_graph_data($userId,['user_timezone'=>$userTimeZone]);
        $resInvoice = $this->get_invoice_expenses_graph_data($userId,['user_timezone'=>$userTimeZone]);
        $resCompareExpenses = $this->get_compare_invoice_statement_expenses_graph_data($userId,'invoice',['user_timezone'=>$userTimeZone]);
        return  array_merge($result,$res,$resStatement,$resInvoice,$resCompareExpenses);
    	 
    }

    public function get_all_categories(){
        $this->db->select("c.id,c.category_name");   
        $this->db->where("c.status=1 AND c.archive=0");
        $this->db->from(TBL_PREFIX."category c");
        $query = $this->db->get();
        return  $query->result_array();
    }
    
    public function get_invoice_categories_pie_data(int $userId=0,$extraParm=[]){    
        $userTimezone = $extraParm['user_timezone']??'UTC';   
        $type = $extraParm['type']??'week';   
        $fin_year = get_common_graph_filter_year(['return_format'=>'Y-m-d','user_timezone'=>$userTimezone]);

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
        
        $query = $this->db->get();
        $res=  $query->result_array();
       
        $categoriesColorData = get_category_color_for_graph(array_column($res,'category_id'));
        $catgoriesProcessedResult= [];
        $catgoriesProcessedResult['labels'] = array_column($res,'category_name');
        $catgoriesProcessedResult['datasets'][0]['data'] =array_column($res,'total_amount');
        $catgoriesProcessedResult['datasets'][0]['backgroundColor']=$categoriesColorData;
        return ['pieData'=> $catgoriesProcessedResult,'invoice_expenses_total'=>array_sum(array_column($res,'total_amount'))];
    }
    public function get_statment_credit_debit_graph_data(int $userId=0,$extraParm=[]){
        $userTimezone = $extraParm['user_timezone']??'UTC';   
        $year = $extraParm['year']??change_one_timezone_to_another_timezone(DATE_TIME,'UTC',$userTimezone,'Y');  
        $this->db->select(["CONCAT(DATE_FORMAT(sli.transaction_date,'%b %Y'),'_',sli.transaction_type) as month_transaction_type","DATE_FORMAT(sli.transaction_date,'%b %Y') as month", "sum(sli.amount) as amount,sli.transaction_type"]);
        $this->db->from(TBL_PREFIX."statement as s");
        $this->db->join(TBL_PREFIX."statement_line_item as sli","s.id=sli.statement_id and s.archive=sli.archive","inner");
        $this->db->where('s.user_id',$userId);
        $this->db->where('s.archive',0);
        $this->db->where('sli.read_status',2);
        $this->db->where("year(sli.transaction_date)='".$year."'");
        $this->db->group_by("DATE_FORMAT(sli.transaction_date,'%b %Y'),sli.transaction_type");
        $query = $this->db->get();
        $res=  $query->num_rows()>0?$query->result_array():[];
        $res = !empty($res) ? pos_index_change_array_data($res,'month_transaction_type'):[];
        $data=[['Month','Credit','Debit']];
        for($i = 1 ; $i <= 12; $i++)
        {
            $temp=[];
            $monthString =date("M Y",strtotime($year."-".$i."-01"));
            
            $temp[$monthString][0]=$monthString;
            $temp[$monthString][1]=isset($res[$monthString.'_1']['amount'])?(float)$res[$monthString.'_1']['amount']:0;
            $temp[$monthString][2]=isset($res[$monthString.'_2']['amount'])?(float)$res[$monthString.'_2']['amount']:0;
            $data[]=$temp[$monthString];
        }
        $maxtick = find_max_tick($res,['amount']);

        return ['statementData'=> $data,'statement_max_tick'=>$maxtick];
    }

    public function get_statement_year(int $userId){
        $this->db->select(["min(LEFT(sli.transaction_date,4)) as min_year"]);
        $this->db->from(TBL_PREFIX."statement as s");
        $this->db->join(TBL_PREFIX."statement_line_item as sli","s.id=sli.statement_id and s.archive=sli.archive","inner");
        $this->db->where('s.user_id',$userId);
        $this->db->where('s.archive',0);
        $query = $this->db->get();
        $res=  $query->num_rows()>0?$query->row():[];
        $year = $res->min_year??date('Y');
        return range($year,date('Y'),1);
    }
    public function get_invoice_year(int $userId){
        $this->db->select(["min(LEFT(ili.invoice_date,4)) as min_year"]);
        $this->db->from(TBL_PREFIX."invoice as i");
        $this->db->join(TBL_PREFIX."invoice_line_item as ili","i.id=ili.invoice_id and i.archive=ili.archive","inner");
        $this->db->where('i.user_id',$userId);
        $this->db->where('i.archive',0);
        if(INVOICE_PROCESS_ONLY){
            $this->db->where('ili.read_status',2);
        }
        $query = $this->db->get();
        $res=  $query->num_rows()>0?$query->row():[];
        $year = $res->min_year??date('Y');
        return range($year,date('Y'),1);
    }

    public function get_invoice_expenses_query($userId,$years=[]){
        $this->db->select(["CONCAT(DATE_FORMAT(ili.invoice_date,'%b %Y')) as month_transaction_type, DATE_FORMAT(ili.invoice_date,'%b %Y') as month, sum(ili.amount) as amount"]);
        $this->db->from(TBL_PREFIX."invoice as i");
        $this->db->join(TBL_PREFIX."invoice_line_item as ili","i.id=ili.invoice_id and i.archive=ili.archive","inner");
        $this->db->where('i.user_id',$userId);
        $this->db->where('i.archive',0);
        if(INVOICE_PROCESS_ONLY){
            $this->db->where('ili.read_status',2);
        }
        $this->db->where_in("year(ili.invoice_date)",$years);
        $this->db->group_by("DATE_FORMAT(ili.invoice_date, '%b %Y')");
        $query = $this->db->get();
        return $query->num_rows()>0?$query->result_array():[];
        
    }
    public function get_invoice_expenses_graph_data(int $userId=0,$extraParm=[]){
        $userTimezone = $extraParm['user_timezone']??'UTC';   
        $year = $extraParm['year']??change_one_timezone_to_another_timezone(DATE_TIME,'UTC',$userTimezone,'Y');
        $res = $this->get_invoice_expenses_query($userId,[$year]); 
        $res = !empty($res) ? pos_index_change_array_data($res,'month_transaction_type'):[]; 
        $data=[];
        for($i = 1 ; $i <= 12; $i++)
        {  
            $monthString =date("M Y",strtotime($year."-".$i."-01"));
            $data['labels'][]=$monthString;
            $data['datasets'][0]['label']=[$year];
            $data['datasets'][0]['data'][]=isset($res[$monthString]['amount'])?(float)$res[$monthString]['amount']:0;
            $data['datasets'][0]['backgroundColor'][]=['#0a9191'];
            $data['datasets'][0]['hoverBackgroundColor'][]=['#0a9191'];
        }
        $maxtick = find_max_tick($res,['amount']);
        return ['invoice_expenses_data'=> $data,'invoice_expenses_max_tick'=>$maxtick];
    }

    public function get_statement_expenses_query($userId,$years=[]){
        $this->db->select(["CONCAT(DATE_FORMAT(sli.transaction_date,'%b %Y')) as month_transaction_type, DATE_FORMAT(sli.transaction_date,'%b %Y') as month, sum(sli.amount) as amount"]);
        $this->db->from(TBL_PREFIX."statement as s");
        $this->db->join(TBL_PREFIX."statement_line_item as sli","s.id=sli.statement_id and s.archive=sli.archive","inner");
        $this->db->where('s.user_id',$userId);
        $this->db->where('s.archive',0);
        $this->db->where('sli.read_status',2);
        $this->db->where('sli.transaction_type',2);
        $this->db->where_in("year(sli.transaction_date)",$years);
        $this->db->group_by("DATE_FORMAT(sli.transaction_date, '%b %Y')");
        $query = $this->db->get();
        return $query->num_rows()>0?$query->result_array():[];
        
    }

    public function get_compare_invoice_statement_expenses_graph_data(int $userId=0,$type='invoice',$extraParm=[]){
        $userTimezone = $extraParm['user_timezone']??'UTC';   
        $yearfrom = $extraParm['year_from']??change_one_timezone_to_another_timezone(DATE_TIME,'UTC',$userTimezone,'Y');
        $yearto = $extraParm['year_to']??change_one_timezone_to_another_timezone(DATE_TIME,'UTC',$userTimezone,'Y');
        $res = $type=='statement' ?$this->get_statement_expenses_query($userId,[$yearfrom,$yearto]):$this->get_invoice_expenses_query($userId,[$yearfrom,$yearto]); 
        $res = !empty($res) ? pos_index_change_array_data($res,'month_transaction_type'):[]; 
        $data=[];
        for($i = 1 ; $i <= 12; $i++)
        {  
            $monthStringFrom =date("M Y",strtotime($yearfrom."-".$i."-01"));
            $monthStringTo =date("M Y",strtotime($yearto."-".$i."-01"));
            $monthString =date("M",strtotime($yearfrom."-".$i."-01"));
            $data['labels'][]=$monthString;
            $data['datasets'][0]['data'][]=isset($res[$monthStringFrom]['amount'])?(float)$res[$monthStringFrom]['amount']:0;
            $data['datasets'][0]['backgroundColor'][]=['#c5d0ff'];
            $data['datasets'][0]['hoverBackgroundColor'][]=['#c5d0ff'];
            $data['datasets'][0]['label']=['From: '.$yearfrom];
            $data['datasets'][1]['data'][]=isset($res[$monthStringTo]['amount'])?(float)$res[$monthStringTo]['amount']:0;
            $data['datasets'][1]['label']=['To: '.$yearto];
            $data['datasets'][1]['backgroundColor'][]=['#7a85ed'];
            $data['datasets'][1]['hoverBackgroundColor'][]=['#7a85ed'];
        }
        $maxtick = find_max_tick($res,['amount']);
        return ['compare_expenses_data'=> $data,'compare_expenses_max_tick'=>$maxtick];
    }
 
}