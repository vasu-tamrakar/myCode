<?php

defined('BASEPATH') or exit('No direct script access allowed');

class CategoryModel extends CI_Model
{

    public function getCategoryList($requestData){
      
      $sql = '';
      if((isset($requestData->data->vendor_id)) && ($requestData->data->vendor_id >0)){
        $vendor_id = $requestData->data->vendor_id;
        $this->db->select('vm.category_id');
        $this->db->where(array("vm.vendor_id"=>$vendor_id,"vm.archive"=>0, "vm.status="=>3));
        $this->db->where("c.id=vm.category_id");
        $sql = $this->db->get_compiled_select(TBL_PREFIX.'vendor_mapping AS vm');
      }
      
      
      $column_set =  ['c.id as value','c.category_name as label'];
      $this->db->select($column_set);
      $this->db->where(array('c.parent_id' => 0, 'c.status' => '1', 'c.archive'=>'0'));
      
      if(!empty($sql)){
        $this->db->where("c.id NOT IN ($sql)");
      }
      $this->db->order_by('c.id');
      $query = $this->db->get(TBL_PREFIX.'category AS c');

      if($query->num_rows() > 0){
        $categories=array();
        $result = $query->result_array();
        return $result;
      }else{
        return false;
      }
    }


    public function getSubCategoryList($requestData){
      if(!empty($requestData->data)){
        $category_name = $requestData->data->category_name;
        $getCategoryId = $this->getCategoryId($category_name);
        if(!empty($getCategoryId))
        $column_set = ['id','category_name'];
        $this->db->select($column_set);
        $this->db->where(array('parent_id' => $getCategoryId, 'status' => '1', 'archive'=>'0'));
        $query = $this->db->get(TBL_PREFIX.'category');
        if($query->num_rows() > 0){
          $subcategories=array();
          $result = $query->result_array();
          foreach ($result as $key => $value) {
             $row['value'] =  $value['category_name'];
             $row['label']  = $value['category_name'];
             $subcategories[]  = (object) $row;
          }
            return $subcategories;
        }else{
          return false;
        }
      }
    }

    public function getCategoryId($catName){
      $this->db->where(array('parent_id' => 0,  'category_name' => $catName ,'status' => '1', 'archive'=>'0'));
      $query = $this->db->get(TBL_PREFIX.'category');
      return (int) $query->row_array()['id'];
    }

    public function getList($request_body) {
      $limit = $request_body->data->pageSize;
      $page = $request_body->data->page;
      $sorted = $request_body->data->sorted;
      $filter = $request_body->data->filtered;
      $orderBy = '';
      $direction = '';

      $array = array("id","name","parentId");

      if (!empty($sorted)) {
        if (!empty($sorted[0]->id)) {
          $orderBy = $sorted[0]->id;
          $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
        }
      } else {
        $orderBy = $array;
        $direction = 'DESC';
      }

      if (isset($filter->search) && $filter->search != '') {

        $this->db->group_start();
        for ($i = 0; $i < count($array); $i++) {
          $column_search = $array[$i];
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

      $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $array)), false);
      $this->db->where('archive', 0);
      $this->db->order_by($orderBy, $direction);

      $this->db->limit($limit, ($page * $limit));

      $query = $this->db->get('tbl_category') or die('MySQL Error: ' . $this->db->_error_number());
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

    public function get_all_parents_categories_model($column=false){
      $column_set = $column?$column:['id','category_name'];
      $this->db->select($column_set);
      $this->db->where(array('parent_id' => 0, 'status' => 'active', 'archive'=>'0'));
      $query = $this->db->get(TBL_PREFIX.'category');
      if($query->num_rows() > 0){
        return $query->result_array();
      }else{
        return false;
      }
    }


}
