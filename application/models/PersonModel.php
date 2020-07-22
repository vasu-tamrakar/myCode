<?php

defined('BASEPATH') || exit('No direct script access allowed');

class PersonModel extends CI_Model {
 
	private function get_person_email_sub_query() {
		
        $this->db->select('pe.email');
        $this->db->from(TBL_PREFIX.'person_email pe');
        $this->db->where("pe.person_id=p.id AND pe.archive=p.archive", NULL, false);
        $this->db->where('pe.archive', 0);
        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }

    private function get_person_phone_sub_query() {
        $this->db->select('ph.phone');
        $this->db->from(TBL_PREFIX.'person_phone ph');
        $this->db->where("ph.person_id=p.id AND ph.archive=p.archive", NULL, false);
        $this->db->where('ph.archive', 0);
        $this->db->limit(1);
		return $this->db->get_compiled_select();
 	}

    private function get_person_country_sub_query() {
        $this->db->select('pa.country_id');
        $this->db->from(TBL_PREFIX.'person_address pa');
        $this->db->where("pa.person_id=p.id AND pa.archive=p.archive", NULL, false);
        $this->db->where('pa.archive', 0);
        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }
	
	private function get_person_address_sub_query() {
        $this->db->select('pa.street');
        $this->db->from(TBL_PREFIX.'person_address pa');
        $this->db->where("pa.person_id=p.id AND pa.archive=p.archive", NULL, false);
        $this->db->where('pa.archive', 0);
        $this->db->limit(1);
		return $this->db->get_compiled_select();
	}

	private function get_person_city_sub_query() {
        $this->db->select('pa.city');
        $this->db->from(TBL_PREFIX.'person_address pa');
        $this->db->where("pa.person_id=p.id AND pa.archive=p.archive", NULL, false);
        $this->db->where('pa.archive', 0);
        $this->db->limit(1);
		return $this->db->get_compiled_select();
	}


	private function get_person_state_sub_query() {
        $this->db->select('pa.state');
        $this->db->from(TBL_PREFIX.'person_address pa');
        $this->db->where("pa.person_id=p.id AND pa.archive=p.archive", NULL, false);
        $this->db->where('pa.archive', 0);
        $this->db->limit(1);
		return $this->db->get_compiled_select();
	}

	private function get_person_postal_sub_query() {
        $this->db->select('pa.postal');
        $this->db->from(TBL_PREFIX.'person_address pa');
        $this->db->where("pa.person_id=p.id AND pa.archive=p.archive", NULL, false);
        $this->db->where('pa.archive', 0);
        $this->db->limit(1);
		return $this->db->get_compiled_select();
	}


    private function get_country_code_sub_query() {
        $this->db->select('c.country_code');
        $this->db->from(TBL_PREFIX.'person_address pa');
        $this->db->where("pa.person_id=p.id AND pa.archive=p.archive", NULL, false);
        $this->db->join(TBL_PREFIX.'country c', "c.id=pa.country_id AND c.archive=0");
        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }

    private function get_country_name_sub_query() {
        $this->db->select('c.country_name');
        $this->db->from(TBL_PREFIX.'person_address pa');
        $this->db->where("pa.person_id=p.id AND pa.archive=p.archive", NULL, false);
        $this->db->join(TBL_PREFIX.'country c', "c.id=pa.country_id AND c.archive=0");
        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }

    private function get_user_status_case_query($type=1){

        if($type == 1){
            $this->db->select("CASE WHEN u.status=0 THEN 'Pending'  WHEN u.status=1 THEN 'Active' WHEN u.status=2 THEN 'Inactive' ELSE 'N/A' END");
        }
        else{
           $this->db->select("u.status"); 
        }
        $this->db->from(TBL_PREFIX.'user u');
        $this->db->where("u.person_id=p.id AND u.archive=p.archive", NULL, false);
        $this->db->where('u.archive', 0);
        $this->db->limit(1);
        return $this->db->get_compiled_select();
  }


	public function get_person_profile_image($setPersonType) {
		if($setPersonType==3){
			$tableName = TBL_PREFIX.'admin_user u';
		}else {
			$tableName = TBL_PREFIX.'user u';
		}
		$this->db->select('u.profile_image');
        $this->db->from($tableName);
        $this->db->where("u.person_id=p.id AND u.archive=p.archive", NULL, false);
        $this->db->limit(1);
		return $this->db->get_compiled_select();
    }
	 
	public function get_person_details_by_id($personId,$setPersonType, $request_body=null) {

        $getUserTimezone = $request_body->time_zone_mysql?? '+00:00';


        $sub_query_person_email = $this->get_person_email_sub_query();
        $sub_query_person_phone = $this->get_person_phone_sub_query();
        $get_person_country_sub_query = $this->get_person_country_sub_query();
		$sub_query_person_address = $this->get_person_address_sub_query();
		$sub_query_person_state = $this->get_person_state_sub_query();
		$sub_query_person_city = $this->get_person_city_sub_query();
		$sub_query_person_postal = $this->get_person_postal_sub_query();
		$sub_query_person_image = $this->get_person_profile_image($setPersonType);
        $get_country_code_sub_query  = $this->get_country_code_sub_query();
        $get_country_name_sub_query  = $this->get_country_name_sub_query();

        $get_user_status_label_case_query = $this->get_user_status_case_query(1);
        $get_user_status_id_case_query = $this->get_user_status_case_query(2);

        $this->db->select(
                array(
            "(" . $sub_query_person_email . ") as email",
            "(" . $sub_query_person_phone . ") as phone",
		   "(" . $sub_query_person_postal . ") as postal",
		   "(" . $sub_query_person_state . ") as state",
		   "(" . $sub_query_person_city . ") as suburb",
			"(" . $sub_query_person_address . ") as address",
            "(".$get_person_country_sub_query.") as country",
			"(" . $sub_query_person_image . ") as profile_image",
            "(".$get_country_code_sub_query.") as country_code",
            "(".$get_country_name_sub_query.") as country_name",
            "(".$get_user_status_label_case_query.") as status_label",
            "(".$get_user_status_id_case_query.") as status",
            "p.firstname",
            "p.lastname",
             "DATE_FORMAT(CONVERT_TZ(p.created,'+00:00', '".$getUserTimezone."'), '%d/%m/%Y') as created"

            ), false
        );
        $this->db->from(TBL_PREFIX.'person p');
        $this->db->where('p.id', $personId);
        $this->db->where('p.archive', 0);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
		$result = $query->num_rows() > 0 ? $query->row_array() : [];

        if(empty($result)){
            $response_ary = array('status'=>false, 'msg' => "No Record Found" );
            echo json_encode($response_ary);
            exit();
        }
       
        $image = '/images/user.svg';
		if (!empty($result) && !empty($result['profile_image']) && isset($result['profile_image'])) {
            if($setPersonType==3){
                $filename1 = UPLOADS.'admin_profile/'.$result['profile_image'];
                if (file_exists($filename1) && is_file($filename1)) {
                   $image = $result['profile_image'];
                } 
            } else {
                $filename2 = UPLOADS.'user_profile/'.$result['profile_image'];
                if (file_exists($filename2) && is_file($filename2)) {
                   $image = $result['profile_image'];
                }    
            }
        }
        $result['profile_image'] =   $image; 
        return $result;
    }
    
	 

}
