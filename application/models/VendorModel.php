<?php

defined('BASEPATH') or exit('No direct script access allowed');

class VendorModel extends CI_Model
{
	public function __construct(){
		parent::__construct();
		$this->load->library('Notification');
	}

	//User Module Model for Vendors 

	public function user_vendor_list($request_body){

		$vendorType=$this->get_vendor_type_case_query();
		$vendorStatus=$this->get_vendor_status_case_query();
		$vendorNameSubQuery=$this->get_vendor_person_name_subquery();
		$login_user_id = $request_body->user_id;
		$limit = $request_body->data->pageSize;
		$page = $request_body->data->page;
		$sorted = $request_body->data->sorted;
		$orderBy = '';
		$direction = '';
		$filter = $request_body->data->filtered;

		$this->db->select('COUNT(vm.id)');
		$this->db->where("vm.vendor_id=v.id AND vm.status=1 AND vm.archive=0");
    	$this->db->from(TBL_PREFIX.'vendor_mapping as vm');
    	$pendingCategoriesSubQuery = $this->db->get_compiled_select();

	
		$sort_columns = array("vendor_id","gst_number", "pincode","vendor_status","vendor_type_name","vendor_name");
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
		$queryHavingData = $this->db->get_compiled_select();
        $queryHavingData = explode('WHERE', $queryHavingData);
		$queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';

		if(isset($filter->filterByType) && $filter->filterByType != '0') {  	
			$this->db->where(['vendor_type'=>$filter->filterByType]);                        
        }
        if(isset($filter->filterByStatus) && $filter->filterByStatus != '0') {  	
			$this->db->where(['status'=>$filter->filterByStatus]);                        
        }
		
		if (!empty($sorted)) {
		  if (!empty($sorted[0]->id)) {
			$orderBy = $sorted[0]->id;
			$direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
		  }
		} else {
		  $orderBy ='v.id';
		  $direction = 'DESC';
		}
   
		$select_column = [
			"v.id as vendor_id",
			"v.name as vendor_name", 
			"v.gst_number",
			"v.pincode",
			$vendorStatus." as vendor_status",
			$vendorType." as vendor_type_name",
			"(".$pendingCategoriesSubQuery.") as pending_categories",
		];
		$this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
		$array = array('v.archive' => 0,  'um.user_id'=> $login_user_id);
		$this->db->where($array);
		$this->db->join(TBL_PREFIX.'vendor_user_mapping as um', 'um.vendor_id = v.id AND v.archive=um.archive', 'inner');
		$this->db->order_by($orderBy, $direction);
		$this->db->limit($limit, ($page * $limit));
		/* it is useed for subquery filter */
        if (!empty($queryHaving)) {
            $this->db->having($queryHaving);
        }
		$query =$this->db->get(TBL_PREFIX.'vendor v');
   
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

	public function vendorExistCheck($request_data,$id=null){
		$vendorId = (!empty($id))?$id:'';
		$columnArr = ['id','name','status'];
		$tableName = TBL_PREFIX.'vendor';
		$whereArr = array(
		'name'=>$request_data->name,
		'gst_number' => $request_data->gst,
		'pincode' => $request_data->pincode,
		'vendor_type' =>2,
		'archive' => 0	
		);
		if($vendorId > 0){
			$whereArr['id !='] = $vendorId;
		}
	 return $this->CommonModel->getDataWhere($columnArr, $whereArr, $tableName, ['result_array'=>3]);
	}

	public function vendor_user_mapping($userID, $vendorID){
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

	public function addNotificationAlert($vendorName,$userId,$key='New'){
		$title_or_desc = ($key=='New')? "New vendor is added “".$vendorName."” for approval.":"Update the vendor “".$vendorName."” ";
		$this->notification->setAlertTitle($title_or_desc);
		$this->notification->setAlertType(2);
		$this->notification->setUserId(0);
		$this->notification->setDescription($title_or_desc);
		$this->notification->setIsRead(2);
		$this->notification->setNotificationCreated(create_date_store_in_db());
		$this->notification->setCreatedByType(1);
		$this->notification->setCreatedBy($userId);
		$this->notification->setNotificationArchive(0);
		$this->notification->SaveUserNotificationAlert();
	}

	public function user_add_vendor($request_body){
		$tableName = TBL_PREFIX.'vendor';
		$request_data = $request_body->data; 
		$userId = $request_body->user_id;
		$checkExistVendor = $this->vendorExistCheck($request_data);
		
		if((!empty($checkExistVendor)) && (isset($checkExistVendor[0]->id))){
			$vendor_id = $checkExistVendor[0]->id;
		}else{
			$vendorData = array(
				'name' => $request_data->name,
				'status' => 1,
				'created_by' => $userId,
				'created' => create_date_store_in_db(),
				'archive' => 0,
				'gst_number' =>$request_data->gst,
				'pincode' => $request_data->pincode,
				'vendor_type' => 2,
				'source_type' => 2
			);
			$vendor_id = $this->CommonModel->insertData($vendorData,TBL_PREFIX.'vendor');
		}
		if($vendor_id > 0){
			$mappingExist =  $this->BasicModel->getRecordWhere(TBL_PREFIX.'vendor_user_mapping','id',['user_id'=>$userId,'vendor_id'=>$vendor_id,'archive'=>0]);
			$this->vendor_user_mapping($userId, $vendor_id);
			if(isset($mappingExist->id)){
				return $response = array('status' => false, 'message' => 'Vendor already registered with these details.');
			}
			$this->addNotificationAlert($request_data->name,$userId,'New');
			return $response = array('status' => true, 'data' => $vendor_id, 'message' => 'Vendor successfully added.');
		}
	}

	public function user_update_vendor($request_body){
		$request_data = $request_body->data;
		if(isset($request_data->id) && !empty($request_data->id)){
			$vendorId = $request_data->id;
			$tableName = TBL_PREFIX.'vendor';
		    $userId = $request_body->user_id;
			$checkExistVendor = $this->vendorExistCheck($request_data,$vendorId);
			if(!empty($checkExistVendor)){
				return $response = array('status' => false, 'message' => 'Vendor already exist.');	
			}  else {
				$where = array('id'=>$vendorId);
				$vendorData = array(
					'name' => $request_data->name,
					'gst_number' =>$request_data->gst,
					'pincode' => $request_data->pincode,
					'updated' =>  create_date_store_in_db()
				);
				$vendor_id = $this->CommonModel->UpdateData($where,$vendorData,TBL_PREFIX.'vendor');
				if($vendor_id > 0){
					$this->addNotificationAlert($request_data->name,$userId,'Update');
					return $response = array('status' => true, 'data' => $vendor_id, 'message' => 'Vendor successfully updated.');
				 }
			}
		}
	}

	public function view_user_single_vendor($request_data){
		$vendorId = $request_data->vendor_id;

		$vendorType=$this->get_vendor_type_case_query();
		$vendorStatus=$this->get_vendor_status_case_query();

		$this->db->select('COUNT(vm.id)');
		$this->db->where("vm.vendor_id=v.id AND vm.status=2 AND vm.archive=0");
    	$this->db->from(TBL_PREFIX.'vendor_mapping as vm');
		$approvedCategoriesSubQuery = $this->db->get_compiled_select();

		$this->db->select('COUNT(vm.id)');
		$this->db->where("vm.vendor_id=v.id AND vm.status=3 AND vm.archive=0");
    	$this->db->from(TBL_PREFIX.'vendor_mapping as vm');
		$pendingCategoriesSubQuery = $this->db->get_compiled_select();
		
	 
		$column = [
			"v.id as vendor_id",
			"v.name as name", 
			"v.gst_number as gst",
			"v.pincode",
			$vendorStatus." as vendor_status",
			$vendorType." as vendor_type_name",
			"(".$approvedCategoriesSubQuery.") as approved_categories",
			"(".$pendingCategoriesSubQuery.") as pending_categories",
		];
		$getVendor = $this->CommonModel->getDataWhere(
			$column,
			["id"=>$vendorId, "archive"=>0],
			TBL_PREFIX.'vendor v',
			['result_type'=>3]
		);

		if(empty($getVendor)){
			return false;
		}


		$this->db->select(["GROUP_CONCAT(sub_c.category_name SEPARATOR ', ') AS name "]);
		$this->db->where(["vm.vendor_id"=>$vendorId, "vm.archive"=>0, "vm.status"=>2]);
		$this->db->join(TBL_PREFIX.'category sub_c', "sub_c.archive = 0 AND sub_c.status=1 AND sub_c.id=vm.category_id");
		$this->db->order_by("vm.created", "DESC");
		$this->db->from(TBL_PREFIX.'vendor_mapping vm');

		$query = $this->db->get();
		$result1 = $query->num_rows() > 0 ? $query->row_array():[];

		$this->db->select(["GROUP_CONCAT(sub_c.category_name SEPARATOR ', ') AS name "]);
		$this->db->where(["vm.vendor_id"=>$vendorId, "vm.archive"=>0, "vm.status"=>1]);
		$this->db->join(TBL_PREFIX.'category sub_c', "sub_c.archive = 0 AND sub_c.status=1 AND sub_c.id=vm.category_id");
		$this->db->order_by("vm.created", "DESC");
		$this->db->from(TBL_PREFIX.'vendor_mapping vm');

		$query = $this->db->get();
		$result2 = $query->num_rows() > 0 ? $query->row_array():[];

		$getVendor->acategories = $result1;
		$getVendor->pcategories = $result2;

		return $getVendor;
	}

	private function get_vendor_source_type_case_query($initial = "vm"){
		return "CASE WHEN $initial.source_type=1 THEN 'Python'  WHEN $initial.source_type=2 THEN 'User' WHEN $initial.source_type=3 THEN 'Admin' ELSE 'N/A' END";
	}




 	//Admin Module Model for Vendors  also some for usermodule as common function

	private function get_vendor_categories(){
		$this->db->select("GROUP_CONCAT(sub_c.category_name SEPARATOR ', ')");
		$this->db->where("vm.vendor_id=v.id AND vm.archive=0 AND vm.status=2");
		$this->db->join(TBL_PREFIX.'category sub_c', "sub_c.archive = 0 AND sub_c.status=1 AND sub_c.id=vm.category_id");
		$this->db->order_by("vm.created", "DESC");
		$this->db->from(TBL_PREFIX.'vendor_mapping vm');
		return $this->db->get_compiled_select();
	}

	public function admin_vendor_list($request_body){

		$vendorType=$this->get_vendor_type_case_query();
		$vendorStatus=$this->get_vendor_status_case_query();
		$vendorNameSubQuery=$this->get_vendor_person_name_subquery();
		$vendorSourceType = $this->get_vendor_category_source_type("v");

		$getVendorCategories = $this->get_vendor_categories();
		


		$login_user_id = $request_body->user_id;
		$limit = $request_body->data->pageSize;
		$page = $request_body->data->page;
		$sorted = $request_body->data->sorted;
		$orderBy = '';
		$direction = '';
		$filter = $request_body->data->filtered;

		$this->db->select('COUNT(vm.id)');
		$this->db->where("vm.vendor_id=v.id AND vm.status=1 AND vm.archive=0");
    	$this->db->from(TBL_PREFIX.'vendor_mapping as vm');
    	$pendingCategoriesSubQuery = $this->db->get_compiled_select();


    	

	
		$sort_columns = array("vendor_id","gst_number", "pincode","vendor_status","vendor_type_name","vendor_name", "source_type");
		if (isset($filter->key) && $filter->key != '') {
		  $this->db->group_start();
		  for ($i = 0; $i < count($sort_columns); $i++) {
			$column_search = $sort_columns[$i];
			if (strstr($column_search, "as") !== false) {
			  $search_column = explode(" as ", $column_search);
			  if ($search_column[0] != 'null')
				$this->db->or_like($search_column[0], $filter->key);
			} else if ($column_search != 'null') {
			  $this->db->or_like($column_search, $filter->key);
			}
		  }
		  $this->db->group_end();
		}
		$queryHavingData = $this->db->get_compiled_select();
        $queryHavingData = explode('WHERE', $queryHavingData);
		$queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';

		if(isset($filter->filterByType) && $filter->filterByType != '0') {  	
			$this->db->where(['vendor_type'=>$filter->filterByType]);                        
        }
        if(isset($filter->filterByStatus) && $filter->filterByStatus != '0') {  	
			$this->db->where(['status'=>$filter->filterByStatus]);                        
        }
		
		if (!empty($sorted)) {
		  if (!empty($sorted[0]->id)) {
			$orderBy = $sorted[0]->id;
			$direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
		  }
		} else {
		  $orderBy ='v.id';
		  $direction = 'DESC';
		}
   
		$select_column = [
			"v.id as vendor_id",
			"v.name as vendor_name", 
			"v.gst_number",
			"v.pincode",
			$vendorStatus." as vendor_status",
			$vendorType." as vendor_type_name",
			"(".$pendingCategoriesSubQuery.") as pending_categories",
			"$vendorSourceType as source_type",
			"(".$getVendorCategories.") as vendor_categories"
		];
		$this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
		// $this->db->select("COALESCE((".$vendorNameSubQuery."),'') as vendor_name",false);
		$array = array('v.archive' => 0);
		$this->db->where($array);
		$this->db->order_by($orderBy, $direction);
		$this->db->limit($limit, ($page * $limit));
		/* it is useed for subquery filter */
        if (!empty($queryHaving)) {
            $this->db->having($queryHaving);
        }
		$query =$this->db->get(TBL_PREFIX.'vendor v');
   
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

	  private function get_vendor_type_case_query(){
		  return "CASE WHEN v.vendor_type=1 THEN 'Global'  WHEN v.vendor_type=2 THEN 'Local' ELSE 'N/A' END ";
	  }
	  private function get_vendor_status_case_query(){
		  return "CASE WHEN v.status=1 THEN 'Pending'  WHEN v.status=2 THEN 'Approved' WHEN v.status=3 THEN 'Disapproved' ELSE 'N/A' END";
	  }
	  
	private function get_vendor_person_name_subquery(){
		  $this->db->select('sub_p.firstname');
		  $this->db->from(TBL_PREFIX.'person sub_p');
		  $this->db->where("sub_p.id=v.person_id");
		  $this->db->limit(1);
		  return $this->db->get_compiled_select();
	}

	public function vendor_approval_model($request_body){

		$request_data = $request_body->data;
		$selectedVendor = $request_data->selected;
		$request_status =( $request_data->key == 1)? 2 : 3;
		$user_id = $request_body->user_id;
		$created = create_date_store_in_db();

		if(!empty($selectedVendor)){
			foreach ($selectedVendor as $key => $value) {
				$this->BasicModel->updateRecords(
					TBL_PREFIX.'vendor', 
					['status'=>$request_status, 'archive' =>0],
					['status'=>1, 'archive' =>0, 'id'=>$value]
				);
				if($request_status == 2){
					$arr =array('id', 'name','created_by','source_type');
					$vendorData = $this->BasicModel->getRecordWhere(TBL_PREFIX.'vendor AS v',$arr,['id'=>$value,  'archive'=>0]);
					
					$alerttype = $vendorData->source_type==1?2:1;

					$this->notification->setAlertTitle("Vendor “".$vendorData->name."” apporved by Admin");
					$this->notification->setAlertType($alerttype);
					$this->notification->setUserId($vendorData->created_by);
					$this->notification->setDescription("Vendor “".$vendorData->name."” apporved by Admin.");
					$this->notification->setIsRead(2);
					$this->notification->setNotificationCreated($created);
					$this->notification->setCreatedByType(2);
					$this->notification->setCreatedBy($user_id);
					$this->notification->setNotificationArchive(0);
					$this->notification->SaveUserNotificationAlert();
				}
					
			}
			return true;

		}else{
			return false;
		}
	}



	public function add_vendor_model($request_data){

		// global vendor check if already exist
		if($request_data->vendor_type == 1){
    		
    		$this->db->select(['v.name', 'v.id']);
    		$this->db->where(["v.vendor_type" => 1, "v.archive" =>0, "v.name"=>$request_data->name]);
    		$this->db->from(TBL_PREFIX.'vendor v');
    		$this->db->limit(1);
    		$query = $this->db->get();
			$result = $query->num_rows() > 0 ? $query->row():[];

			if(!empty($result)){
				echo json_encode(['status'=>false, 'msg' => "Vendor already registered." ]);
				exit();
			}

    	}

    	// local vendor check if gst already exsit
    	if($request_data->vendor_type == 2){
    		$this->db->select(['v.name', 'v.id']);
    		$this->db->where(["v.vendor_type"=> 2, "v.archive"=>0, "v.gst_number"=>$request_data->gst, "v.pincode" => $request_data->pincode, "v.name"=>$request_data->name]);
    		$this->db->from(TBL_PREFIX.'vendor v');
    		$this->db->limit(1);
    		$query = $this->db->get();
    		$result = $query->num_rows() > 0 ? $query->row():[];
    		if(!empty($result)){
				echo json_encode(['status'=>false, 'msg' => "Vendor already registered with these details." ]);
				exit();
			}

    	}

    	$insertVendor = $this->CommonModel->insertData(
    		[
    			'name'=>$request_data->name,
    			'status'=> 2,
    			'created_by'=>0,
    			'created' => create_date_store_in_db(),
    			'archive'=>0,
    			'gst_number' =>($request_data->vendor_type == 2)? $request_data->gst: null,
				'pincode' =>($request_data->vendor_type == 2)? $request_data->pincode: null,
				'vendor_type'=> $request_data->vendor_type,
				'source_type'=>3
    		],
    		TBL_PREFIX.'vendor'
    	);


    	$categoryArr = [];
    	foreach ($request_data->categories as $key => $value) {
    		$arr = [];
    		$arr['vendor_id'] = $insertVendor;
    		$arr['category_id'] = $value->value;
    		$arr['status'] = 2;
    		$arr['source_type'] = 3;
    		$arr['created'] = create_date_store_in_db();
    		$arr['archive'] = 0;
			$categoryArr[] = $arr;

    	}

    	$insertCategories = $this->CommonModel->insertBatch($categoryArr, TBL_PREFIX.'vendor_mapping');

    	return true;

	}


	public function view_vendor_model($request_data, $type=1){
		$vendorId = $request_data->vendor_id;

		$vendorType=($type==1)? $this->get_vendor_type_case_query() : 'v.vendor_type';
		$vendorStatus=($type==1)? $this->get_vendor_status_case_query() : 'v.status';
		$vendorSourceType = ($type==1)? $this->get_vendor_category_source_type("v"):'v.source_type';


		$getVendor = $this->CommonModel->getDataWhere(
			["id", "name", $vendorType. " as vendor_type", "$vendorStatus as status", "gst_number as gst", "pincode", "$vendorSourceType as source_type"],
			["id"=>$vendorId, "archive"=>0],
			TBL_PREFIX.'vendor v',
			['result_type'=>3]
		);

		if(empty($getVendor)){
			return false;
		}


		$this->db->select(["sub_c.category_name as label", "sub_c.id as value"]);
		$this->db->where(["vm.vendor_id"=>$vendorId, "vm.archive"=>0, "vm.status"=>2]);
		$this->db->join(TBL_PREFIX.'category sub_c', "sub_c.archive = 0 AND sub_c.status=1 AND sub_c.id=vm.category_id");
		$this->db->order_by("vm.created", "DESC");
		$this->db->from(TBL_PREFIX.'vendor_mapping vm');

		$query = $this->db->get();
		$result = $query->num_rows() > 0 ? $query->result_array():[];

		$getVendor->categories = $result;

		return $getVendor;

	}


	public function delete_vendor_model($request_data){

		$vendorId = $request_data->vendor_id;
		$updateVendor = $this->CommonModel->UpdateData(
			['id'=>$vendorId, 'archive'=>0],
			['archive'=>1],
			TBL_PREFIX.'vendor'
		);

		if($updateVendor > 0){
			return true;
		}

		return false;
	}


	public function update_vendor_model($request_data){

		// global vendor check if already exist
		if($request_data->vendor_type == 1){
    		
    		$this->db->select(['v.name', 'v.id']);
    		$this->db->where(["v.vendor_type" => 1, "v.archive" =>0, "v.name"=>$request_data->name, 'v.id !=' => $request_data->id]);
    		$this->db->from(TBL_PREFIX.'vendor v');
    		$this->db->limit(1);
    		$query = $this->db->get();
			$result = $query->num_rows() > 0 ? $query->row():[];

			if(!empty($result)){
				echo json_encode(['status'=>false, 'msg' => "Name already registered other vendor." ]);
				exit();
			}

    	}
    	// local vendor check if gst already exsit
    	if($request_data->vendor_type == 2){
    		$this->db->select(['v.name', 'v.id']);
    		$this->db->where([
    			"v.vendor_type"=> 2, 
    			"v.archive"=>0, 
    			"v.gst_number"=>$request_data->gst, 
    			"v.pincode" => $request_data->pincode, 
    			"v.name"=>$request_data->name, 
    			'v.id !=' => $request_data->id
    		]);
    		$this->db->from(TBL_PREFIX.'vendor v');
    		$this->db->limit(1);
    		$query = $this->db->get();
    		$result = $query->num_rows() > 0 ? $query->row():[];
    		if(!empty($result)){
				echo json_encode(['status'=>false, 'msg' => "Vendor already registered with these details." ]);
				exit();
			}

    	}

    	$updateVendor = $this->CommonModel->UpdateData(
    		['id'=>$request_data->id, 'archive'=>0],
    		[
    			'name'=>$request_data->name,
    			'status'=> $request_data->status,
    			'archive'=>0,
    			'gst_number' =>($request_data->vendor_type == 2)? $request_data->gst: null,
				'pincode' =>($request_data->vendor_type == 2)? $request_data->pincode: null,
				'vendor_type'=> $request_data->vendor_type,
    		],
    		TBL_PREFIX.'vendor'
    	);



    	$updateCategory = $this->CommonModel->UpdateData(
    		['vendor_id'=>$request_data->id, 'archive'=>0, 'status !=' => 1],
    		['archive'=>1],
    		TBL_PREFIX.'vendor_mapping'
    	);

    	$currentVendorCategories = $this->CommonModel->getDataWhere(
    		['id', 'category_id'],
    		['vendor_id'=>$request_data->id],
    		TBL_PREFIX.'vendor_mapping',
    		['result_type'=>2]
    	);

    	$categoryIds = pos_index_change_array_data($currentVendorCategories, "category_id");

    	$insertCategories = [];
    	$updateCatgories = [];


    	foreach ($request_data->categories as $key => $value) {
    		if(isset($categoryIds[$value->value])){
    			$updateCatgories[] = [
    				'id'=>$categoryIds[$value->value]['id'],
    				'archive' => 0,
    				'status'=>2
    			];
    		}else{
    			$arr = [];
	    		$arr['vendor_id'] = $request_data->id;
	    		$arr['category_id'] = $value->value;
	    		$arr['status'] = 2;
	    		$arr['source_type'] = 3;
	    		$arr['created'] = create_date_store_in_db();
	    		$arr['archive'] = 0;
				$insertCategories[] = $arr;
    		}
    		
    	}

    	$insertBatch = $this->CommonModel->insertBatch(
    		$insertCategories, TBL_PREFIX.'vendor_mapping'
    	);

    	$updateBatch = $this->CommonModel->updateBatch(
    		$updateCatgories, TBL_PREFIX.'vendor_mapping', 'id'
    	);

    	return true;



	}


	public function single_vendor_approval_model($request_data, $user_id){

		$updateCategory = $this->CommonModel->UpdateData(
    		['id'=>$request_data->vendor_id,  'archive'=>0],
    		['status'=> ($request_data->key == 1)? 2 : 3], 
    		TBL_PREFIX.'vendor'
    	);
    	if($updateCategory > 0){
			$arr =array('id', 'name','created_by','source_type');
			$vendorData = $this->BasicModel->getRecordWhere(TBL_PREFIX.'vendor AS v',$arr,['id'=>$request_data->vendor_id,  'archive'=>0]);
			
			$alerttype = $vendorData->source_type==1?2:1;

			$created = create_date_store_in_db();
			$this->notification->setAlertTitle("Vendor “".$vendorData->name."” apporved by Admin");
			$this->notification->setAlertType($alerttype);
			$this->notification->setUserId($vendorData->created_by);
			$this->notification->setDescription("Vendor “".$vendorData->name."” apporved by Admin.");
			$this->notification->setIsRead(2);
			$this->notification->setNotificationCreated($created);
			$this->notification->setCreatedByType(2);
			$this->notification->setCreatedBy($user_id);
			$this->notification->setNotificationArchive(0);
			$this->notification->SaveUserNotificationAlert();
    		return true;
    	}

    	return false;

	}	

	private function get_vendor_category_status_case_query(){
	  	return "CASE WHEN vm.status=1 THEN 'Pending'  WHEN vm.status=2 THEN 'Approved' WHEN vm.status=3 THEN 'Disapproved' ELSE 'N/A' END";
	}

	private function get_vendor_category_source_type($type = "vm"){
	  	return "CASE WHEN $type.source_type=1 THEN 'AI'  WHEN $type.source_type=2 THEN 'User' WHEN $type.source_type=3 THEN 'Admin' ELSE 'N/A' END";
	}


	public function vendor_pending_category_list_model($request_data){

		$page = $request_data->page;
		$sorted = $request_data->sorted;
		$filter = $request_data->filtered;
		$limit = $request_data->pageSize;
		$orderBy = '';
		$direction = '';
		$vendorCategoryStatus = $this->get_vendor_category_status_case_query();
		$vendorCategorySourceType = $this->get_vendor_category_source_type();


		$vendor_details = $this->CommonModel->getDataWhere(
			['name'],
			['id'=>$request_data->vendor_id, 'archive'=>0],
			TBL_PREFIX.'vendor',
			['result_type'=>3]
		);

		if(empty($vendor_details)){
			return false;
		}

		$sort_columns = array("id", "category_name", "source_type");
		if (isset($filter->key) && $filter->key != '') {
		  $this->db->group_start();
		  for ($i = 0; $i < count($sort_columns); $i++) {
			$column_search = $sort_columns[$i];
			if (strstr($column_search, "as") !== false) {
			  $search_column = explode(" as ", $column_search);
			  if ($search_column[0] != 'null')
				$this->db->or_like($search_column[0], $filter->key);
			} else if ($column_search != 'null') {
			  $this->db->or_like($column_search, $filter->key);
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
		  $orderBy ='vm.id';
		  $direction = 'DESC';
		}

		$select_columns = [
			'vm.id', 
			'c.category_name', 
			"$vendorCategoryStatus as status",
			"$vendorCategorySourceType as source_type"
		];
		$this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_columns)), false);
		$this->db->where([
			'vm.archive'=>0,
			'vm.vendor_id'=>$request_data->vendor_id,
			'vm.status'=>1
		]);
		$this->db->join(TBL_PREFIX.'category c', "vm.category_id = c.id AND c.status=1 AND c.archive=0");
		$this->db->join(TBL_PREFIX.'vendor v', "v.archive=0 AND v.id=$request_data->vendor_id");
		$this->db->order_by($orderBy, $direction);
		$this->db->limit($limit, ($page * $limit));

		/* it is used for subquery filter */
        if (!empty($queryHaving)) {
            $this->db->having($queryHaving);
        }
		$query =$this->db->get(TBL_PREFIX.'vendor_mapping vm');
   
		$dt_filtered_total = $all_count = $this->db->query('SELECT FOUND_ROWS() as pages;')->row()->pages;
		if ($dt_filtered_total % $limit == 0) {
		  $dt_filtered_total = ($dt_filtered_total / $limit);
		} else {
		  $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
		}

		$result = $query->result();

		
		$return = [
			'pages' => $dt_filtered_total, 
			'data' => $result, 
			'all_count' => $all_count,
			'vendor_name'=>$vendor_details->name
		];
   
		return $return;

	}


	public function vendor_category_approval_model($request_data, $adminuser_id=''){

		$status = ($request_data->key == 1)? 1:2;
		$type = $request_data->type;

		$status = ($request_data->key == 1)? 2 : 3;// 1-pending, 2-approve, 3-disapprove

		$created = create_date_store_in_db();
		if($type == 1 && isset($request_data->category_id)){

			
			$updateCategory = $this->CommonModel->UpdateData(
	    		['id'=>$request_data->category_id,  'archive'=>0],
	    		['status'=> $status], 
	    		TBL_PREFIX.'vendor_mapping'
	    	);

			
	    	if($updateCategory > 0){

	    		if($status == 3){
					$disapproveFn = $this->disapprove_pending_category($request_data, 1);
				}
				if($status == 2){
					/* Notification set */

					$this->db->select('v.name,v.created_by,v.source_type,c.category_name');
					$this->db->where(array('vm.id'=>$request_data->category_id,'vendor_id'=>$request_data->vendor_id));
					$this->db->join(TBL_PREFIX.'vendor AS v','vm.vendor_id= v.id AND v.archive=0','inner');
					$this->db->join(TBL_PREFIX.'category AS c','vm.category_id= c.id AND c.archive=0','inner');
					$vendorSql = $this->db->get(TBL_PREFIX.'vendor_mapping AS vm');

					if($vendorSql->num_rows() > 0){
						$vendorData = $vendorSql->row_array();
						if($vendorData['source_type'] == 1){
							$alertType = 2;
						}else{
							$alertType =1;
						}
					
						$this->notification->setAlertTitle("Your category ".(isset($vendorData['category_name'])?$vendorData['category_name']:'')." is Approved for Vendor ".(isset($vendorData['name'])?$vendorData['name']:''));
						$this->notification->setAlertType($alertType);
						$this->notification->setUserId(isset($vendorData['created_by'])?$vendorData['created_by']:0);
						$this->notification->setDescription("Your category “".(isset($vendorData['category_name'])?$vendorData['category_name']:'')."” is Approved for Vendor “".(isset($vendorData['name'])?$vendorData['name']."”.":''));
						$this->notification->setIsRead(2);
						$this->notification->setNotificationCreated($created);
						$this->notification->setCreatedByType(2);
						$this->notification->setCreatedBy($adminuser_id);
						$this->notification->setNotificationArchive(0);
						$this->notification->SaveUserNotificationAlert();
					}
				}
	    		return true;
	    	}

	    	return false;
		}
		elseif ($type == 2 && isset($request_data->selected)) {
			
			if(!empty($request_data->selected)){
				$selectedCategories = $request_data->selected;
				
				foreach ($selectedCategories as $key => $value) {
					$this->CommonModel->UpdateData(
						['id'=>$value, 'status'=>1, 'archive' =>0],
						['status'=>$status, 'archive' =>0],
						TBL_PREFIX.'vendor_mapping'
					);
					if($status == 2){
						/* Notification set */
	
						$this->db->select('v.name,v.created_by,v.source_type,c.category_name');
						$this->db->where(array('vm.id'=>$value));
						$this->db->join(TBL_PREFIX.'vendor AS v','vm.vendor_id= v.id AND v.archive=0','inner');
						$this->db->join(TBL_PREFIX.'category AS c','vm.category_id= c.id AND c.archive=0','inner');
						$vendorSql = $this->db->get(TBL_PREFIX.'vendor_mapping AS vm');
	
						if($vendorSql->num_rows() > 0){
							$vendorData = $vendorSql->row_array();
							if($vendorData['source_type'] == 1){
								$alertType = 2;
							}else{
								$alertType =1;
							}
						
							$this->notification->setAlertTitle("Your category ".(isset($vendorData['category_name'])?$vendorData['category_name']:'')." is Approved for Vendor ".(isset($vendorData['name'])?$vendorData['name']:''));
							$this->notification->setAlertType($alertType);
							$this->notification->setUserId(isset($vendorData['created_by'])?$vendorData['created_by']:0);
							$this->notification->setDescription("Your category ".(isset($vendorData['category_name'])?$vendorData['category_name']:'')." is Approved for Vendor ".(isset($vendorData['name'])?$vendorData['name']:''));
							$this->notification->setIsRead(2);
							$this->notification->setNotificationCreated($created);
							$this->notification->setCreatedByType(2);
							$this->notification->setCreatedBy($adminuser_id);
							$this->notification->setNotificationArchive(0);
							$this->notification->SaveUserNotificationAlert();
						}
					}
				}

				if($status == 3){
					$disapproveFn = $this->disapprove_pending_category($request_data, 2);
				}
				return true;

			}else{
				return false;
			}
		}


	}


	private function disapprove_pending_category($request_data, $type = 1){

		$getMiscellaneousId = $this->CommonModel->getDataWhere(
			['id'],
			['key_name'=>'Miscellaneous', 'status' => 1, 'archive' => 0],
			TBL_PREFIX.'category',
			['result_type'=>3]
		);

		if(!isset($getMiscellaneousId)){
			return true;
		}

		// check if Miscellaneous already mapped with this vendor in vendor_mapping
		$vendorMappedMiscId = null;

		$checkVendorMisc = $this->CommonModel->getDataWhere(
			['id'],
			[
				'vendor_id'=>$request_data->vendor_id, 
				'category_id'=>$getMiscellaneousId->id, 
			],
			TBL_PREFIX.'vendor_mapping',
			['result_type'=>3]
		);
			
		if(isset($checkVendorMisc)){
			$vendorMappedMiscId = $checkVendorMisc->id;

			$this->CommonModel->UpdateData(
				['id'=>$vendorMappedMiscId],
				['status'=>2, 'archive' =>0],
				TBL_PREFIX.'vendor_mapping'
			);
		}
		else{
			$insertMiscForMap = $this->CommonModel->insertData(
				[	
					'vendor_id'=> $request_data->vendor_id, 
					'category_id' => $getMiscellaneousId->id,
					'status' => 2,
					'source_type'=>3,
					'created' => create_date_store_in_db(),
					'archive'=>0
				],
				TBL_PREFIX.'vendor_mapping'
			);

			$vendorMappedMiscId = $insertMiscForMap;
		}



		// check if Miscellaneous already mapped in this tbl_fm_category_mapping
		$categoryMappedMiscId = null;

		$checkCategoryMisc = $this->CommonModel->getDataWhere(
			['id'],
			['category_id'=>$getMiscellaneousId->id],
			TBL_PREFIX.'category_mapping',
			['result_type'=>3]
		);

		if(isset($checkCategoryMisc)){
			$categoryMappedMiscId = $checkCategoryMisc->id;

			$this->CommonModel->UpdateData(
				['id'=>$categoryMappedMiscId],
				['status'=>1, 'archive' =>0],
				TBL_PREFIX.'category_mapping'
			);
		}
		else{
			$insertMiscForCategoryMap = $this->CommonModel->insertData(
				[ 
					'category_id' => $getMiscellaneousId->id,
					'sub_category_id' => 0,
					'status' => 1,
					'created' => create_date_store_in_db(),
					'archive'=>0
				],
				TBL_PREFIX.'category_mapping'
			);

			$categoryMappedMiscId = $insertMiscForCategoryMap;
		}





		if($type == 1){
			
			// statement line item mapped_id change
			$this->CommonModel->UpdateData(
				['mapped_id'=>$request_data->category_id, 'archive'=>0],
				['mapped_id'=>$vendorMappedMiscId],
				TBL_PREFIX.'statement_line_item'
			);


			// invoice line item mapped_id change

			$getCategoryIdInVendorMapping = $this->CommonModel->getDataWhere(
				['category_id'],
				[
					'id'=> $request_data->category_id, 
					'archive'=>0, 
					'status'=>3
				],
				TBL_PREFIX.'vendor_mapping',
				['result_type'=>3]
			);

			if(!isset($getCategoryIdInVendorMapping)){
				return true;
			}

			$getMappedCategoryId = $this->CommonModel->getDataWhere(
				['id'],
				[
					'category_id'=> $getCategoryIdInVendorMapping->category_id, 
					'archive'=>0, 
					'status'=>1
				],
				TBL_PREFIX.'category_mapping',
				['result_type'=>3]
			);
			if(!isset($getMappedCategoryId)){
				return true;
			}

			$this->db->select(['ili.id']);
			$this->db->where("ili.mapping_id=$getMappedCategoryId->id AND ili.archive=0");
			$this->db->join(TBL_PREFIX.'invoice i', "i.vendor_id=$request_data->vendor_id AND i.id=ili.invoice_id AND i.archive=ili.archive", 'inner');
			$this->db->from(TBL_PREFIX.'invoice_line_item ili');
			$lineItemQuery = $this->db->get();
			$lineItemQuery_result =$lineItemQuery->num_rows()>0? $lineItemQuery->result_array():[];

			if(empty($lineItemQuery_result)){
				return true;
			}
			$this->db->where_in('id',array_column($lineItemQuery_result ,'id'));
			$this->CommonModel->UpdateData(
				['archive'=>0,'mapping_id!='=>$categoryMappedMiscId],
				['mapping_id'=>$categoryMappedMiscId],
				TBL_PREFIX.'invoice_line_item'
			);



		}

		if($type == 2){

			$selectedCategories = $request_data->selected;
			
			// statement line item mapped_id change		
			foreach ($selectedCategories as $key => $value) {
				$this->CommonModel->UpdateData(
					['mapped_id'=>$value, 'archive'=>0],
					['mapped_id'=>$vendorMappedMiscId],
					TBL_PREFIX.'statement_line_item'
				);
			}

			// invoice line items mapped_id change

			$this->db->select(['ili.id']);
			$this->db->where("ili.archive=0");
			$this->db->where_in("vm.id",$selectedCategories);
			$this->db->from(TBL_PREFIX.'invoice_line_item ili');
			$this->db->join(TBL_PREFIX.'vendor_mapping vm', "vm.vendor_id=$request_data->vendor_id AND vm.archive=0",'inner');
			$this->db->join(TBL_PREFIX."category_mapping cm", "cm.category_id=vm.category_id AND vm.archive=cm.archive and ili.mapping_id=cm.id",'inner');
			$this->db->join(TBL_PREFIX.'invoice i', "i.vendor_id=vm.vendor_id AND i.id=ili.invoice_id AND i.archive=ili.archive", 'inner');

			$lineItemQuery = $this->db->get();
			$lineItemQuery_result =$lineItemQuery->num_rows()>0? $lineItemQuery->result_array():[];

			if(empty($lineItemQuery_result)){
				return true;
			}
			$this->db->where_in('id',array_column($lineItemQuery_result ,'id'));
			$this->CommonModel->UpdateData(
				['archive'=>0,'mapping_id!='=>$categoryMappedMiscId],
				['mapping_id'=>$categoryMappedMiscId],
				TBL_PREFIX.'invoice_line_item'
			);

		}

		return true;

	}


}
