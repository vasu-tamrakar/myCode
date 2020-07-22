<?php

defined('BASEPATH') or exit('No direct script access allowed');

class BasicModel extends CI_Model {

    public function __construct() {
        // Call the CI_Model constructor
        parent::__construct();
        $this->load->database();    // Load database
    }

    public function getRow($table_name = '', $columns = array(), $id_array = array()) {
        if (!empty($columns)) :
            $all_columns = implode(",", $columns);
            $this->db->select($all_columns);
        endif;
        if (!empty($id_array)) :
            foreach ($id_array as $key => $value) {
                $this->db->where($key, $value);
            }
        endif;
        $query = $this->db->get( $table_name);

        //echo last_query();
        if ($query->num_rows() > 0)
            {
                $res = $query->row();
                return $res;
            }
            else
                return FALSE;
        }

    // Function for getting records from table with where condition
        function getRecordWhere($table, $column = '', $where = '') {
            if ($column != '') {
                $this->db->select($column);
            } else {
                $this->db->select('*');
            }
            $this->db->from($table);
            if ($where != '') {
                $this->db->where($where);
            }
            $query = $this->db->get();
            $res = $query->row();
            return $res;
        }

    // Function for getting records from table with where condition and order by
        function getRecordWhereOrderby($table, $column = '', $where = '', $orderby = '', $direction = 'ASC') {
            if ($column != '') {
                $this->db->select($column);
            } else {
                $this->db->select('*');
            }
            $this->db->from(TBL_PREFIX . $table);
            if ($where != '') {
                $this->db->where($where);
            }
            if ($orderby != '') {
                $this->db->order_by($orderby, $direction);
            }

            $query = $this->db->get();
            return $query->result();
        }

    // Function for inserting records
    function insertRecords($table, $data, $multiple = FALSE) {
        if ($multiple) {
            $this->db->insert_batch($table, $data);
        } else {
            $this->db->insert($table, $data);
        }
        return $this->db->insert_id();
    }

    // Function for updating records
    function updateRecords($table, $data, $where) {

        $this->db->where($where);
        $this->db->update($table, $data);

        return $this->db->affected_rows();
    }

    // Function for deleting records
        function deleteRecords($table, $where) {
            $this->db->delete($table, $where);
            if (!$this->db->affected_rows()) {
                return FALSE;
            } else {
                return TRUE;
            }
        }

        public function getResult($table_name = '', $id_array = '', $columns = array(), $order_by = array(), $result_type = '') {
            if (!empty($columns)) :
                $all_columns = implode(",", $columns);
                $this->db->select($all_columns);
            endif;
            if (!empty($order_by)) :
                $this->db->order_by($order_by[0], $order_by[1]);
            endif;

            if (!empty($id_array)) :
                foreach ($id_array as $key => $value) {
                    $this->db->where($key, $value);
                }
            endif;
            $query = $this->db->get($table_name);
            if ($query->num_rows() > 0)
                return !empty($result_type) ? $query->result_array() : $query->result();
            else
                return FALSE;
        }

        public function insertUpdateBatch($action = 'insert', $table_name = '', $data = [], $update_base_column_key = '') {
            if ($action == 'insert' && !empty($table_name) && !empty($data) && is_array($data)) {
                $this->db->insert_batch(TBL_PREFIX . $table_name, $data);
                return true;
            } elseif ($action == 'update' && !empty($table_name) && !empty($update_base_column_key) && !empty($data) && is_array($data)) {
                $this->db->update_batch(TBL_PREFIX . $table_name, $data, $update_base_column_key);
                return true;
            } else {
                return false;
            }
        }

        public function sub_query_call($table_names,$id){
            $foreign_key ='person_id';
            if(!empty($table_names)){
                foreach($table_names as $table){
                    $this->db->where($foreign_key, $$id);
                    $query = $this->db->get($table_name); 
                    if ($query->num_rows() > 0)
                    return !empty($result_type) ? $query->result_array() : $query->result();
                    else
                    return FALSE;
                }
            }
        }

    }
