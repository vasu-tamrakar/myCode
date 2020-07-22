<?php defined('BASEPATH') || exit('No direct script access allowed');

require_once APPPATH . 'classes/StatementCheck.php';

class StatementModel extends CI_Model
{

  public function statmentBankList($requestData){
    $columnArr =  ['b.id as value','b.bank_name as label'];
    $whereArr = array('b.archive' => 0, 'b.status' => 1);
    $result = $this->CommonModel->getDataWhere($columnArr, $whereArr, TBL_PREFIX.'bank as b', ['result_array'=>3]);
    return $result;
  }

   

  public function insertStatement($stmtData){
    $this->db->insert(TBL_PREFIX.'statement',$stmtData);
    return $this->db->insert_id();
  }

  function get_single_value($doc, $data, $reading_start, $counter) {
      $required_field = '';
      $data = $doc->getElementsByTagName('p');
      $cheak_required_field = false;

      foreach ($data as $inv) {
          if (strpos($inv->nodeValue, $reading_start) !== false) {
              $cheak_required_field = true;
              $cnt_val = 0;
          }
          if ($cheak_required_field && $cnt_val >= $counter) {
              if (!$required_field) {
                  $required_field = $inv->nodeValue;
              }
          }
          if ($cheak_required_field) {
              $cnt_val++;
          }
      }
      return $required_field;
  }


  function checkPasswordProtectedFile($targetPath,$filePath,$password=null){
    $checkPasswordProtected = check_pdf_is_encrypted($filePath);
    if($checkPasswordProtected){
      if(!empty($password)){
        $fileDetails = encrypyted_pdf_to_decrypt($targetPath,$filePath,$password);
        if($fileDetails){
          return ['status'=>true, 'protected' => 1, 'filename' => pathinfo($targetPath)['basename']];
          
        } else {
          // echo json_encode(array('status' => false, 'error' => 'Wrong Password'));
          // exit();
          return ['status'=>false, 'protected' => 1];
        }
      } else {
        echo json_encode(array('status' => false, 'error' => 'File Has Password Protected,Password required'));
        exit();
      }
    } else {
        return ['status'=>false, 'protected' => 0];;
    }

	}

  public function readUploadStatement($id,$password,$original_file_name){
  
        $unreadpdfData = array();
        $stmtPageDetails1 = array();
        $whereCondition = array('id'=>$id);
        $result = $this->db->where($whereCondition)->get(TBL_PREFIX.'statement')->row_array();
        if(!empty($result)){
         
          $filePath = FCPATH.'uploads/statement/' . $result['user_id'].'/'.$result['statement_file_name'];
          $targetPath = FCPATH.'uploads/statement/' . $result['user_id'].'/'.time().'_'.$result['statement_file_name'];
          $checkPasswordProtectedFile = $this->checkPasswordProtectedFile($targetPath,$filePath,$password);
          
          //checkencrypted or not 
          if(!empty($checkPasswordProtectedFile) && $checkPasswordProtectedFile['protected']==1){
              if($checkPasswordProtectedFile['status']==true){
                $stmtPageDetails1['statement_file_name'] = $checkPasswordProtectedFile['filename']; 
                $this->BasicModel->updateRecords(TBL_PREFIX.'statement', $stmtPageDetails1, array('id'=>$id));
                $result = $this->db->where($whereCondition)->get(TBL_PREFIX.'statement')->row_array();
              } else if($checkPasswordProtectedFile['status']==false) {
                $unreadpdfData = array(
                  'file' => $result['statement_file_name'],
                  'protected' => $result['statement_file_name'],
                  'original_file_name' => $original_file_name,
                  'user_id' => $result['user_id'],
                  'from' => 3
                );
                $this->insert_unreadable_pdffile_data($unreadpdfData);
                echo json_encode(array('status' => false, 'password'=>false, 'error' => 'Wrong Password, Added in Unreadable files'));
                exit();
              }
            }
            $storePath = USER_STATEMENT_PATH.$result['user_id'].'/'.$result['id'].'/';
            $pdfPath = FCPATH.USER_STATEMENT_PATH.$result['user_id'].'/'.$result['statement_file_name'];
            $pdfFileName= explode('.pdf',$result['statement_file_name'])[0];
            $htmlFiles = convertPdfToHtml($pdfFileName,$pdfPath,$storePath);
           
            if(!empty($htmlFiles) && count($htmlFiles)>0){
               $htmlAllFiles = explode(",",$htmlFiles);
               $statementCheck= new StatementCheck($htmlAllFiles,$id);
               $stmtDetails = $statementCheck->checkTypeStmt();
               if(!empty($stmtDetails) && $stmtDetails>0){
                 $stmtPageDetails  = $stmtDetails['statement'];
                 $stmtPageDetails['status']=1;
                 $stmtPageDetails['change_status']=2;
                 $stmtTransactionDetails = $stmtDetails['transaction'];
                 if(!empty($stmtTransactionDetails)){
                   $this->BasicModel->insertRecords(TBL_PREFIX.'statement_line_item', $stmtTransactionDetails, $multiple = TRUE);
                 }
                 if(!empty($stmtPageDetails)){
                   $this->BasicModel->updateRecords(TBL_PREFIX.'statement', $stmtPageDetails, array('id'=>$id));
                 }
                 return true;
               } else {
                   echo json_encode(array('status' => false, 'error' => 'Sorry, This file is not supported to read'));
                   exit;
               }
            }
        } else {
          return false;
        }
  }

