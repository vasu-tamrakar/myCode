<?php

defined('BASEPATH') || exit('No direct script access allowed');

class ExportModel extends CI_Model {

    function __construct()
    {
      parent::__construct(); 
      $this->load->model('Common_model');
    }
    
    private function get_original_map() {
        $this->db->select('CONCAT(c.category_name,"-",c.id)');
        $this->db->from(TBL_PREFIX.'vendor_mapping vm');
        $this->db->where("vm.id=si.original_map_id AND vm.archive=si.archive AND vm.archive=0", NULL, false);
        $this->db->join(TBL_PREFIX.'category as c','c.id = vm.category_id AND c.archive=0', 'inner');
        return $this->db->get_compiled_select();
    }

    private function get_vendor_details() {
        $this->db->select('CONCAT(v.name,"-",v.id)');
        $this->db->from(TBL_PREFIX.'vendor_mapping vm');
        $this->db->where("vm.id=si.mapped_id AND vm.archive=si.archive AND vm.archive=0", NULL, false);
        $this->db->join(TBL_PREFIX.'vendor as v', 'v.id =vm.vendor_id AND v.archive=0', 'inner');
        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }

    
    private function get_category_details() {
        $this->db->select('CONCAT(c.category_name,"-",c.id)');
        $this->db->from(TBL_PREFIX.'vendor_mapping vm');
        $this->db->where("vm.id=si.mapped_id AND vm.archive=si.archive AND vm.archive=0", NULL, false);
        $this->db->join(TBL_PREFIX.'category as c','c.id = vm.category_id AND c.archive=0', 'inner');
        return $this->db->get_compiled_select();
    }
 

    private function get_bankname() {
        $this->db->select('b.bank_name');
        $this->db->from(TBL_PREFIX.'bank b');
        $this->db->where("b.id=s.statement_for AND b.status=1 AND b.archive=0", NULL, false);
        return $this->db->get_compiled_select();
    }

