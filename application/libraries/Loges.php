<?php

class Loges {
    
    protected $CI;
    /*private $module;*/
    private $activityType;
    private $title='';
    private $description;
    private $specificTitle = '';
    private $createdBy;
    private $createdType;
    private $tableId;

    public function __construct() {
        /* Assign the CodeIgniter super-object */
        $this->CI = & get_instance();
       
    }

    /*function setAdminID($adminId) { $this->adminId = $adminId; }
    function getAdminID() { return $this->adminId;}

    function setLicenseeID($licenseeId) { $this->licenseeId = $licenseeId; }
    function getLicenseeID() { return $this->licenseeId;}

    function setModule($module) {  $this->module = $module;}
    function getModule() {  return $this->module; }   */

    function setActivityType($activityType) {  $this->activityType = $activityType;}
    function getActivityType() {  return $this->activityType; }

    function setTitle($title) { $this->title = $title; }
    function getTitle() { return $this->title; }

    function setDescription($description) { $this->description = $description; }
    function getDescription() { return $this->description; }

    function setSpecificTitle($specificTitle) { $this->specificTitle = $specificTitle; }
    function getSpecificTitle() { return $this->specificTitle; }

    function setCreatedBy($createdBy) { $this->createdBy = $createdBy; }
    function getCreatedBy() { return $this->createdBy;}

    function setCreatedType($createdType) { $this->createdType = $createdType; }
    function getCreatedType() { return $this->createdType;}
    
    function setTableID($tableId) { $this->tableId = $tableId; }
    function getTableID() { return $this->tableId;}


    function createLog() {  
        /*$this->setModuleDetails();*/
        $this->setLogsActivityType();        
       
        /* $this->CI->load->library('UserName'); */
        
        
        
        $data = array(
            'activity_id' => $this->activityType,                 //tbl_fm_logs_activity_type
            'title' => ($this->activityType > 0 ?'':$this->title), 
            'specific_title' => $this->specificTitle,           //for speccific title
            'description' => $this->description,                //use for json data
            'created_by' => $this->createdBy,
            'created_for' => $this->tableId,
            'created_type' => $this->createdType,               //0-not known 1-user,2-vendor,3-invoice,4-statement
            'archive' => 0,
            'created' => create_date_store_in_db(),
        );
        
        $this->CI->db->insert(TBL_PREFIX.'logs', $data);
    }

    function setLogsActivityType() {
        $this->CI->db->select(['id']);
        $this->CI->db->from(TBL_PREFIX.'logs_activity_type as la');
        $this->CI->db->where('la.activity_key', $this->activityType);
        $query = $this->CI->db->get();

        $res = $query->row();
        if (!empty($res)) {            
                $this->setActivityType($res->id);
            } else {
                $this->setActivityType(0);
            }
        }    
}