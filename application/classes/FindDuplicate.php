<?php

class FindDuplicate {
   var $userId;

  function __construct($user_id){
    $this->userId = $user_id;
  }

  
  function findDuplicateLineItem(){
    $CI = & get_instance();   
    $table = 'duplicate_fetch';
    $line_item_table = 'statement_line_item';
    $last_fetch_time =    $CI->BasicModel->getRecordWhereOrderby($table, 'last_fetch_id', '', 'id',  'desc');
   
    if(!empty($last_fetch_time)){
      $fetchId = $last_fetch_time[0]->last_fetch_id; 
      $where = array('read_status'=>2, 'id > ' => $fetchId);
    } else {
      $where = array('read_status'=>2);
    }

    $current_fetch  = $CI->BasicModel->getRecordWhereOrderby($line_item_table,  "id,transaction_date,amount,description,mapped_id", $where , 'id',  'asc');

    $result = array();
    if(!empty($current_fetch)){
     foreach ($current_fetch as $key => $st) {
        $where = array('transaction_date'=>$st->transaction_date,  'amount'=>$st->amount , 
        'description'=>$st->description, 'mapped_id'=>$st->mapped_id,   'status'=>1,  'id!=' => $st->id );
        $result = $CI->BasicModel->getRecordWhere(TBL_PREFIX.$line_item_table, array('id','transaction_date', 'amount', 'description','mapped_id'), $where);
        
        if(!empty($result) && $result!=null){
            $CI->BasicModel->updateRecords(TBL_PREFIX.$line_item_table,['status'=>3,'original_id'=>$result->id],['id' => $st->id]);
        }
     }
     $last_fetch_id =  $CI->BasicModel->getRecordWhereOrderby($line_item_table, 'id', '', 'id',  'desc');
     if(!empty($last_fetch_id)){
       $CI->BasicModel->insertRecords(TBL_PREFIX.$table, array('user_id'=>$this->userId, 'last_fetch_id'=>$last_fetch_id[0]->id));
       return true;
     }
   } else {
     return false;
   }
 }



}
?>