  function  insert_unreadable_pdffile_data($data=NULL){
    $alldata=array();
    $tableName = TBL_PREFIX.'unreadable_pdf';
    $row['file_name'] = $data['file'];
    $row['user_id'] = $data['user_id'];
    $row['status'] = 1;
    $row['source_type'] = $data['from'];
    $row['created'] = create_date_store_in_db();
    $row['protected'] = $data['protected'];
    $row['original_file_name'] = $data['original_file_name'];
    $alldata[] = $row;
    $this->BasicModel->insertRecords($tableName, $alldata, $multiple = TRUE);
  }


  public function readAllStatements($userId){
      $columnArr = ['id','user_id','statement_file_name','change_status','source_type'];
      $whereCondition = array('user_id'=>$userId, 'change_status'=>1,'source_type'=>3);
      $tableName = TBL_PREFIX.'statement';
      $count_read=0; $count_array=[];
      $results = $this->CommonModel->getDataWhere($columnArr, $whereCondition, $tableName, ['result_array'=>3]);
        if(!empty($results)){
          foreach ($results as $key =>  $result) {
            $result = (array)$result;
            $storePath = USER_STATEMENT_PATH.$result['user_id'].'/'.$result['id'].'/';
            $pdfPath = FCPATH.USER_STATEMENT_PATH.$result['user_id'].'/'.$result['statement_file_name'];
            $pdfFileName= explode('.pdf',$result['statement_file_name'])[0];
            $htmlFiles = convertPdfToHtml($pdfFileName,$pdfPath,$storePath);
            $id = $result['id'];
            if(!empty($htmlFiles) && count($htmlFiles)>0){
               $htmlAllFiles = explode(",",$htmlFiles);
               $statementCheck= new StatementCheck($htmlAllFiles,$id);
               $stmtDetails = $statementCheck->checkTypeStmt();
               if(!empty($stmtDetails) && $stmtDetails>0){
                 $stmtPageDetails  = $stmtDetails['statement'];
                 $stmtPageDetails['status']=1;
                 $stmtPageDetails['change_status']=2;
                 $stmtTransactionDetails = $stmtDetails['transaction'];
                 $count_array[] = count($stmtPageDetails); 
                 if(!empty($stmtTransactionDetails)){
                   $this->BasicModel->insertRecords(TBL_PREFIX.'statement_line_item', $stmtTransactionDetails, $multiple = TRUE);
                 }
                 if(!empty($stmtPageDetails)){
                   $this->BasicModel->updateRecords(TBL_PREFIX.'statement', $stmtPageDetails, array('id'=>$id));
                 }
               }
            }
          }
          $count_read = (!empty($count_array))?count($count_array):0;
          return ['status'=>true, 'count'=>  $count_read ]; 
        } else {
          return ['status'=>false, 'count'=>0];
        }
  }


