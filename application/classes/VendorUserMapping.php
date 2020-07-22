<?php

class VendorUserMapping {
    
    protected $CI;
    private $userId;
    private $vendorId;
 
    public function __construct() {
        /* Assign the CodeIgniter super-object */
        $this->CI = & get_instance();
       
    }
 
 
    function setUserId($userId) {  $this->userId = $userId;}
    function getUserId() {  return $this->userId; }

    function setVendorId($vendorId) { $this->vendorId = $vendorId; }
    function getVendorId() { return $this->vendorId; }

    function setSourceType($sourceType) { $this->sourceType = $sourceType; }
    function getSourceType() { return $this->sourceType; }


    function addVendorUserMappingByPythonAdd() { 
        $tableName1 = TBL_PREFIX.'vendor_user_mapping';
        $tableName2 = TBL_PREFIX.'vendor_mapping';
        $tableName3 = TBL_PREFIX.'notification';
        $results =  $this->getVendorMappedIds();  
        if(!empty($results)){
            $notifyMsgData = array();
            $notifyData =  array();
            foreach($results as $result) {
                $this->setVendorId($result['vendor_id']);
                $data[] = array(
                    'user_id' => $this->userId,                 //tbl_fm_logs_activity_type
                    'vendor_id' => $this->vendorId,
                    'archive' => 0,
                    'created' => create_date_store_in_db(),
                );
                
                if($result['isNew']==1){
                    $notifyData[] = array(
                        'id' => $result['id'],
                        'notify' => 1,
                        'updated' => create_date_store_in_db(),
                     ); 
                  $notifyMsgData[] =  $this->sendVendorMappingNotification($result['vendor_id'],$result['category_id'],$this->userId);
                }
            }
            if(!empty($data)){
                $this->CI->CommonModel->insertBatch($data,$tableName1);
            }
            if(!empty($notifyData)){
                $this->CI->CommonModel->updateBatch($notifyData,$tableName2,'id');
            }
            if(!empty($notifyMsgData)){
                $this->CI->CommonModel->insertBatch($notifyMsgData,$tableName3);
            }
        } 
    }
 

    function sendVendorMappingNotification($vid,$cid,$userId){
        $created = create_date_store_in_db();
        $vendorDetails =  $this->CI->BasicModel->getRecordWhere(TBL_PREFIX.'vendor', 'name', ['id' => $vid, 'archive'=>0]);
        $catDetails = $this->CI->BasicModel->getRecordWhere(TBL_PREFIX.'category', 'category_name', ['id' => $cid, 'archive'=>0]);
        $title_desc = "New category ".$catDetails->category_name." is added in ".$vendorDetails->name." vendor,  from python for approval";
          $this->CI->notification->setAlertTitle($title_desc);
          $this->CI->notification->setAlertType(2);
          $this->CI->notification->setUserId(0);
          $this->CI->notification->setDescription($title_desc);
          $this->CI->notification->setIsRead(2);
          $this->CI->notification->setNotificationCreated($created);
          $this->CI->notification->setCreatedByType(1);
          $this->CI->notification->setCreatedBy($userId);
          $this->CI->notification->setNotificationArchive(0);
         return $this->CI->notification->SaveUserNotificationAlert(true);
      }
    
    private function get_vm_notify_forpython_case_query(){
        return "CASE WHEN vm.source_type=1 AND vm.notify=0 THEN '1' ELSE '0' END";
    }

    function getVendorMappedIds(){
        $table1 = TBL_PREFIX.'statement as s';
        $table2 = TBL_PREFIX.'statement_line_item as sli';
        $table3 = TBL_PREFIX.'vendor_mapping as vm';
        $table4 = TBL_PREFIX.'vendor as v';
      
         $isNotify =  $this->get_vm_notify_forpython_case_query();
        
        $columns = array(
        'vm.vendor_id', 
        'vm.category_id', 
        'vm.id' ,
        's.user_id',
        "$isNotify as isNew"
       );

        $groupcolumns = array(
            'vm.vendor_id', 
            'vm.category_id', 
            'vm.id' ,
            's.user_id',  
        );

        $whereArray = array(
          's.archive' =>0,
          's.user_id' => $this->userId
        );

        $this->CI->db->select($columns);
        $this->CI->db->from($table1);
        $this->CI->db->join($table2,'s.id=sli.statement_id AND sli.read_status=2 and sli.archive=s.archive');
        $this->CI->db->join($table3,'vm.id=sli.mapped_id and sli.archive=vm.archive');
        $this->CI->db->join($table4,'v.id=vm.vendor_id and v.source_type=1 and v.read_status=1');
        $this->CI->db->where("NOT EXISTS(SELECT * FROM tbl_fm_vendor_user_mapping as sub_vm where sub_vm.vendor_id=vm.vendor_id and sub_vm.user_id=s.user_id)");
        $this->CI->db->where($whereArray);
        $this->CI->db->group_by($groupcolumns);
        $this->CI->db->order_by('s.user_id','desc');
        $query = $this->CI->db->get();
        return $result = $query->result_array();
    }
 
}