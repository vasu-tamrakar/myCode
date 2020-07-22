<?php

defined('BASEPATH') or exit('No direct script access allowed');

class  ProtectedPdfModel extends CI_Model
{
	function get_protected_pdf_list_model($request_body) {
		 
		$loginId = $request_body->user_id;
		$limit = $request_body->data->pageSize;
		$page = $request_body->data->page;
		$sorted = $request_body->data->sorted;
		$orderBy = '';
		$direction = '';
		$getUserTimezone = $request_body->time_zone_mysql?? '+00:00';
		$filter = $request_body->data->filtered;
		 

		$sort_columns = array("id",
        "pd.original_file_name as filename",
        "pd.file_name as present_filename",
        "'' as bank_id",
        "'' as password",
		"DATE_FORMAT(CONVERT_TZ(pd.created,'+00:00', '".$getUserTimezone."'), '%d/%m/%Y') as created",
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
        
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id)) {
              $orderBy = $sorted[0]->id;
              $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
          } else {
            $orderBy ='pd.id';
            $direction = 'DESC';
          }

		
		$this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $sort_columns)), false);
		$array = array('pd.archive' => 0, 'pd.user_id'=>$loginId); 
		$this->db->where($array);
		$this->db->order_by($orderBy, $direction);
		$this->db->limit($limit, ($page * $limit));
		$query =$this->db->get(TBL_PREFIX.'unreadable_pdf as pd');
 
		$dt_filtered_total = $all_count = $this->db->query('SELECT FOUND_ROWS() as pages;')->row()->pages;
		if ($dt_filtered_total % $limit == 0) {
			$dt_filtered_total = ($dt_filtered_total / $limit);
		} else {
			$dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
		}
		$result = $query->result();
		return array('pages' => $dt_filtered_total, 'data' => $result, 'all_count' => $all_count);
    }

    public function get_all_dropdown_password_model($request_body){
        $loginId = $request_body->user_id;
        $sub_query_to_get_bankname = $this->get_psw_bankname('name');
        $columns = [
            'bp.id as value',
            "(" . $sub_query_to_get_bankname . ") as label"
        ];
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $columns)), false);
		$array = array('bp.archive' => 0, 'bp.user_id'=>$loginId); 
		$this->db->where($array);
        $query =$this->db->get(TBL_PREFIX.'bank_password as bp');
        $result = $query->result_array(); 
        return $result;
    }

    
    private function get_psw_bankname($column) {
        $select  =  'b.bank_name';
        $this->db->select($select);
        $this->db->from(TBL_PREFIX.'bank b');
        $this->db->where("b.id=bp.bank_id AND b.status=1 AND b.archive=0", NULL, false);
        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }
    
    public function unprotect_pdf_delete($request_body){
        $request_data = $request_body->data; 
		$pdf_psw_id = $request_data->id;
		$updateCategory = $this->CommonModel->UpdateData(
			['id'=>$pdf_psw_id, 'archive'=>0],
			['archive'=>1],
			TBL_PREFIX.'unreadable_pdf'
		);
		if($updateCategory > 0){
			return true;
		}
		return false;
    }
    
    public function decrypt_pdf_by_password_model($request_body){
        $request_data = $request_body->data; 
        if(!empty($request_data)){
            
            $id=$request_body->data->id;
            $bank_id = $request_body->data->bank_id;
            $givenpassword = $request_body->data->password;
            $userId = $request_body->user_id;
            $result = $this->BasicModel->getRecordWhere(TBL_PREFIX.'unreadable_pdf','file_name', ['id'=>$id]);
            if(!empty($result) && !empty($result->file_name)){
                $filePath  = FCPATH.USER_ATTACHMENT_PATH.$result->file_name;
                $pwd = $this->BasicModel->getRecordWhere(TBL_PREFIX.'bank_password','password', ['id'=>$bank_id]);
                $password = (!empty($bank_id) && $bank_id!=0)? encrypt_decrypt('decrypt',$pwd->password):$givenpassword;
                $targetPath =  FCPATH.USER_ATTACHMENT_PATH.'psw_'.$bank_id.$result->file_name;
                $remvoedPwd = encrypyted_pdf_to_decrypt($targetPath, $filePath, $password); 
                if(!empty($remvoedPwd)){
                    $alldata['file']= 'psw_'.$bank_id.$result->file_name;
                    $alldata['protected']= 0;
                    $alldata['decrypted_file']= 1;
                    $pdfdetails = checkStatementType($alldata,$userId);  
                    $insert = $this->insertAttachment($pdfdetails);
                    if(!empty($insert)){
                        $this->BasicModel->updateRecords(TBL_PREFIX.'unreadable_pdf',['archive'=>1],['id'=>$id]); 
                        return $insert;
                    } else {
                        return ['status'=>false];
                    }
                } else {
                    echo json_encode(['status'=>false, 'msg'=>'Sorry, its wrong password.']);
                    exit;
                }               
            }
        }
    }

    private function insertAttachment($pdfdetails){
		if(!empty($pdfdetails)){
            $table = array_keys($pdfdetails)[0];
            if(!empty($table) && !empty($table)){
            $insert_id = $this->BasicModel->insertRecords($table, $pdfdetails,TRUE);
            $whichTbl  = ($table=='tbl_fm_statement')?'Statement':'Invoice';
            return ['status'=>true, 'table'=> $whichTbl]; 
            } 	   
		}
	}
}