   public function list_statement($request_body){
     $login_user_id = $request_body->user_id;
     $limit = $request_body->data->pageSize;
     $page = $request_body->data->page;
     $sorted = $request_body->data->sorted;
     $orderBy = '';
     $direction = '';
     $filter = $request_body->data->filtered;
     $getUserTimezone = $request_body->time_zone_mysql?? '+00:00';
     $sub_query_to_get_bankname = $this->get_bankname('name');
     $sub_query_to_get_read_status_count = $this->get_statement_read_status_count();
     $sort_columns = array("s.id",
     "s.statement_notes", 
     "s.statement_for",
     "(" . $sub_query_to_get_bankname . ") as bankname",
     "(" . $sub_query_to_get_read_status_count . ") as read_status_count",
     "s.statement_type",
     "DATE_FORMAT(s.issue_date, '%d/%m/%Y') as issue_date",
     "DATE_FORMAT(CONVERT_TZ(s.created,'+00:00', '".$getUserTimezone."'), '%d/%m/%Y') 
     as created");
     if (isset($filter->search) && $filter->search != '') {
       $this->db->group_start();
       for ($i = 0; $i < count($sort_columns); $i++) {
         $column_search = $sort_columns[$i];
         if (strstr($column_search, "as") !== false) {
           $search_column = explode(" as ", $column_search);
           if ($search_column[0] != 'null')
             $this->db->or_like($search_column[0], $filter->search);
         } else if ($column_search != 'null') {
           $this->db->or_like($column_search, $filter->search);
         }
       }
       $this->db->group_end();
     }
 

     $sortorder = getSortBy(
			$sorted, 
			['created'=>"s.created"], 
			['orderBy'=> 's.id', 'direction'=>'DESC']
    );

     $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $sort_columns)), false);
     $array = array('s.archive' => 0, 's.status' => 1,  's.user_id' => $login_user_id);
     $this->db->where($array);
    // $this->db->where("EXISTS(SELECT * FROM tbl_fm_statement_line_item as sli where sli.statement_id=s.id and sli.archive=s.archive and sli.read_status=2)");
     $this->db->order_by($sortorder['orderBy'], $sortorder['direction']);
     $this->db->limit($limit, ($page * $limit));
     $query =$this->db->get(TBL_PREFIX.'statement as s');

     $dt_filtered_total = $all_count = $this->db->query('SELECT FOUND_ROWS() as pages;')->row()->pages;
     if ($dt_filtered_total % $limit == 0) {
       $dt_filtered_total = ($dt_filtered_total / $limit);
     } else {
       $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
     }
     $result = $query->result();
     $return = array('pages' => $dt_filtered_total, 'data' => $result, 'all_count' => $all_count);

     return $return;
   }

  public function get_statement_read_status_count(){
    $select  = 'count(sli.id)';
    $this->db->select($select);
    $this->db->from(TBL_PREFIX.'statement_line_item sli');
    $this->db->where("s.id=sli.statement_id AND s.archive=sli.archive AND sli.read_status=2", NULL, false);
    $this->db->limit(1);
    return $this->db->get_compiled_select();
  }

  public function viewDetailStatement($requestData){
    $userId = $requestData->user_id;
    $limit = $requestData->data->pageSize;
    $page = $requestData->data->page;
    $sorted = $requestData->data->sorted;
    $statementId = $requestData->data->statement_id;
    $formType = $requestData->data->formType;
    $filter = $requestData->data->filtered;
    if(!empty($statementId)){
      $sub_query_to_get_vendor = $this->get_vendor_details('name');
      $sub_query_to_get_vendor_id = $this->get_vendor_details('id');
      $sub_query_to_get_category = $this->get_category_details('name');
      $sub_query_to_get_category_id = $this->get_category_details('id');
     
      $sort_columns = array("si.id", 
      "si.description",
       "si.transaction_type",
       "si.transaction_date as date",
       "si.amount",
       "si.main_balance",
       "si.status",
       "si.read_status",
       "DATE_FORMAT(s.issue_date, '%d/%m/%Y') as issue_date",
       "DATE_FORMAT(si.transaction_date, '%d/%m/%Y') as transaction_date",
       "(" . $sub_query_to_get_vendor . ") as vendor_name",
       "(" . $sub_query_to_get_vendor_id . ") as vendor_id",
       "(" . $sub_query_to_get_category . ") as category_name",
       "(" . $sub_query_to_get_category_id . ") as category_id",
     
     
      );
      if (isset($filter->search) && $filter->search != '') {
        $this->db->group_start();
        for ($i = 0; $i < count($sort_columns); $i++) {
          $column_search = $sort_columns[$i];
          if (strstr($column_search, "as") !== false) {
            $search_column = explode(" as ", $column_search);
            if ($search_column[0] != 'null')
              $this->db->or_like($search_column[0], $filter->search);
          } else if ($column_search != 'null') {
            $this->db->or_like($column_search, $filter->search);
          }
        }
        $this->db->group_end();
      }

      $sortorder = getSortBy(
        $sorted, 
        ['created'=>"s.created"], 
        ['orderBy'=> 's.id', 'direction'=>'DESC']
      );

     
      $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $sort_columns)), false);
      $array = array('s.archive' => 0, 'si.archive' => 0, 's.user_id' => $userId,'s.id'=>$statementId);
      $this->db->where($array);
      $this->db->order_by($sortorder['orderBy'], $sortorder['direction']);
      if($formType=='view'){
        $this->db->limit($limit, ($page * $limit));
      }
      $this->db->from(TBL_PREFIX.'statement as s');
      $this->db->join(TBL_PREFIX.'statement_line_item as si', 'si.statement_id ='.$statementId.'', 'inner');
      $query =$this->db->get();

      $statementTransactionData = $query->result_array();
      $dt_filtered_total = $all_count = $this->db->query('SELECT FOUND_ROWS() as pages;')->row()->pages;
      if ($dt_filtered_total % $limit == 0) {
        $dt_filtered_total = ($dt_filtered_total / $limit);
      } else {
        $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
      }
      $vendorIds = array_column($statementTransactionData,'vendor_id');
      $vendorDisapprove=$this->get_pending_cat($vendorIds);

      $statementData = $this->statemenData($userId,$statementId);
      $pdfFile = USER_STATEMENT_PATH.$userId.'/'.$statementData['statement_file_name'];
      $return = array('pages' => $dt_filtered_total,
      'statement_pdf_file' => (file_exists($pdfFile))?$pdfFile:'',
      'statement_data'=>$statementData,
      'statement_transaction' => $statementTransactionData,
      'vendor_disapproved_cat' => $vendorDisapprove,
      'all_count' => $all_count);
      return $return;
    }
  }
  function get_pending_cat($vendorId=[]){
    $vendorId = !empty($vendorId) ? $vendorId:0;
    $vendorId = is_array($vendorId) ? $vendorId :[$vendorId];
    $this->db->select(["group_concat(DISTINCT sub_vm.category_id) as diapproved_cat","sub_vm.vendor_id"]);
    $this->db->from(TBL_PREFIX."vendor_mapping` `sub_vm`");
    $this->db->where(["sub_vm.status"=>3,"sub_vm.archive"=>0]);
    $this->db->where_in("sub_vm.vendor_id",$vendorId);
    $this->db->group_by("sub_vm.vendor_id");
    $query = $this->db->get();
    $res = $query->num_rows()>0 ? $query->result_array():[];
    return !empty($res) ? pos_index_change_array_data($res,'vendor_id'):[];
  }

 
  function statemenData($userId,$statementId){
    $whereArray = array('s.archive' => 0, 's.user_id' => $userId, 's.id'=>$statementId);
    $sub_query_to_get_bankname = $this->get_bankname('name');
    $sub_query_to_get_bankname_id = $this->get_bankname('id');
      $sort_columns2=array("s.statement_file_name", 
      "s.statement_for", 
      "s.statement_type",  
      "s.issue_date" ,
      "(" . $sub_query_to_get_bankname_id . ") as bankname",
      "(" . $sub_query_to_get_bankname . ") as b_name",
      "s.source_type");
      $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $sort_columns2)), false);
      $this->db->where($whereArray);
      $this->db->from(TBL_PREFIX.'statement as s');
      return  $this->db->get()->row_array();
  }


