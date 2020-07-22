<?php defined('BASEPATH') || exit('No direct script access allowed');

require_once APPPATH . 'classes/StatementCheck.php';

class AdminUserModel extends CI_Model
{
  private function get_user_status_case_query(){
      return "CASE WHEN u.status=0 THEN 'Pending'  WHEN u.status=1 THEN 'Active' WHEN u.status=2 THEN 'Inactive' ELSE 'N/A' END";
  }

   private function get_user_email_verified_query(){
      return "CASE WHEN u.email_verify=0 THEN 'Verified'  WHEN u.email_verify=1 THEN 'Not Verified' ELSE 'N/A' END";
  }

  public function listUsers($request_body){
    $limit = $request_body->data->pageSize;
    $page = $request_body->data->page;
    $sorted = $request_body->data->sorted;
    $filter = $request_body->data->filtered;

    $getUserTimezone = $request_body->time_zone_mysql?? '+00:00';

    $userStatus=$this->get_user_status_case_query();
    $userEmailVerify=$this->get_user_email_verified_query();

    $sort_columns = array(
      "id", 
      "status",  
      "firstname",
      "lastname",
      "email_verify",
      "created",
      "email"
    );

    if (isset($filter->search) && $filter->search != '') {
      $this->db->group_start();
      for ($i = 0; $i < count($sort_columns); $i++) {
        $column_search = $sort_columns[$i];
        if (strstr($column_search, "as") !== false) {
          $search_column = explode(" as ", $column_search);
          if($search_column[0] != 'null'){
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

    if (isset($filter->filterBy) && $filter->filterBy != '-1') {

      if($filter->filterBy == '3'){
         $this->db->where('u.status', 0);
       }else{
         $this->db->where('u.status', $filter->filterBy);
       }
    
    } 
    
    $sortorder = getSortBy(
			$sorted, 
			['created'=>"p.created"], 
			['orderBy'=> 'id', 'direction'=>'DESC']
		);

    $select_columns = array(
      "p.id as id",
      "u.id as uid", 
      "$userStatus as status",  
      "p.firstname","p.lastname",
      " $userEmailVerify as email_verify",
      "DATE_FORMAT(CONVERT_TZ(p.created,'+00:00', '".$getUserTimezone."'), '%d/%m/%Y') as created",
      "u.email"
    );

    $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_columns)), false);
    $array = array('p.archive' => 0, 'p.type' => 1); 
    $this->db->where($array);
    $this->db->join(TBL_PREFIX.'user u', "u.archive = 0 AND u.person_id=p.id");
    $this->db->order_by($sortorder['orderBy'], $sortorder['direction']);
    $this->db->limit($limit, ($page * $limit));
    if (!empty($queryHaving)) {
            $this->db->having($queryHaving);
        }
    $query =$this->db->get(TBL_PREFIX.'person p');

    $dt_filtered_total = $all_count = $this->db->query('SELECT FOUND_ROWS() as pages;')->row()->pages;
    if ($dt_filtered_total % $limit == 0) {
      $dt_filtered_total = ($dt_filtered_total / $limit);
    } else {
      $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
    }
    $result = $query->result();

    return array('pages' => $dt_filtered_total, 'data' => $result, 'all_count' => $all_count);
  }

  

  function singleUserDetails($request_body){
    $personId = $request_body->data->person_id;
    require_once APPPATH . 'classes/person/person.php';
    $objPerson = new PersonClass\Person();
    $objPerson->setPersonId($personId);
    return $objPerson->getPersonDetails($request_body);
  }

  public function updateUser($request_data){
    $request_all_data = $request_data->data;
    $personId = $request_all_data->person_id;
   
    require_once APPPATH . 'classes/person/person.php';
    $objPerson = new PersonClass\Person();
    
    $objPerson->setPersonId($personId);
    if(!empty($request_all_data->phone)){  
      $objPerson->setPersonPhone([(object)['phone'=>strip_tags($request_all_data->phone)]]);
      $objPerson->updatePhone();
    }
    if(!empty($request_all_data->email)){
      $objPerson->setPersonEmail([(object)['email'=>strip_tags($request_all_data->email)]]);
      $objPerson->updateEmail();
    }
    if(!empty($request_all_data->address)){
      $objPerson->setPersonStreet($request_all_data->address);
      $objPerson->setPersonPostal($request_all_data->postal);
      $objPerson->setPersonState($request_all_data->state);
      $objPerson->setPersonCity($request_all_data->suburb);
      $objPerson->setPersonCountry($request_all_data->country);
      $objPerson->updateAddress();
    }
    if(!empty($request_all_data->firstname)||!empty($request_all_data->lastname)){
      $firstName = strip_tags($request_all_data->firstname??'');
      $lastName = strip_tags($request_all_data->lastname??'');
      $objPerson->setFirstName($firstName);
      $objPerson->setLastName($lastName);
      $objPerson->updatePerson();
    }


   $TimezoneDetails = getPrimaryTimezoneFromCountryId($request_all_data->country);
   $this->CommonModel->UpdateData(
      ['person_id'=>$personId, 'archive' => 0],
      [
        'email' => strip_tags($request_all_data->email),
        'user_timezone'=>isset($TimezoneDetails)? $TimezoneDetails->id : 0
      ],
      TBL_PREFIX.'user'
   );


    return true;
 }


 public function addUser($requestData){
    $request_all_data = $requestData->data;
    require_once APPPATH . 'classes/person/person.php';
    $objPerson = new PersonClass\Person();
    $firstName = strip_tags($request_all_data->firstname??'');
    $lastName = strip_tags($request_all_data->lastname??'');
     
    $objPerson->setFirstName($firstName);
    $objPerson->setLastName($lastName);
    $objPerson->setPersonTypeIdByKey('user');
    $personId = $objPerson->createPerson();
    $objPerson->setPersonEmail([(object)['email'=>strip_tags($request_all_data->email)]]);
    $objPerson->insertEmail('email');

    $objPerson->setPersonStreet($request_all_data->address);
    $objPerson->setPersonPostal($request_all_data->postal);
    $objPerson->setPersonState($request_all_data->state);
    $objPerson->setPersonCity($request_all_data->suburb);
    $objPerson->setPersonCountry($request_all_data->country);
    $objPerson->insertAddress();

    $objPerson->setPersonPhone([(object)['phone'=>strip_tags($request_all_data->phone)]]);
    $objPerson->insertPhone('phone');
  
    $password = '';

    $TimezoneDetails = getPrimaryTimezoneFromCountryId($request_all_data->country);
    
    $token = array(
      'datetime' => strtotime(date(DATE_TIME_FORMAT)),
      'user_email'=> strip_tags($request_all_data->email),
      'setnewpass' => 1
   );
 
    $token = encrypt_decrypt('encrypt', json_encode($token));

    $data = array(
			'person_id'=>$personId,
			'email' => strip_tags($request_all_data->email),
			'password' => $password,
      'created' => create_date_store_in_db(),
      'token_status' => 1,
      'token' => $token,
      'email_verify' => 0,
      'user_timezone'=>isset($TimezoneDetails)? $TimezoneDetails->id : 0
		);
    $insertData = $this->CommonModel->insertData($data, TBL_PREFIX.'user');


    $url  = FRONT_URL.'user/set_password/'.urlencode(base64_encode($token));

    $mail_data = [
      'email' => strip_tags($request_all_data->email),
      'username' => $firstName,
      'url' => $url
    ];

    user_created_mail_to_user($mail_data);

    return true;
 }

public function deleteUser($responseData){
  $personId = $responseData->data->person_id;
  require_once APPPATH . 'classes/person/person.php';
  $objPerson = new PersonClass\Person();
  $objPerson->setPersonId($personId);
  $objPerson->deletePerson();
  return true;
}

public function single_user_activate($request_data){
  $this->load->library('Notification');
  $updateUser = $this->CommonModel->UpdateData(
    ['id'=>$request_data->data->user_id,  'archive'=>0],
    [
      'status'=> ($request_data->data->key == 1)? 1 : 2, 
      'email_verify' => ($request_data->data->key == 1)? 0 : 1
    ], 
    TBL_PREFIX.'user'
  );
  if($updateUser > 0){
      $this->notification->setAlertTitle("Your account is Activated");
      $this->notification->setAlertType(1);
      $this->notification->setUserId($request_data->data->user_id);
      $this->notification->setDescription("Your account is Activated.");
      $this->notification->setIsRead(2);
      $this->notification->setNotificationCreated(create_date_store_in_db());
      $this->notification->setCreatedByType(2);
      $this->notification->setCreatedBy($request_data->user_id);
      $this->notification->setNotificationArchive(0);
      $this->notification->SaveUserNotificationAlert();
    return true;
  }
  return false;
}

 
}