    public function  getStatementLineItemById($lineitemdId){
        if(!empty($lineitemdId)){
          $sub_query_to_get_vendor = $this->get_vendor_details();
          $sub_query_to_get_category = $this->get_category_details();
          $sub_query_to_get_bankname = $this->get_bankname();
          $sub_query_to_get_original_map = $this->get_original_map();
          $sort_columns = array("si.id as statement_ine_item_id", 
           "s.id as statement_id", 
           "si.description",
           "si.transaction_type",
           "si.transaction_date as date",
           "si.amount",
           "si.main_balance",
           "si.status",
           "si.original_map_id", 
           "si.mapped_id",
           "si.flag",
           "DATE_FORMAT(s.issue_date, '%d/%m/%Y') as issue_date",
           "DATE_FORMAT(si.transaction_date, '%d/%m/%Y') as transaction_date",
           "(" . $sub_query_to_get_vendor . ") as vendor_details",
           "(" . $sub_query_to_get_category . ") as category_details",
           "(" . $sub_query_to_get_bankname . ") as bankname",
           "(" . $sub_query_to_get_original_map . ") as orignal_map_details",
          );
          $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $sort_columns)), false);
          $array = array('s.archive' => 0, 'si.archive' => 0);
          $this->db->where($array);
          $this->db->from(TBL_PREFIX.'statement as s');
          $this->db->join(TBL_PREFIX.'statement_line_item as si', 'si.statement_id = s.id AND si.id='.$lineitemdId.'', 'inner');
          $query =$this->db->get();
          return $query->row_array();
        }
      }

      function getHeaderData($type){
        if(!empty($type) && $type=='statement'){
        $dataHeader = ['Stmt Line item ID', "Stmt ID", "Bank",
                      "Statement Issue Date",  "LI Desc",  "Trx Date",  
                      "LI Vendor ID", "LI Vendor Name", "Cred/Debit",
                      "LI Category ID By AI",  "LI Category Name By AI",
                      "Category Mapped ID By AI",  "LI Category ID By User",
                      "LI Category Name By User",  "FLAG",  "AI	Category Mapped ID By User",
                      "AI SENT"];
        } else if($type=='invoice') {
          $dataHeader = [
                      'Invoice Line item ID',"Invoice ID", 
                      "Invoice Number", "Order Number", 
                      "Vendor ID","Vendor Name", "Invoice Date", 
                      "gst", "TOTAL Amount","PAID Amount",
                      "DUE Amount",
                      "LI Item Desc", "LI Qty", "LI UNIT PRICE", 
                      "LI Total amount", "LI Gst",  "LI Category ID By AI",  
                      "LI Category Name By AI",  "Category Mapped ID By AI",  
                      "Category Mapped ID By User",  "LI Category ID By User",
                      "LI Category Name By User", "FLAG",
                      "AI SENT"];
        }
        return $dataHeader;
      }

      public function export_statement_data($datas){
        $dataRes = array();
        $updatedata = array();
        $dataHeader =  $this->getHeaderData('statement');
        foreach($datas as $data){
          $results = $this->getStatementLineItemById($data['id']);

          $vendorDetails =explode('-',$results['vendor_details']);
          $vendor_name = !empty($vendorDetails[0])?$vendorDetails[0]:'';
          $vendor_id = !empty($vendorDetails[1])?$vendorDetails[1]:'';

          $categorybyUser = explode('-',$results['category_details']);
          $li_category_name_by_user = !empty($categorybyUser[0])?$categorybyUser[0]:'';
          $li_category_id_by_user = !empty($categorybyUser[1])?$categorybyUser[1]:'';

          $original_data_ai =explode('-',$results['orignal_map_details']);
          $li_category_name_by_ai = !empty($original_data_ai[0])?$original_data_ai[0]:'';
          $li_category_id_by_ai = !empty($original_data_ai[1])?$original_data_ai[1]:'';

          $dataRes[] =   array(
            $results['statement_ine_item_id'], $results['statement_id'],
            $results['bankname'],$results['issue_date'],$results['description'],
            $results['transaction_date'], $vendor_name,
            $vendor_id, ($results['transaction_type']==2)?'Credit':'Debit',
            $li_category_id_by_ai,  $li_category_name_by_ai,$results['original_map_id'],
            $li_category_id_by_user, $li_category_name_by_user, $results['flag'],
            $results['mapped_id'],1);
            $updatedata[]=['id'=>$data['id'],'ai_sent'=>1];
        }
        $filename = time() . '_line_item_statement' . '.xls';
        $filePath = FCPATH.USER_EXPORT_PATH . '/';
        $result =  $this->Common_model->export_as_excel($dataHeader, $dataRes, ['file_name'=>$filename, 'file_dir_path'=>$filePath,'sheet_name'=>'STATEMENT']); 
        if(!empty($result) && $result['status']==true){
            $updateBatch = $this->CommonModel->updateBatch($updatedata,TBL_PREFIX.'statement_line_item','id');
            if($updateBatch){
                return ['status'=> true, 'filename'=> $filename];
            }
        } else {
            return false;
        }
      } 


      function export_invoice_data($datas){
        $dataRes = array();
        $updatedata = array();
        $dataHeader =  $this->getHeaderData('invoice');
        foreach($datas as $data){
          $results = $this->getInvoiceLineItemById($data['id']);

          $vendorDetails =explode('-',$results['vendor_details']);
          $i_vendor_name = !empty($vendorDetails[0])?$vendorDetails[0]:'';
          $i_vendor_id = !empty($vendorDetails[1])?$vendorDetails[1]:'';

          $original_data_ai =explode('-',$results['orignal_map_details']);
          $i_li_category_name_by_ai = !empty($original_data_ai[0])?$original_data_ai[0]:'';
          $i_li_category_id_by_ai = !empty($original_data_ai[1])?$original_data_ai[1]:'';
          $i_orignal_mapped_id_by_ai  = $results['original_map_id'];

          $categorybyUser = explode('-',$results['category_details']);
          $i_li_category_name_by_user = !empty($categorybyUser[0])?$categorybyUser[0]:'';
          $i_li_category_id_by_user = !empty($categorybyUser[1])?$categorybyUser[1]:'';
          $i_mapped_id_by_user = $results['mapped_id'];

          $dataRes[] =   array(
            $results['invoice_ine_item_id'],$results['invoice_id'],
            $results['invoice_number'],$results['order_number'], 
            $i_vendor_id,$i_vendor_name ,
            $results['invoice_date'],$results['total_amount'], 
            $results['paid_amount'],$results['due_amount'], 
            $results['item_description'],$results['qty'],
            $results['unit_price'],$results['amount'],
            $i_li_category_id_by_ai,$i_li_category_name_by_ai,
            $i_orignal_mapped_id_by_ai,$i_mapped_id_by_user,
            $i_li_category_id_by_user,$i_li_category_name_by_user,
            $results['flag'],1);
          $updatedata[]=['id'=>$data['id'],'ai_sent'=>1];
        }
        $filename = time() . '_line_item_invoice' . '.xls';
        $filePath = FCPATH.USER_EXPORT_PATH . '/';
        $result =  $this->Common_model->export_as_excel($dataHeader, $dataRes, ['file_name'=>$filename, 'file_dir_path'=>$filePath,'sheet_name'=>'INVOICE']); 
        if(!empty($result) && $result['status']==true){
            $updateBatch = $this->CommonModel->updateBatch($updatedata,TBL_PREFIX.'invoice_line_item','id');
            if($updateBatch){
                return ['status'=> true, 'filename'=> $filename];
            }
        } else {
            return false;
        }
      }

      public function  getInvoiceLineItemById($lineitemdId){
        if(!empty($lineitemdId)){
           $sub_query_to_get_vendor = $this->get_invoice_vendor_details();
           $sub_query_to_get_category = $this->get_invoice_category_details();
           $sub_query_to_get_original_map = $this->get_invoice_original_map_details();
          $sort_columns = array("li.id as invoice_ine_item_id", 
           "l.id as invoice_id", 
           "l.invoice_number",
           "l.order_number",
           "l.vendor_id", 
           "l.total_amount",
           "l.paid_amount",
           "l.due_amount",
           "li.item_description",
           "li.qty",
           "li.unit_price",
           "li.amount",
           "li.original_map_id",  
           "li.mapping_id as mapped_id",
           "li.flag",
           "DATE_FORMAT(li.invoice_date, '%d/%m/%Y') as invoice_date",
            "(" . $sub_query_to_get_vendor . ") as vendor_details",
            "(" . $sub_query_to_get_category . ") as category_details",
          //  "(" . $sub_query_to_get_bankname . ") as bankname",
            "(" . $sub_query_to_get_original_map . ") as orignal_map_details",
          );
          $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $sort_columns)), false);
          $array = array('l.archive' => 0, 'li.archive' => 0);
          $this->db->where($array);
          $this->db->from(TBL_PREFIX.'invoice as l');
          $this->db->join(TBL_PREFIX.'invoice_line_item as li', 'li.invoice_id = l.id AND li.id='.$lineitemdId.'', 'inner');
          $query =$this->db->get();
          return $query->row_array();
        }
      }

      private function get_invoice_vendor_details() {
        $this->db->select('CONCAT(v.name,"-",v.id)');
        $this->db->from(TBL_PREFIX.'vendor v');
        $this->db->where("v.id=l.vendor_id AND v.archive=li.archive", NULL, false);
        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }

    private function get_invoice_category_details() {
      $this->db->select('CONCAT(c.category_name,"-",c.id)');
      $this->db->from(TBL_PREFIX.'category_mapping cm');
      $this->db->where("cm.id=li.mapping_id  AND  cm.archive=li.archive", NULL, false);
      $this->db->join(TBL_PREFIX.'category as c','c.id = cm.category_id AND c.archive=0', 'inner');
      return $this->db->get_compiled_select();
    }

    private function get_invoice_original_map_details() {
      $this->db->select('CONCAT(c.category_name,"-",c.id)');
      $this->db->from(TBL_PREFIX.'category_mapping cm');
      $this->db->where("cm.id=li.original_map_id AND cm.archive=li.archive AND cm.archive=0", NULL, false);
      $this->db->join(TBL_PREFIX.'category as c','c.id = cm.category_id AND c.archive=0', 'inner');
      return $this->db->get_compiled_select();
  }
 
}