private function get_bankname($column) {
  $select  = ($column=='name')? 'b.bank_name' : 'b.id' ;
  $this->db->select($select);
  $this->db->from(TBL_PREFIX.'bank b');
  $this->db->where("b.id=s.statement_for AND b.status=1 AND b.archive=0", NULL, false);
  return $this->db->get_compiled_select();
}

private function get_vendor_details($column) {
  $select  = ($column=='name')? 'v.name' : 'v.id' ;
  $this->db->select($select);
  $this->db->from(TBL_PREFIX.'vendor_mapping vm');
  $this->db->where("vm.id=si.mapped_id AND vm.archive=si.archive AND vm.archive=0", NULL, false);
  $this->db->join(TBL_PREFIX.'vendor as v', 'v.id =vm.vendor_id AND v.archive=0', 'inner');
  $this->db->limit(1);
  return $this->db->get_compiled_select();
}
 

private function get_category_details($column) {
  $select  = ($column=='name')? 'c.category_name' : 'c.id' ;
  $this->db->select($select);
  $this->db->from(TBL_PREFIX.'vendor_mapping vm');
  $this->db->where("vm.id=si.mapped_id AND vm.archive=si.archive AND vm.archive=0", NULL, false);
  $this->db->join(TBL_PREFIX.'category as c','c.id = vm.category_id AND c.archive=0', 'inner');
  return $this->db->get_compiled_select();
}

