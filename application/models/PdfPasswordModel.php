<?php

defined('BASEPATH') or exit('No direct script access allowed');

class  PdfPasswordModel extends CI_Model
{
	function get_pdfpassword_list($request_body) {
		 
		$loginId = $request_body->user_id;
		$limit = $request_body->data->pageSize;
		$page = $request_body->data->page;
		$sorted = $request_body->data->sorted;
		$orderBy = '';
		$direction = '';
		$getUserTimezone = $request_body->time_zone_mysql?? '+00:00';
		$filter = $request_body->data->filtered;
		$sub_query_to_get_bankname = $this->get_bankname();

		$sort_columns = array("bp.id",
		"bp.bank_id",
		"bp.password",
		"(" . $sub_query_to_get_bankname . ") as bankname",
		"DATE_FORMAT(CONVERT_TZ(bp.created,'+00:00', '".$getUserTimezone."'), '%d/%m/%Y') as created",
		"DATE_FORMAT(CONVERT_TZ(bp.updated,'+00:00', '".$getUserTimezone."'), '%d/%m/%Y') as updated",
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
			['created'=>"bp.created",'updated'=>"bp.updated"],
			['orderBy'=> 'bp.id', 'direction'=>'DESC']
		  );
		 
		$this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $sort_columns)), false);
		$array = array('bp.archive' => 0, 'bp.user_id'=>$loginId); 
		$this->db->where($array);
		$this->db->order_by($sortorder['orderBy'], $sortorder['direction']);
		$this->db->limit($limit, ($page * $limit));
		$query =$this->db->get(TBL_PREFIX.'bank_password as bp');
 
		$dt_filtered_total = $all_count = $this->db->query('SELECT FOUND_ROWS() as pages;')->row()->pages;
		if ($dt_filtered_total % $limit == 0) {
			$dt_filtered_total = ($dt_filtered_total / $limit);
		} else {
			$dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
		}
		$result = $query->result();
		return array('pages' => $dt_filtered_total, 'data' => $result, 'all_count' => $all_count);
	}

	private function get_bankname() {
		$this->db->select('b.bank_name');
		$this->db->from(TBL_PREFIX.'bank b');
		$this->db->where("b.id=bp.bank_id AND b.status=1 AND b.archive=bp.archive", NULL, false);
		$this->db->limit(1);
		return $this->db->get_compiled_select();
	  }

	 
	 
	public function pdf_password_view($request_data){
		$pdf_psw_id = $request_data->pdf_psw_id;
		$get_bank_password = $this->CommonModel->getDataWhere(
			["id", "bank_id"],
			['id'=>$pdf_psw_id,"archive"=>0],
			TBL_PREFIX.'bank_password',
			['result_type'=>3]
		);
		if(empty($get_bank_password)){
			return false;
		}
 		return $get_bank_password;
	}

	public function update_pdf_password($request_body){
		$request_data = $request_body->data;
		$pdf_psw_id = $request_data->id;
		$loginId = $request_body->user_id; 
		if(!empty($request_data->id)){
			$encrypt_password =  encrypt_decrypt('encrypt',$request_data->password);
			$this->CommonModel->UpdateData(
				['id'=>$request_data->id, 'archive'=>0],
				[
					'bank_id'=>$request_data->bank_id,
					'password' => $encrypt_password,
					'user_id'=>$loginId,
					'updated' => create_date_store_in_db(),
				],
				TBL_PREFIX.'bank_password'
			);
			return true;
    	} else {
			return false;
		}
	}

	public function add_pdf_password($request_body){
		$request_data = $request_body->data;
		$loginId = $request_body->user_id; 
		// Password check if already exist for same bank and user
		if(!empty($request_data) && $loginId){
    		$this->db->select(['bank_id', 'id']);
    		$this->db->where(["archive" => 0, 'user_id'=> $loginId, "bank_id"=>$request_data->bank_id]);
    		$this->db->from(TBL_PREFIX.'bank_password');
    		$this->db->limit(1);
    		$query = $this->db->get();
			$result = $query->num_rows() > 0 ? $query->row():[];
			if(!empty($result)){
				echo json_encode(['status'=>false, 'msg' => "For this Bank Password already exist." ]);
				exit();
			}
		}
		$encrypt_password =  encrypt_decrypt('encrypt',$request_data->password);
    	$insertPdfPassword = $this->CommonModel->insertData(
    		[
				'bank_id'=>$request_data->bank_id,
				'user_id'=>$loginId,
				'password' => $encrypt_password,
    			'created' => create_date_store_in_db(),
    			'archive'=>0
    		],
    		TBL_PREFIX.'bank_password'
    	);
    	return true;
	}

	public function pdf_password_delete($request_data){
		$pdf_psw_id = $request_data->id;
		$updateCategory = $this->CommonModel->UpdateData(
			['id'=>$pdf_psw_id, 'archive'=>0],
			['archive'=>1],
			TBL_PREFIX.'bank_password'
		);
		if($updateCategory > 0){
			return true;
		}
		return false;
	}


	



}
