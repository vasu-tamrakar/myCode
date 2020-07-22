<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Notification {
    
    protected $CI;
    private $alert_title;
    private $alert_type;
    private $user_id;
    private $description;
    private $is_read=2;
    private $created;
    private $updated;
    private $created_by_type;
    private $created_by;
    private $archive;
    
    public function __construct() {
        // Assign the CodeIgniter super-object
        $this->CI = & get_instance();
       
    }
   
    function setAlertTitle($alert_title){$this->alert_title = $alert_title;}
    function getAlertTitle(){return $this->alert_title;}

    function setAlertType($alert_type){$this->alert_type = $alert_type;}
    function getAlertType(){return $this->alert_type;}

    function setUserId($user_id){$this->user_id = $user_id;}
    function getUserId(){return $this->user_id;}

    function setIsRead($is_read){$this->is_read = $is_read;}
    function getIsRead(){return $this->is_read;}

    function setDescription($description) { $this->description = $description; }
    function getDescription() { return $this->description; }

    function setNotificationUpdate($updated){$this->updated = $updated;}
    function getNotificationUpdate(){return $this->updated;}

    function setNotificationCreated($created){$this->created = $created;}
    function getNotificationCreated(){return $this->created;}

    function setCreatedByType($created_by_type) { $this->created_by_type = $created_by_type; }
    function getCreatedByType() { return $this->created_by_type;}

    function setCreatedBy($created_by) { $this->created_by = $created_by; }
    function getCreatedBy() { return $this->created_by;}

    function setNotificationArchive($archive) { $this->archive = $archive; }
    function getNotificationArchive() { return $this->archive;}

    
    

    function SaveUserNotificationAlert($tempReturn=false){
        $data = array(
            'alert_title' => $this->alert_title,
            'alert_type' => $this->alert_type,
            'user_id' => $this->user_id,
            'description' => $this->description,
            'is_read' => $this->is_read,
            'created_by_type' => $this->created_by_type,
            'created_by' => $this->created_by,
            'archive' => $this->archive,
            'created' => isset($this->created)? $this->created:create_date_store_in_db(),
        );    
        if($tempReturn){
            return $data;
        }else{
            $this->CI->db->insert(TBL_PREFIX.'notification', $data);
        }        
    }

  
}