public function addOrUpdateStatementData($requestData){

  $userId = $requestData->user_id;
  $statementId = $requestData->data->statement_id;
  $duplicateConfirm = $requestData->data->duplicate;
  $original_id=$requestData->data->original_id;
  $statementDetails = $requestData->data->statmentData;
  $statementTransactionDetails = $requestData->data->transactionData;
  $created = create_date_store_in_db();
  //Duplicate check 
  if(!$duplicateConfirm){
    $duplicateEntryExist = $this->checkDuplicateEntryExist($statementDetails,$statementTransactionDetails);
  } 
  // Add Statement 
  if(isset($statementDetails)&&!empty($statementDetails) && empty($statementId)){
      $statementData['statement_for'] = $statementDetails->statement_for_of;
      $statementData['issue_date']=$statementDetails->issue_date;
      $statementData['user_id']=$userId;
      $statementData['status']=1;
      $statementData['created'] = $created;
      $statementData['source_type']=2; //manual=2
      $statementId = $this->BasicModel->insertRecords(TBL_PREFIX."statement", $statementData, $multiple = FALSE);
      if(isset($statementTransactionDetails)&&!empty($statementTransactionDetails) && !empty($statementId)){
        foreach ($statementTransactionDetails as $trans) {
             
              $insertData = array(
                'transaction_date' => (isset($trans->date))?date('Y-m-d',strtotime($trans->date)):date('Y-m-d'),
                'description' => $trans->description,
                'transaction_type' => $trans->transaction_type,
                'amount' => $trans->amount,
                'statement_id' => $statementId,
                'read_status' => '1',
                'main_balance'=>$trans->main_balance,
                //'mapped_id' => !empty($mapped_id)-,
                //'original_id'=> !empty($original_id)?$original_id:''
              );

              if(!empty($original_id)){
                $insertData['original_id'] = (int) $original_id;
                $insertData['status'] = 3;
              }
               $completeAddLineItem[] =  $insertData;
         
        }
        $insertTransaction=  $this->CommonModel->insertBatch($completeAddLineItem,TBL_PREFIX."statement_line_item");
        if($insertTransaction){
          $this->loges->setActivityType('add_new_manual_statement');
          $this->loges->setDescription(json_encode($requestData));
          $this->loges->setTableID($statementId);
          $this->loges->setCreatedBy($userId);
          $this->loges->setCreatedType(3);
          $this->loges->createLog();
          return true;
        }
    }
  }

  // Edit Statement 
  if(isset($statementDetails)&&!empty($statementDetails) && !empty($statementId)){
    if(isset($statementTransactionDetails)&&!empty($statementTransactionDetails) && !empty($statementId)){

      $ustatementData['statement_for'] = $statementDetails->statement_for_of;
      $ustatementData['issue_date']=$statementDetails->issue_date;
      $ustatementData['updated'] = $created;
      $this->BasicModel->updateRecords(TBL_PREFIX."statement", $ustatementData, array("id" => $statementId));

      $tids = $this->getAllTranactionIds($statementId);
      foreach ($statementTransactionDetails as $k => $trans) { 
        $dataNew[] = !empty($trans->id)?$trans->id:'';

        $updateData = []; 

        $updateData['statement_id'] = ($statementId)?$statementId:0;
        $updateData['transaction_date'] = (isset($trans->date))?date('Y-m-d',strtotime($trans->date)):date('Y-m-d');
        $updateData['description']=$trans->description;
        $updateData['transaction_type']=$trans->transaction_type;
        $updateData['amount']=$trans->amount;
        $updateData['main_balance']=$trans->main_balance;
      
        // Category Changes Update
      if(isset($trans->categoryChanged) && $trans->categoryChanged>0 || isset($trans->vendorChanged) && $trans->vendorChanged>0 && !empty($trans->id)){
          if(!empty($trans->category_id) && !empty($trans->vendor_id)){
            $flagValue = $single_line_item = $this->CommonModel->getDataWhere(['mapped_id','flag'], ['id' => $trans->id,'archive' => 0], TBL_PREFIX.'statement_line_item', ['result_type' => '3']);
              $mappedData =  $this->categoryChangesUpdate($trans->category_id,$trans->id,$trans->vendor_id,$userId);
              if(!empty($mappedData)) {
                if($flagValue->flag==0){
                  $updateData['mapped_id']=$mappedData['mapped_id'];
                  $updateData['original_map_id']=$flagValue->mapped_id;
                } else {
                  $updateData['mapped_id']=$mappedData['mapped_id'];
                }
                //$this->CategoryChangeNotification($trans->vendor_name,$userId);
                $this->addUserMapping($trans->vendor_id,$userId);
              }
              $updateData['ai_sent']=0;
              $updateData['flag'] = 1;
           }
      }

        // Duplicate
        if(!empty($original_id)){
          $updateData['original_id'] = (int) $original_id;
          $updateData['status'] = 3;
        }


        if(empty($trans->id)){
          $updateData['created'] = $created;
          $this->BasicModel->insertRecords(TBL_PREFIX."statement_line_item", $updateData, $multiple = FALSE);
        } else {
          $updateData['updated'] = $created;
          $this->BasicModel->updateRecords(TBL_PREFIX."statement_line_item", $updateData, array("id" => $trans->id));
        }
      }

      $kdata= array();  
      if (!in_array($tids, $dataNew)) {
        $d_trans_id = array_diff($tids, $dataNew); 
        foreach($d_trans_id as $trans_id){
          $kdata[]=['id'=>$trans_id, 'archive'=>1];
        }
        $this->CommonModel->updateBatch($kdata,TBL_PREFIX."statement_line_item",'id');
      }
      
      return true;
    }
  }
 
}

