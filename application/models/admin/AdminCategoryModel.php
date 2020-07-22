<?php

defined('BASEPATH') or exit('No direct script access allowed');

class AdminCategoryModel extends CI_Model
{
	function get_category_list($request_body) {
		$limit = $request_body->data->pageSize;
		$page = $request_body->data->page;
		$sorted = $request_body->data->sorted;
		$orderBy = '';
		$direction = '';
		$filter = $request_body->data->filtered;

		$getUserTimezone = $request_body->time_zone_mysql?? '+00:00';

		$sort_columns = array(
			"id",
			"category_name as name",
			 "(CASE WHEN status=1 THEN 'Active' WHEN status=2 THEN 'Inactive' ELSE '' END) as status", 
			 "DATE_FORMAT(CONVERT_TZ(created,'+00:00', '".$getUserTimezone."'), '%d/%m/%Y') as created"
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

		if (isset($filter->filterBy) && $filter->filterBy != '') {
			$this->db->where('status', $filter->filterBy);	    
		}
		$sortorder = getSortBy(
			$sorted, 
			['created'=>"created"], 
			['orderBy'=> TBL_PREFIX.'category.id', 'direction'=>'DESC']
		);
		$this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $sort_columns)), false);
		$array = array('archive' => 0); 
		$this->db->where($array);
		$this->db->order_by($sortorder['orderBy'], $sortorder['direction']);
		$this->db->limit($limit, ($page * $limit));
		$query =$this->db->get(TBL_PREFIX.'category');

		$dt_filtered_total = $all_count = $this->db->query('SELECT FOUND_ROWS() as pages;')->row()->pages;
		if ($dt_filtered_total % $limit == 0) {
			$dt_filtered_total = ($dt_filtered_total / $limit);
		} else {
			$dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
		}
		$result = $query->result();
		return array('pages' => $dt_filtered_total, 'data' => $result, 'all_count' => $all_count);
	}

	



	 
	public function get_category($request_data){
		$categoryId = $request_data->category_id;
		$getCategory = $this->CommonModel->getDataWhere(
			["id", 'category_name as name'],
			['id'=>$categoryId,"archive"=>0],
			TBL_PREFIX.'category',
			['result_type'=>3]
		);
		if(empty($getCategory)){
			return false;
		}
 		return $getCategory;
	}

	public function update_category($request_data){
		// Category check if already exist
		if(!empty($request_data->id)){
    		$this->db->select(['category_name', 'id']);
    		$this->db->where(["archive" =>0, "category_name"=>$request_data->name, 'id!=' => $request_data->id]);
    		$this->db->from(TBL_PREFIX.'category');
    		$this->db->limit(1);
    		$query = $this->db->get();
			$result = $query->num_rows() > 0 ? $query->row():[];
			if(!empty($result)){
				echo json_encode(['status'=>false, 'msg' => "Category Name already Exist." ]);
				exit();
			}
			$this->CommonModel->UpdateData(
				['id'=>$request_data->id, 'archive'=>0],
				[
					'category_name'=>$request_data->name
				],
				TBL_PREFIX.'category'
			);
			return true;
    	} else {
			return false;
		}
	}

	public function add_category($request_data){
		// Category check if already exist
		if($request_data->name){
    		$this->db->select(['category_name', 'id']);
    		$this->db->where(["archive" => 0, "category_name"=>$request_data->name]);
    		$this->db->from(TBL_PREFIX.'category');
    		$this->db->limit(1);
    		$query = $this->db->get();
			$result = $query->num_rows() > 0 ? $query->row():[];

			if(!empty($result)){
				echo json_encode(['status'=>false, 'msg' => "Category already exist." ]);
				exit();
			}
		}
    	$insertCategory = $this->CommonModel->insertData(
    		[
    			'category_name'=>$request_data->name,
    			'status'=> 1,
    			'created' => create_date_store_in_db(),
    			'archive'=>0
    		],
    		TBL_PREFIX.'category'
    	);
    	return true;
	}

	public function delete_category($request_data){
		$categoryId = $request_data->category_id;
		$updateCategory = $this->CommonModel->UpdateData(
			['id'=>$categoryId, 'archive'=>0],
			['archive'=>1],
			TBL_PREFIX.'category'
		);
		if($updateCategory > 0){
			return true;
		}
		return false;
	}


	



}
