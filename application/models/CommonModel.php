<?php

defined('BASEPATH') || exit('No direct script access allowed');

class CommonModel extends CI_Model {


	public function insertData($data, $tableName){
		$this->db->insert($tableName, $data);
        return $this->db->insert_id();
	}

	public function UpdateData($arr, $data, $tblName) {

        $this->db->where($arr);
        $this->db->update($tblName, $data);
        return $this->db->affected_rows();
    }

	public function getDataWhere($columnArr, $whereArr, $tableName, $extraPrms=[]){
		$resultType = $extraPrms['result_type'] ?? 1;
		$resultTypeData=json_decode(MYSQl_RESULT_KEY_DATA,true);//['1'=>'result','2'=>'result_array','3'=>'row','4'=>'row_array']
		$orderBy = $extraPrms['order_by'] ?? '';
		$this->db->select($columnArr);
		$this->db->where($whereArr);
		if(!empty($orderBy) && is_string($orderBy)){
			$this->db->order_by($orderBy);
		}
		$query = $this->db->get($tableName);
        return  $query->{$resultTypeData[$resultType]}();
	}
 
	public function insertBatch($data, $table_name){
		if (!empty($table_name) && !empty($data) && is_array($data)) {
            $this->db->insert_batch($table_name, $data);
            return true;
        }
	}

	public function updateBatch($data, $table_name, $update_base_column_key=""){
		if (!empty($table_name) && !empty($update_base_column_key) && !empty($data) && is_array($data)) {
            $this->db->update_batch($table_name, $data, $update_base_column_key);
            return true;
        }else{
        	return false;
        }
	}

	

}