public function getVendorMappedId($trans_id){
  $whereArray = ['id' => $trans_id,'archive' => 0];
  $original_map_id= array();
  $single_line_item = $this->CommonModel->getDataWhere(['mapped_id','flag'], $whereArray, TBL_PREFIX.'statement_line_item', ['result_type' => '3']);
  if(!empty($single_line_item ) && !empty($single_line_item->mapped_id)){
    $datas = $this->CommonModel->getDataWhere(['id','vendor_id','category_id'],['id'=>$single_line_item->mapped_id,'archive'=>0], TBL_PREFIX.'vendor_mapping', ['result_type' => '4']);
    $original_map_id = array(
    'id' =>  $datas['id'], 
    );
  } 
  return $original_map_id;
}

public function categoryChangesUpdate($categoryId,$transId,$vendorId,$userId){
  $updateData=array();
  if(!empty($categoryId) && $categoryId!=0){
    $mapped_details = $this->getVendorMappedId($transId); 
      if(!empty($mapped_details)){
        $mapped_id =  $this->categoryVendorMapUpdate($vendorId,$categoryId,$userId);
        $updateData['mapped_id'] = $mapped_id;
        $updateData['original_map_id'] = $mapped_details['id']; 
    } 
  }
  return $updateData;
}

public function addUserMapping($vid,$userId){
  $whereArray = [
    'vendor_id' => $vid,
    'user_id' => $userId,
    'archive' => 0
  ];
  $user_mapping_id = $this->CommonModel->getDataWhere(['id'], $whereArray, TBL_PREFIX.'vendor_user_mapping', ['result_type' => '3']);
  if(empty($user_mapping_id)){
    $updateMapping = array(
      'vendor_id' => $vid,
      'user_id' => $userId,
      'archive' => 0
    );
    $last_user_mapping_id = $this->BasicModel->insertRecords(TBL_PREFIX."vendor_user_mapping", $updateMapping, $multiple = FALSE);
  }
}

