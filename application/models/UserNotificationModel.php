<?php

class UserNotificationModel extends CI_Model
{
	public function get_notification_list_model($request_body) {
		$getUserTimezone = $request_body->time_zone_mysql?? '+00:00';
		$limit = $request_body->data->pageSize;
		$page = $request_body->data->page;
		$sorted = $request_body->data->sorted;
		
		$filter = $request_body->data->filtered;
        $user_id = $request_body->user_id;
		$sort_columns = array(
            "id",
            "IF(CHAR_LENGTH(alert_title) > 100, CONCAT(LEFT(alert_title,100), '...'),alert_title) as title",
            "(CASE WHEN is_read=1 THEN 'Read' WHEN is_read=2 THEN 'Unread' ELSE '' END) as is_read",
			"DATE_FORMAT(CONVERT_TZ(created,'+00:00', '".$getUserTimezone."'), '%d/%m/%Y') as created");

		if (isset($filter->search) && $filter->search != '') {

			$this->db->group_start();
			for ($i = 0; $i < count($sort_columns); $i++) {
				$column_search = $sort_columns[$i];
				if (strstr($column_search, "as") !== false) {
					$search_column = explode(" as ", $column_search);
					if ($search_column[0] != 'null'){
						$this->db->or_like($search_column[0], $filter->search);
					}
				} else if ($column_search != 'null') {
					$this->db->or_like($column_search, $filter->search);
				}
			}
			$this->db->group_end();
		}

		if (isset($filter->filterBy) && $filter->filterBy != '') {
			$this->db->where('is_read', $filter->filterBy);	    
		}

		$sortorder = getSortBy(
			$sorted, 
			['created'=>"created"], 
			['orderBy'=> 'id', 'direction'=>'DESC']
		  );

	 
		$this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $sort_columns)), false);
		$array = array('alert_type'=>1, 'user_id' =>$user_id, 'created_by_type' =>2, 'archive' => 0); 
		$this->db->where($array);
		$this->db->order_by($sortorder['orderBy'], $sortorder['direction']);
		$this->db->limit($limit, ($page * $limit));
		$query =$this->db->get(TBL_PREFIX.'notification');

		$dt_filtered_total = $all_count = $this->db->query('SELECT FOUND_ROWS() as pages;')->row()->pages;
		if ($dt_filtered_total % $limit == 0) {
			$dt_filtered_total = ($dt_filtered_total / $limit);
		} else {
			$dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
		}
		$result = $query->result();
		return array('pages' => $dt_filtered_total, 'data' => $result, 'all_count' => $all_count);
	}

 


	public function get_notification_detail_model($request_body){
		$getUserTimezone = $request_body->time_zone_mysql?? '+00:00';
		$notification_id = $request_body->data->notification_id;
		$user_id = $request_body->user_id;
        $arrwhere = array('n.id' => $notification_id,'n.alert_type' => 1,'n.user_id' => $user_id,'n.archive' => 0);
        $this->db->where($arrwhere);
        $this->db->update(TBL_PREFIX.'notification AS n', array('is_read'=> 1));
			$arrGet =array(
                "n.id",
                "n.alert_title",
                "n.description",
                "(CASE WHEN n.is_read=1 THEN 'Read' WHEN n.is_read=2 THEN 'Unread' ELSE '' END)AS is_read",
				"created_by_type",
				"created_by",
                "DATE_FORMAT(CONVERT_TZ(n.created,'+00:00', '".$getUserTimezone."'), '%d/%m/%Y') as created"
            );
        
        $this->db->select($arrGet);
        $this->db->where($arrwhere);
        $this->db->join(TBL_PREFIX.'user AS u', 'n.created_by = u.id AND u.archive=0','left');
		$query = $this->db->get(TBL_PREFIX.'notification AS n');
        if($query->num_rows() >0){
			$data = $query->row_array();
			
			if($data["created_by_type"] == 1){
				$this->db->select("CONCAT_WS(' ', p.firstname, p.lastname) AS created_by");
				$this->db->where(array("u.id" => $data["created_by"],"u.archive" =>0));
				$this->db->join(TBL_PREFIX.'person AS p','p.id=u.person_id AND p.type=1 AND p.archive=0', 'left');
				$userQuery = $this->db->get(TBL_PREFIX.'user AS u');
				$user = $userQuery->row_array();
				$data["created_by"] = isset($user['created_by'])?$user['created_by']:'User';
			}else{
				$this->db->select("CONCAT_WS(' ', p.firstname, p.lastname) AS created_by");
				$this->db->where(array("u.id" => $data["created_by"],"u.archive" =>0));
				$this->db->join(TBL_PREFIX.'person AS p','p.id=u.person_id AND p.type=3 AND p.archive=0', 'left');
				$userQuery = $this->db->get(TBL_PREFIX.'admin_user AS u');
				$user = $userQuery->row_array();
				$data["created_by"] = isset($user['created_by'])?$user['created_by']:'System';
			}
            return $data;
        }else{
            return false;
        }
    }
    

	public function delete_notification_model($request_data, $user_id){
        $notification_id = $request_data->notification_id;
		$updateNotification = $this->CommonModel->UpdateData(
			['id'=>$notification_id, 'archive'=>0],
			['archive'=>1,'updated'=>create_date_store_in_db()],
			TBL_PREFIX.'notification'
		);
		if($updateNotification > 0){
			return true;
		}
		return false;
    }
    
    public function read_all_notification_model($request_data){
        $user_id = $request_data->user_id;
        $created = create_date_store_in_db();
		$arrwhere = array('n.alert_type' => 1,'n.user_id' => $user_id,'n.created_by_type' => 2,'n.archive' => 0);
		$this->db->where($arrwhere);
		$this->db->update(TBL_PREFIX.'notification AS n', array('is_read'=> 1, 'updated' => $created));
		if($this->db->affected_rows() > 0){
			$status= true;
		}else{
			$status= false;
		}
		return $status;
    }

    public function header_data_model($user_id){
		$result=array();
		$this->db->select("COUNT(DISTINCT(n.id)) AS notification");
        $this->db->where(array("n.alert_type" => 1, "n.user_id =" => $user_id, "n.is_read" => 2, "n.created_by_type" => 2,"n.archive" => 0));
        $this->db->from(TBL_PREFIX.'notification as n');
		$query = $this->db->get();
		$unreadNotification = $query->row();
		$userProfileImage = $this->get_profile_img_for_header_data($user_id); 
		$result['count_unread_notification'] = isset($unreadNotification->notification)?$unreadNotification->notification:0;
		$result['profile_image'] = !empty($userProfileImage)?$userProfileImage:'/images/user.svg';
		return $result;
	}

	public function get_profile_img_for_header_data($user_id){
		$whereArr=['status'=>1,'archive'=>0, 'id'=>$user_id];
		$result = $this->CommonModel->getDataWhere(['profile_image'], $whereArr, TBL_PREFIX.'user', ['result_array'=>3]);
		$image='';
		$filename = USER_SMALL_IMAGE.$result[0]->profile_image;
		if (file_exists($filename) && is_file($filename)) {
		   $image = $result[0]->profile_image;
		} 
		return $image;
	}
}
