<?php defined('BASEPATH') || exit('No direct script access allowed');

require_once APPPATH . 'classes/StatementCheck.php';

class InvoiceModel extends CI_Model
{

  
  public function __construct(){
    parent::__construct();
    $this->load->library('Notification');
  }
  public function read_statement(){

  }

  public function insertInvoice($invoiceData){
    $this->db->insert(TBL_PREFIX.'invoice',$invoiceData);
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




  public function readAllInvoices($id){
        $result = $this->db->where('id',$id)->get(TBL_PREFIX.'invoice')->row_array();
        if(!empty($result)){
              $storePath = USER_INVOICE_PATH.$result['user_id'].'/'.$result['id'].'/';
              $pdfPath = FCPATH.USER_INVOICE_PATH.$result['user_id'].'/'.$result['invoice_file'];
              $pdfFileName= explode('.', $result['invoice_file'])[0];
              $htmlFiles = convertPdfToHtml($pdfFileName,$pdfPath,$storePath);
                if((count($htmlFiles) > 0) && ($htmlFiles != false)){
                   $htmlAllFiles = explode(",",$htmlFiles);
                   $statementCheck= new StatementCheck($htmlAllFiles,$id);
                   $invoiceDetails = $statementCheck->checkTypeStmt();
                   if((!empty($invoiceDetails)) && ($invoiceDetails>0)){
                     $invPageDetails  = $invoiceDetails['invoice'];
                     $invPageDetails['status']=1;
                     $invPageDetails['change_status']=2;
                     $invTransactionDetails = $invoiceDetails['transaction'];
                     if(!empty($invTransactionDetails)){
                       $this->BasicModel->insertRecords(TBL_PREFIX.'invoice_line_item', $invTransactionDetails, TRUE);
                     }
                     if(!empty($invPageDetails)){
                       $this->BasicModel->updateRecords(TBL_PREFIX.'invoice', $invPageDetails, array('id'=>$id));
                     }
                     return true;
                   }
                }else {
                  echo json_encode(array('status' => false, 'error' => 'Files Not Converted to HTML'));
                  exit();
                }
        } else {
          return false;
        }
  }


   public function list_invoice($request_body){
     $getUserTimezone = $request_body->time_zone_mysql?? '+00:00';
     $login_user_id = $request_body->user_id;
     $limit = $request_body->data->pageSize;
     $page = $request_body->data->page;
     $sorted = $request_body->data->sorted;
     $orderBy = '';
     $direction = '';
     $filter = $request_body->data->filtered;
     $sort_columns = array("i.id", "i.invoice_number", "v.name as vendor_name", "i.total_amount", "i.paid_amount","i.due_amount","DATE_FORMAT(CONVERT_TZ('i.created','+00:00', '".$getUserTimezone."'), '%d/%m/%Y') as created");
     if (isset($filter->search) && $filter->search != '') {
       $this->db->group_start();
       for ($i = 0; $i < count($sort_columns); $i++) {
         $column_search = $sort_columns[$i];
         if (strstr($column_search, "as") !== false) {
            $search_column = explode(" as ", $column_search);
              if ($search_column[0] != 'null'){
                $this->db->or_like($search_column[0], $filter->search);
              }
         } else if ($column_search != 'null') {
           $this->db->or_like($column_search, $filter->search);
         }
       }
       $this->db->group_end();
     }
      $queryHavingData = $this->db->get_compiled_select();
      $queryHavingData = explode('WHERE', $queryHavingData);
      $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';


     if (!empty($sorted)) {
       if (!empty($sorted[0]->id)) {
         $orderBy = $sorted[0]->id;
         $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
       }
     } else {
       $orderBy = 'i.id';
       $direction = 'DESC';
     }

     $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $sort_columns)), false);
     $array = array('i.archive' => 0, 'i.status' => 1, 'i.user_id' => $login_user_id);
     $this->db->where($array);
     $this->db->order_by($orderBy, $direction);
     $this->db->join(TBL_PREFIX.'vendor AS v','i.vendor_id=v.id AND v.archive=0','inner');
     $this->db->limit($limit, ($page * $limit));
     if (!empty($queryHaving)) {
        $this->db->having($queryHaving);
      }
     $query =$this->db->get(TBL_PREFIX.'invoice AS i');

     $dt_filtered_total = $all_count = $this->db->query('SELECT FOUND_ROWS() as pages;')->row()->pages;
     if ($dt_filtered_total % $limit == 0) {
       $dt_filtered_total = ($dt_filtered_total / $limit);
     } else {
       $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
     }
     $result = $query->result();
     if(!empty($result)){
        $return = array('pages' => $dt_filtered_total, 'data' => $result, 'all_count' => $all_count);
     } else {
        $return = array('pages' => $dt_filtered_total, 'data' => [], 'all_count' => $all_count);
     }



     return $return;
   }

  public function viewDetailInvoice($requestData){
    $getUserTimezone = $requestData->time_zone_mysql?? '+00:00';
    $userId = $requestData->user_id;
    $limit = $requestData->data->pageSize;
    $page = $requestData->data->page;
    $sorted = $requestData->data->sorted;
    $invoiceId = $requestData->data->invoice_id;
    $orderBy = '';
    $direction = '';
    $filter = $requestData->data->filtered;
    if(!empty($invoiceId)){
      $sub_query_to_get_invoice_category_name = $this->get_invocie_category_details('name');
      $sub_query_to_get_invoice_category_id = $this->get_invocie_category_details('id');
      $this->db->query("SET @csum := 0");
      $sort_columns = array("si.id","si.item_description", "si.qty", "si.unit_price", 
      "si.invoice_date as date", "si.gst", "si.amount as total",
      '(@csum := @csum + si.amount) as total_price',
      "(" . $sub_query_to_get_invoice_category_name . ") as category_name",
      "(" . $sub_query_to_get_invoice_category_id . ") as category_id",
    );
      if (isset($filter->search) && $filter->search != '') {
        $this->db->group_start();
        for ($i = 0; $i < count($sort_columns); $i++) {
          $column_search = $sort_columns[$i];
          if (strstr($column_search, "as") !== false) {
            $search_column = explode(" as ", $column_search);
            if ($search_column[0] != 'null'){
              $this->db->or_like($search_column[0], $filter->search);
            }
          } else if ($column_search != 'null') {
            $this->db->or_like($column_search, $filter->search);
          }
        }
        $this->db->group_end();
      }
      $queryHavingData = $this->db->get_compiled_select();
      $queryHavingData = explode('WHERE', $queryHavingData);
      $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';

      if (!empty($sorted)) {
        if (!empty($sorted[0]->id)) {
          $orderBy = $sorted[0]->id;
          $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
        }
      } else {
        $orderBy = 'si.id';
        $direction = 'ASC';
      }

      $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $sort_columns)), false);
      $array = array('s.archive' => 0, 's.user_id' => $userId,'s.id'=>$invoiceId);
      $this->db->where($array);
      $this->db->order_by($orderBy, $direction);
      $this->db->limit($limit, ($page * $limit));
      if (!empty($queryHaving)) {
        $this->db->having($queryHaving);
      }
      $this->db->from(TBL_PREFIX.'invoice as s');
      $this->db->join(TBL_PREFIX.'invoice_line_item as si', 'si.invoice_id ='.$invoiceId.' AND si.archive=0', 'right');
      $query =$this->db->get();
 

      $dt_filtered_total = $all_count = $this->db->query('SELECT FOUND_ROWS() as pages;')->row()->pages;
      if ($dt_filtered_total % $limit == 0) {
        $dt_filtered_total = ($dt_filtered_total / $limit);
      } else {
        $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
      }

      $whereArray = array('i.status' => 1, 
      'i.archive' => 0, 'i.user_id' => $userId, 'i.id'=>$invoiceId);
      $arrayField =array("DATE_FORMAT(CONVERT_TZ(i.created,'+00:00', '".$getUserTimezone."'), '%d/%m/%Y') as created",
        'i.due_amount',
        'u.email',
        'i.gst',
        'i.invoice_date',
        'i.invoice_number',
        'i.order_number',
        'i.paid_amount', 
        'i.source_type',
        'i.invoice_file as file',
        '(CASE WHEN i.status=1 THEN "Pending" WHEN i.status=2 THEN "Approve" WHEN i.status=3 THEN "Disapprove" ELSE "" END) AS status',
        'i.total_amount',
        'i.sub_amount',
        'i.vendor_id');
      $this->db->select($arrayField);
      $this->db->where($whereArray);
      $this->db->from(TBL_PREFIX.'invoice as i');
      $this->db->join(TBL_PREFIX.'user as u', 'u.id = i.user_id', 'left');
      $invoiceData =$this->db->get()->row_array();

      if( isset($invoiceData['source_type']) && ($invoiceData['source_type'] != 2) && ($invoiceData['file'] != null) && ($invoiceData['file'] != '')){
        $file_pointer = 'uploads/invoices/'.$userId.'/'.$invoiceData['file'];
        if(file_exists($file_pointer) ){
          $invoiceData['file'] = base_url('user/mediaShow/i_pdf/'.urlencode(base64_encode($userId)).'/'.urlencode(base64_encode($invoiceData['file'])));
        }else{
          
          $invoiceData['file'] = base_url('user/mediaShow/i_pdf/'.urlencode(base64_encode(0)).'/'.urlencode(base64_encode('no_preview.pdf')));
        }
      }else{
        $invoiceData['file'] = base_url('user/mediaShow/i_pdf/'.urlencode(base64_encode(0)).'/'.urlencode(base64_encode('no_preview.pdf')));
      } 
      if(!empty($invoiceData) && (isset($invoiceData['vendor_id']))){
        $Arr = array(
          'v.name',
          'v.gst_number',
          'v.pincode',
          'v.vendor_type',
          'v.source_type',
        );
        $this->db->select($Arr);
        $this->db->where(array('v.id' => $invoiceData['vendor_id'],'v.archive' => 0));
        $que_Persion = $this->db->get(TBL_PREFIX.'vendor AS v');
        $vendor_Details = $que_Persion->row_array();
      }
      $invoiceTransactionData = $query->result();
      return array(
            'all_count' => $all_count,
            'pages' => $dt_filtered_total,
            'invoice_data'=>$invoiceData,
            'vendor_details' => $vendor_Details,
            'invoice_transaction' => $invoiceTransactionData,
          );
    }
  }

  private function get_invocie_category_details($column) {
    $select  = ($column=='name')? 'c.category_name' : 'c.id' ;
    $this->db->select($select);
    $this->db->from(TBL_PREFIX.'category_mapping cm');
    $this->db->where("cm.id=si.mapping_id AND cm.archive=si.archive AND cm.archive=0", NULL, false);
    $this->db->join(TBL_PREFIX.'category as c','c.id = cm.category_id AND c.archive=0', 'right');
    return $this->db->get_compiled_select();
  }


   
  public function addOrUpdateInvoiceData($requestData){
      
    $userId = $requestData->user_id;
    $vendorId = $requestData->data->vendor_id;
    $invoiceDetails = $requestData->data->invoice_details;
    $invoiceTransactionDetails = $requestData->data->transactions_details;
    $invoiceGST = ($invoiceDetails->gst > 0)?$invoiceDetails->gst:0;

    $compareAmt = ($invoiceDetails->total_amount - $invoiceGST);
    
    if($invoiceDetails->total_amount >= $invoiceDetails->paid_amount){
      $tempdue = number_format(($invoiceDetails->total_amount - $invoiceDetails->paid_amount),2,'.','');
      if($invoiceDetails->due_amount != $tempdue){
        return array('status' => false,'msg'=>'Invalid due amount.');
      }
    }else{
      return array('status' => false,'msg'=>'Invalid paid amount.');
    }
    
    $totalsum = 0;
    $txn_totalsumGST = 0;
    
    if(count($invoiceTransactionDetails) > 0){
      foreach($invoiceTransactionDetails as $arr) {
          $totalsum += $arr->total;
          $txn_gst = ($arr->gst >0)?$arr->gst:0;
          $txn_totalsumGST = ($txn_totalsumGST+$txn_gst);
      }
    }else{
      return array('status' => false,'msg'=>'Invoice line item in required.');
    }

    if($invoiceGST != $txn_totalsumGST){
      return array('status' => false,'msg'=>'Invoice GST and trasaction GST should be equal.');
    }

    if($compareAmt != $totalsum){
      return array('status' => false,'msg'=>'Invalid line items amount sum.');
    }
    $created = create_date_store_in_db();
    $miscellCatData = getMiscellaneousCatdetail();
    $miscellCategory_id = ($miscellCatData->id > 0)?$miscellCatData->id:0;
      if(isset($invoiceDetails)&&!empty($invoiceDetails)){
        
        if((isset($requestData->data->invoice_id)) && (!empty($requestData->data->invoice_id))){
           
        /* update Invoice */
          $invoiceData['vendor_id'] = ($vendorId)?$vendorId:0;
          $invoiceData['user_id'] = $userId;
          $invoiceData['invoice_number']=$invoiceDetails->invoice_number;
          $invoiceData['order_number']=$invoiceDetails->order_number;
          $invoiceData['total_amount']=$invoiceDetails->total_amount;
          $invoiceData['paid_amount']=$invoiceDetails->paid_amount;
          $invoiceData['sub_amount']= ($invoiceDetails->total_amount - $invoiceGST);
          $invoiceData['gst']=$invoiceGST;
          $invoiceData['due_amount']=$invoiceDetails->due_amount;
          $invoiceData['updated']=$created;
          $invoiceData['invoice_date'] = date('Y-m-d', strtotime($invoiceDetails->invoice_date));
          $invoice_id =$requestData->data->invoice_id;
          $exitInvoice = $this->BasicModel->getRow(TBL_PREFIX."invoice", ['id','invoice_number'],['invoice_number'=>$invoiceDetails->invoice_number, 'user_id'=> $userId, 'vendor_id' => $vendorId,'archive'=>0 ,'id !='=>$invoice_id]);
         
          if(empty($exitInvoice)){
            $updated = $this->BasicModel->updateRecords(TBL_PREFIX."invoice", $invoiceData, ['id' => $invoice_id]);
            $this->vendor_user_mapping($userId, $vendorId);
            if($updated){
              if(!empty($invoiceTransactionDetails)){
                $this->BasicModel->updateRecords(TBL_PREFIX."invoice_line_item", ['archive'=>1,'updated' =>$created], ['invoice_id' => $invoice_id]);
                  foreach ($invoiceTransactionDetails as $key => $value) {
                    
                    $this->db->select('ili.mapping_id,ili.flag');
                    $this->db->where( array('ili.id' => $value->id, 'ili.read_status' =>2, 'ili.flag' => 0) );
                    $this->db->join(TBL_PREFIX.'invoice AS i','ili.invoice_id=i.id AND i.source_type !=2 AND i.archive=0','inner');
                    $queryflag = $this->db->get(TBL_PREFIX.'invoice_line_item AS ili');
                    $flagData = $queryflag->row_array();


                      $Mapping_id = $this->addMappingDataForInvoice($value->category_id, $vendorId,$userId,$miscellCategory_id);

                      $gst = ($value->gst > 0)?$value->gst:0;
                      $amount = ($gst > 0)?($value->total+$gst):$value->total;
                      
                      $updateData['item_description'] = $value->item_description;
                      $updateData['qty'] = $value->qty;
                      $updateData['unit_price'] = $value->unit_price;
                      $updateData['gst'] = $gst;
                      $updateData['sub_amount'] = $value->total;
                      $updateData['amount'] = $amount;
                      $updateData['invoice_id'] = $invoice_id;
                      $updateData['mapping_id'] =$Mapping_id;
                      $updateData['invoice_date'] = $invoiceData['invoice_date'];
                      $updateData['status'] = 1;
                      $updateData['archive'] = 0;
                      if((isset($flagData)) && !empty($flagData)){
                        if($flagData['mapping_id'] != $Mapping_id){
                          $updateData['original_map_id'] = $flagData['mapping_id'];
                        }
                        $updateData['flag'] = 1;
                      }
                      if((isset($value->id)) && ($value->id > 0)){
                        $updateData['updated'] = $created;
                        $this->BasicModel->updateRecords(TBL_PREFIX."invoice_line_item", $updateData, ['id' => $value->id]);
                      }else{
                    
                          $updateData['created'] = $created;
                          $this->BasicModel->insertRecords(TBL_PREFIX."invoice_line_item", $updateData, FALSE);
                      }
                  }
              }
              $this->loges->setActivityType('edit_invoice');
              $this->loges->setDescription(json_encode($requestData));
              $this->loges->setTableID($invoice_id);
              $this->loges->setCreatedBy($userId);
              $this->loges->setCreatedType(3);
              $this->loges->createLog();
              return array('status' => true,'msg'=>'Invoice successfully updated.');
            }else{
              return array('status' => false,'msg'=>'process failed.');
            }
          }else{
            return array('status' => false,'msg'=>'Invoice number already exist for this user.');
          }
        }else{
           
          /* Add new Invoice */
          $invoiceDetails->vendor_id = ($vendorId)?$vendorId:0;
          $invoiceDetails->user_id = $userId;
          $invoiceDetails->status = 1;
          $invoiceDetails->created=$created;
          $invoiceDetails->archive =0;
          $invoiceDetails->sub_amount = $invoiceDetails->total_amount - $invoiceDetails->gst;
          $invoiceDetails->gst = $invoiceGST;
          $invoiceDetails->source_type =2;
          $invoiceDetails->invoice_date = date('Y-m-d', strtotime($invoiceDetails->invoice_date));

          $exitInvoice = $this->BasicModel->getRow(TBL_PREFIX."invoice", ['id','invoice_number'],['invoice_number'=>$invoiceDetails->invoice_number, 'user_id' =>$userId, 'vendor_id'=>$vendorId,'archive'=>0]);
          if(empty($exitInvoice)){
            $invoice_id = $this->BasicModel->insertRecords(TBL_PREFIX."invoice", $invoiceDetails, FALSE);
            if(isset($invoiceTransactionDetails)&&!empty($invoiceTransactionDetails) && !empty($vendorId)){
                foreach ($invoiceTransactionDetails as $trans) {
                    /* check Category Id */
                  $mappedId = $this->addMappingDataForInvoice($trans->category_id, $vendorId, $userId,$miscellCategory_id);
                  $gst = ($trans->gst >0)?$trans->gst:0;
                  $amount = $trans->total + $gst;
                  $updateData[] = array(
                    'item_description' => $trans->item_description,
                    'qty' => $trans->qty,
                    'unit_price' =>  $trans->unit_price,
                    'sub_amount' =>$trans->total,
                    'gst' =>$gst,
                    'amount' => $amount,
                    'invoice_id' => $invoice_id,
                    'mapping_id' =>$mappedId,
                    'invoice_date' => $invoiceDetails->invoice_date,
                    'status' => 1,
                    'archive' => 0,
                    'created' => $created
                  );
                }
                if(!empty($invoice_id) &&  !empty($updateData)){
                  $this->vendor_user_mapping($userId, $vendorId);
                  $insertTransaction=  $this->BasicModel->insertRecords(TBL_PREFIX."invoice_line_item", $updateData, TRUE);
                  if($insertTransaction){
                    $this->loges->setActivityType('add_new_manual_invoice');
                    $this->loges->setDescription(json_encode($requestData));
                    $this->loges->setTableID($invoice_id);
                    $this->loges->setCreatedBy($userId);
                    $this->loges->setCreatedType(3);
                    $this->loges->createLog();
                     return array('status' => true,'msg'=>'Invoice successfully added.');
                  }
                }
            }
          }else{
            return array('status' => false,'msg'=>'Invoice number already exist for this user.');
          }
        }
      }else{
        return array('status' => false,'msg'=>'no data found.');
      }

  }

  function addMappingDataForInvoice($categoryId, $vendor_id=0,$user_id=0,$miscellCategoryid=0){
    $created = create_date_store_in_db();
    
    $whereVendor = array(
      'vendor_id' => $vendor_id,
      'category_id' => $categoryId,
      'archive' => 0
    );

    $mapDataVendor =  $this->BasicModel->getRecordWhere(TBL_PREFIX.'vendor_mapping','id',$whereVendor);
    
    if( (!empty($miscellCategoryid)) && ($miscellCategoryid >0) ){
      $status = ($miscellCategoryid == $categoryId)?2:1;
    }else{
      $status=1;
    }

    if(empty($mapDataVendor)){
      $mapVendor = array(
        'vendor_id' => $vendor_id,
        'category_id' => $categoryId,
        'status' => $status,
        'source_type' => 2,
        'created' => $created,
        'archive' => 0,
      );
      
      $this->BasicModel->insertRecords(TBL_PREFIX."vendor_mapping", $mapVendor, FALSE);

      $vendorData = $this->BasicModel->getRecordWhere(TBL_PREFIX.'vendor', 'name', ['id' => $vendor_id, 'archive'=>0]);

      $catData = $this->BasicModel->getRecordWhere(TBL_PREFIX.'category', 'category_name', ['id' => $categoryId, 'archive'=>0]);
      
      
      $this->notification->setAlertTitle("New category ".$catData->category_name." is added in ".$vendorData->name." vendor, for approval");
      $this->notification->setAlertType(2);
      $this->notification->setUserId(0);
      $this->notification->setDescription("New category ".$catData->category_name." is added in ".$vendorData->name." vendor, for approval.");
      $this->notification->setIsRead(2);
      $this->notification->setNotificationCreated($created);
      $this->notification->setCreatedByType(1);
      $this->notification->setCreatedBy($user_id);
      $this->notification->setNotificationArchive(0);
      $this->notification->SaveUserNotificationAlert();
    } 


    
    $mappingData = array(
      'category_id' => $categoryId,
      'archive'=>0
    );
    // if(!empty($sub_category_Id)){
    //   $mappingData['sub_category_id'] = $sub_category_Id;
    // }else{
    //   $mappingData['sub_category_id'] = 0;
    // }
    $where = $mappingData;
    $mapData =  $this->BasicModel->getRecordWhere(TBL_PREFIX.'category_mapping','id',$where);
    if(!empty($mapData)){
      return (int) $mapData->id;
    }else {
      $this->BasicModel->insertRecords(TBL_PREFIX."category_mapping", $mappingData, FALSE);
      return $this->db->insert_id();
    }
  }

  function vendor_user_mapping($userID, $vendorID){
    $where = array(
      'user_id' => $userID,
      'vendor_id' => $vendorID,
      'archive' => 0
    );
    $mapData =  $this->BasicModel->getRecordWhere(TBL_PREFIX.'vendor_user_mapping','id',$where);
    if(!empty($mapData)){
      return (int) $mapData->id;
    }else {
      $mapData = $where;
      $mapData['created'] = create_date_store_in_db();
      $this->BasicModel->insertRecords(TBL_PREFIX."vendor_user_mapping", $mapData, FALSE);
      return $this->db->insert_id();
    }
  }
  function addVendor($vendorDetails){
      require_once APPPATH . 'classes/person/person.php';
      $objPerson = new PersonClass\Person();
      $objPerson->setFirstName($vendorDetails->vendor_name);
      $objPerson->setPersonTypeIdByKey('vendor');
      return $objPerson->createPerson();
  }

  function getCategorySubCateogryId($cat,$subcat){
    $where = array('category_name'=>$cat);
    $category =  $this->BasicModel->getRecordWhere(TBL_PREFIX.'category','id',$where);
    $data=array();
    $data['category_id']="";
    $data['sub_category_id'] = "";
    if(!empty($category)){
      $data['category_id'] = $category->id;
      $where1 = array('category_name'=>$subcat, 'parent_id'=> $category->id);
      $subcategory =  $this->BasicModel->getRecordWhere(TBL_PREFIX.'category','id',$where1);
        if(!empty($subcategory)){
          $data['sub_category_id'] = $subcategory->id;
        }
    }
    return $data;
  }

  public function deleteInvoiceData($requestData){
    $userId = $requestData->user_id;
    $invoiceId = $requestData->data->invoice_id;
    $created = create_date_store_in_db();
    $setArray =array(
      'archive' => 1,
      'updated' => $created,
      );
    $result = $this->BasicModel->updateRecords(TBL_PREFIX.'invoice', $setArray, array('id'=>$invoiceId,'user_id'=>$userId,'archive'=> 0));
    $this->BasicModel->updateRecords(TBL_PREFIX.'invoice_line_item', $setArray, array('invoice_id'=>$invoiceId,'archive'=> 0));

    $this->loges->setActivityType('delete_invoice');
    $this->loges->setDescription(json_encode($requestData));
    $this->loges->setTableID($invoiceId);
    $this->loges->setCreatedBy($userId);
    $this->loges->setCreatedType(2);
    $this->loges->createLog();
    return $result;
  }

  public function deleteInvoiceTransactionData($requestData){
   /*$userId = $requestData->user_id;*/
   $invoiceId = $requestData->data->invoice_id;
   $transactionId = $requestData->data->item_id;
   $this->BasicModel->deleteRecords(TBL_PREFIX.'invoice_line_item',array('invoice_id'=>$invoiceId,'id'=>$transactionId));
   return true;
 }

  /*----------------------------------------------------
  | get_DDVendorNameList()
  ----------------------------------------------------*/
  public function get_DDVendorNameList($requestData){
    $user_id = $requestData->user_id;


    $this->db->select("*");
    $this->db->where('sub_i.user_id', $user_id);
    $this->db->where('sub_i.vendor_id=v.id AND v.archive=0');
    $sql1 = $this->db->get_compiled_select(TBL_PREFIX.'invoice AS sub_i');
    $invoiceSql = 'EXISTS ('.$sql1.')';

    
    $this->db->select("*");
    $this->db->where('sub_vum.user_id',$user_id);
    $this->db->where('sub_vum.vendor_id = v.id AND sub_vum.archive=0');
    $sql2 = $this->db->get_compiled_select(TBL_PREFIX.'vendor_user_mapping AS sub_vum');
    $vendorSql = 'EXISTS ('.$sql2.')';

    $arr = array(
      'v.id as value',
      'CONCAT_WS(" ", v.name,v.gst_number, v.pincode) as label',
      'v.gst_number as gst',
      'v.pincode as pincode',
      '(CASE WHEN v.vendor_type=1 THEN "Global" WHEN v.vendor_type=2 THEN "Local" ELSE "N/A" END) AS v_type'
    );

    $this->db->select($arr);
    $this->db->where('v.archive',0);
    
      $this->db->group_start();
      
      
      $this->db->group_start();
      $this->db->where(array('v.status' => 2, 'v.vendor_type' => 1));
      $this->db->group_end();
      
      $this->db->or_group_start();
      $this->db->where(array('v.vendor_type'=> 2, 'v.status' => 2));
      $this->db->group_end();
      
      $this->db->or_group_start();

        $this->db->group_start();
        $this->db->or_where($vendorSql);
        $this->db->group_end();

        $this->db->or_group_start();
          
          $this->db->or_where($invoiceSql);
          
        $this->db->group_end();

      $this->db->group_end();
      $this->db->group_end();
    
    $query = $this->db->get(TBL_PREFIX.'vendor as v');

    if($query->num_rows() > 0){
      return $query->result_array();
    }
    else
    {
      return FALSE;
    }
  }

  /*----------------------------------------------------
  | add_new_vendor_model($arrayField)
  ----------------------------------------------------*/
  public function add_new_vendor_model($arrayField, $user_id){
    $created = create_date_store_in_db();
    $arr = array(
      'v.name' => $arrayField['vendorname'],
      'v.gst_number' => $arrayField['gst'],
      'v.pincode' => $arrayField['pincode'],
      'v.vendor_type' =>2,
      'v.source_type' => 2,
      'v.archive' => 0
    );
    $this->db->select('v.id,v.status');
    $this->db->where($arr);
    $query = $this->db->get(TBL_PREFIX.'vendor AS v');
    
    if($query->num_rows() > 0)
    {
      $vendor = $query->row_array();
      $vendor_id = $vendor['id'];
    }else{
      $vendor_arr =array(
        'name' => $arrayField['vendorname'],
        'status' => 1,
        'created_by ' => $user_id,
        'created' => $created,
        'archive' => 0,
        'gst_number' => $arrayField['gst'],
        'pincode' => $arrayField['pincode'],
        'vendor_type' => 2,
        'source_type' => 2
      );
      $vendor_id = $this->CommonModel->insertData($vendor_arr,TBL_PREFIX.'vendor');
    }

      if($vendor_id > 0){
        $mappingExist =  $this->BasicModel->getRecordWhere(TBL_PREFIX.'vendor_user_mapping','id',['user_id'=>$user_id,'vendor_id'=>$vendor_id,'archive'=>0]);
        $this->vendor_user_mapping($user_id, $vendor_id);
        if(isset($mappingExist->id)){
          return array('status' => false, 'message' => 'Vendor already registered with these details.');
        }
        /* Notification set */
        $this->notification->setAlertTitle("New vendor ".$arrayField['vendorname']." is added, for approval");
        $this->notification->setAlertType(2);
        $this->notification->setUserId(0);
        $this->notification->setDescription("New vendor ".$arrayField['vendorname']." is added, for approval.");
        $this->notification->setIsRead(2);
        $this->notification->setNotificationCreated($created);
        $this->notification->setCreatedByType(1);
        $this->notification->setCreatedBy($user_id);
        $this->notification->setNotificationArchive(0);
        $this->notification->SaveUserNotificationAlert();

        return array('status' => true, 'data' => $vendor_id, 'message' => 'Vendor successfully added.');
      }else{
        return array('status' => false, 'message' => 'vendor process failed.');
      }
  }


  /*----------------------------------------------------
  |   getInvoiceDetailsbyId($requestData)
  ----------------------------------------------------*/
  public function getInvoiceDetailsbyId($requestData)
  {
    $response= array();
    $invoiceId = $requestData->data->invoice_id;
    $arrayParam = array(
      'i.id',
      'i.invoice_number',
      'i.invoice_date',
      'i.total_amount',
      'i.paid_amount',
      'i.due_amount',
      'i.order_number',
      'i.vendor_id',
    );
    $this->db->select($arrayParam);
    $this->db->where(array('i.id' => $invoiceId,'i.status' => 1,'i.archive' => 0));
    $invoiceQuery = $this->db->get(TBL_PREFIX.'invoice AS i');
    if($invoiceQuery->num_rows() > 0){
      $response["invoice"] = $invoiceQuery->row_array();
      $transection = $this->getTransectionsData($invoiceId);
      $response["transactions"] =  !empty($transection)?$transection:array();
      return array('status' => true, 'data' => $response);
    }else{
      return array('status' => false,'message' => 'data not found.');
    }
  }
  

  /*----------------------------------------------------
  |   getTransectionsData($invoiceId)
  ----------------------------------------------------*/
  public function getTransectionsData($invoiceId){
    $this->db->query("SET @csum := 0");
    $array=array(
    'il.id',
    'il.item_description as item',
    'cm.category_id as category',
    'il.qty',
    'il.unit_price',
    'il.amount as total',
    /* '(il.qty * il.unit_price) as total_price',*/
    '(@csum := @csum + il.amount) as total_price'
    );
    $this->db->select($array);
    $this->db->where(array('il.invoice_id' => $invoiceId,'il.status' => 1,'il.archive' => 0));
    $this->db->join(TBL_PREFIX.'category_mapping AS cm','il.mapping_id = cm.id AND cm.status=1 AND cm.archive=0', 'INNER');
    $this->db->order_by('il.id', 'ASC');
    $query = $this->db->get(TBL_PREFIX.'invoice_line_item AS il');
    /* echo $this->db->last_query(); die;*/
    if($query->num_rows() > 0)
    {
      return $query->result_array();
    }else{
      return false;
    }
  }

  public function invoice_ai_review_mapping_call($extraPrams=[]){
    $fromDate = $extraPrams['from_date'] ?? create_date_store_in_db();
    $toDate = $extraPrams['to_date'] ?? create_date_store_in_db();
    $this->db->select(['ili.id']);
    $this->db->from(TBL_PREFIX.'invoice_line_item ili');
    $this->db->where(['ili.archive'=>0,'ili.flag'=>1,'ai_sent'=>0]);
    $this->db->where("ili.updated between '".$fromDate."' and '".$toDate."'",null,false);
    $query =$this->db->get();
    return $query->num_rows()>0 ?  $query->result_array():[];
  }
}