public function getAllTranactionIds($statementId){
  $tids=$this->CommonModel->getDataWhere(['id'],['statement_id'=>$statementId],TBL_PREFIX."statement_line_item",['result_array'=>2]);
  $transactionIds = array();
  foreach($tids as $t){
    $transactionIds[] = $t->id;
  } 
   

  return $transactionIds;
}

public function CategoryChangeNotification($vendorName,$userId){
   /* Notification set */
   $created = create_date_store_in_db();
   $this->notification->setAlertTitle("New category is changed in ".$vendorName.", in statement for approval");
   $this->notification->setAlertType(2);
   $this->notification->setUserId(0);
   $this->notification->setDescription("New category is changed in ".$vendorName.", in statement  for approval");
   $this->notification->setIsRead(2);
   $this->notification->setNotificationCreated($created);
   $this->notification->setCreatedByType(1);
   $this->notification->setCreatedBy($userId);
   $this->notification->setNotificationArchive(0);
   $this->notification->SaveUserNotificationAlert();
}




  public function checkDuplicateEntryExist($statementDetails,$transactions){
    if(!empty($statementDetails) && !empty($transactions)){
      $bankNumber = $statementDetails->statement_for_of;
      foreach ($transactions as $key => $trans) {
      $id = null;
      if(isset($trans->id) && !empty($trans->id)){
        $id =   $trans->id;
      }

      $where_is_duplicate = ['original_id!='=>null, 'archive'=>'0', 'id=' =>$id];
      $is_already_duplicate = $this->CommonModel->getDataWhere(['original_id'], $where_is_duplicate, TBL_PREFIX.'statement_line_item', ['result_array'=>4]);
      
      if(empty($is_already_duplicate)){
        $columnArr = ['sli.id','sli.transaction_date','sli.description','sli.amount','sli.transaction_type','s.statement_for']; 
        $whereArr =['sli.transaction_date'=>date('Y-m-d',strtotime($trans->date)),
        'sli.description'=>$trans->description, 
        'sli.status'=>1,  
        'sli.transaction_type' => $trans->transaction_type,
        'sli.amount' =>$trans->amount,
        'sli.main_balance'=>$trans->main_balance,
        'sli.id!=' =>$id,
        'sli.original_id'=>null,
        'sli.archive'=>0,
       // 'sli.read_status'=>2,
        's.statement_for' => $bankNumber
        ];

        $this->db->select($columnArr);
        $this->db->where($whereArr);
        $this->db->join(TBL_PREFIX.'statement s', "s.id=sli.statement_id AND s.archive=sli.archive", 'inner');
        $this->db->from(TBL_PREFIX.'statement_line_item sli');
        $result = $this->db->get()->row_array();
        }
        if(!empty($result)){
          $response = array('status'=>false, 'key'=>'duplicate', 'original_id'=>$result['id'], 'msg' => '');
          echo json_encode($response);
          exit;
        }
      }
    }
  }
   
  public function categoryVendorMapUpdate($vid,$cid,$userId){
    $created = create_date_store_in_db();
    $checkExistCategory_id = $this->checkExistOnChangedCategory($vid,$cid);
      if(empty($checkExistCategory_id)){
        $miscellCatData = getMiscellaneousCatdetail();
        $miscellCategoryid = ($miscellCatData->id > 0)?$miscellCatData->id:0;
          if( (!empty($miscellCategoryid)) && ($miscellCategoryid >0) ){
            $status = ($miscellCategoryid == $cid)?2:1;
          }else{
            $status=1;
          }
          $updateMapping = array(
            'vendor_id' => $vid,
            'category_id' => $cid,
            'source_type' => 2,
            'status' => $status,
            'created' => $created,
            'archive' => 0,
          );
          $last_map_id = $this->BasicModel->insertRecords(TBL_PREFIX."vendor_mapping", $updateMapping, $multiple = FALSE);
          $this->VendorMappingNotification($vid,$cid,$userId);
        } else {
          $last_map_id = $checkExistCategory_id;
        }
       return $last_map_id;      
  }

  function VendorMappingNotification($vid,$cid,$userId){
    $created = create_date_store_in_db();
    $vendorData = $this->BasicModel->getRecordWhere(TBL_PREFIX.'vendor', 'name', ['id' => $vid, 'archive'=>0]);
    $catData = $this->BasicModel->getRecordWhere(TBL_PREFIX.'category', 'category_name', ['id' => $cid, 'archive'=>0]);
      $this->notification->setAlertTitle("New category ".$catData->category_name." is added in ".$vendorData->name." vendor,  in statement for approval");
      $this->notification->setAlertType(2);
      $this->notification->setUserId(0);
      $this->notification->setDescription("New category ".$catData->category_name." is added in ".$vendorData->name." vendor, in statement for approval.");
      $this->notification->setIsRead(2);
      $this->notification->setNotificationCreated($created);
      $this->notification->setCreatedByType(1);
      $this->notification->setCreatedBy($userId);
      $this->notification->setNotificationArchive(0);
      $this->notification->SaveUserNotificationAlert();
  }

  public function checkExistOnChangedCategory($vid,$cid){
    $whereArray = [
      'vendor_id' => $vid,
      'category_id' => $cid,
      'archive' => 0
    ];
    $mapped_id = $this->CommonModel->getDataWhere(['id'], $whereArray, TBL_PREFIX.'vendor_mapping', ['result_type' => '3']);
    if(!empty($mapped_id)){
      return (int) $mapped_id->id;
    }    
  }

  

  public function deleteStatementData($requestData){
    $userId = $requestData->user_id;
    $statementId = $requestData->data->statement_id;
    $this->BasicModel->deleteRecords(TBL_PREFIX.'statement',array('id'=>$statementId,'user_id'=>$userId));
    $this->BasicModel->deleteRecords(TBL_PREFIX.'statement_line_item',array('statement_id'=>$statementId));
    $this->loges->setActivityType('delete_bankstatement');
    $this->loges->setDescription(json_encode($requestData));
    $this->loges->setTableID($statementId);
    $this->loges->setCreatedBy($userId);
    $this->loges->setCreatedType(4);
    $this->loges->createLog();
    return true;
  }

  public function deleteStatementTransactionData($requestData){
   $userId = $requestData->user_id;
   $statementId = $requestData->data->statement_id;
   $transactionId = $requestData->data->transaction_id;
   $this->BasicModel->deleteRecords(TBL_PREFIX.'statement_line_item',array('statement_id'=>$statementId,'id'=>$transactionId));
   return true;
 }

 public function statement_ai_review_mapping_call($extraPrams=[]){
  $fromDate = $extraPrams['from_date'] ?? create_date_store_in_db();
  $toDate = $extraPrams['to_date'] ?? create_date_store_in_db();
  $this->db->select(['si.id']);
  $this->db->from(TBL_PREFIX.'statement_line_item si');
  $this->db->where(['si.archive'=>0,'si.flag'=>1,'ai_sent'=>0]);
  $this->db->where("si.updated between '".$fromDate."' and '".$toDate."'",null,false);
  $query =$this->db->get();
  return $query->num_rows()>0 ?  $query->result_array():[];
}

}
