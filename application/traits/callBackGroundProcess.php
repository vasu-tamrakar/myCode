<?php
trait callBackGroundProcess {
	    
    protected function call_background_process($methodName = '', $extraParms = [])
    {
        $this->load->model('CommonModel');
        $methodNameAllowed = [
            'invoice_ai_review_mapping',
            'statement_ai_review_mapping',
            'forecasting_invoice',
            'forecasting_statement',
            'fetch_statement'
        ];
        $interValLimit = isset($extraParms['interval_minute']) && (int) $extraParms['interval_minute'] > 0 && (int) $extraParms['interval_minute'] <= 59 ? (int) $extraParms['interval_minute'] : 25;
        $methodCallName = isset($extraParms['method_call']) && !empty($extraParms['method_call']) ? $extraParms['method_call'] : '';
        $methodCallParms = isset($extraParms['method_params']) && !empty($extraParms['method_params']) && is_array($extraParms['method_params']) ? $extraParms['method_params'] : [];
        $startTime = create_date_store_in_db();
        $tableNameWithPreFix = TBL_PREFIX . 'cron_status';
        $tableName = 'cron_status';
        if (!empty($methodName) && in_array($methodName, $methodNameAllowed)) {
            $this->db->select('id');
            $this->db->from($tableNameWithPreFix);
            $this->db->where("last_date_time> NOW() - INTERVAL " . $interValLimit . " MINUTE", null, false);
            $this->db->where("status", 0);
            $this->db->where("method_name", $methodName);
            $result = $this->db->get();
            if ($result->num_rows() == 0) {
                $row = $this->CommonModel->insertData(array('method_name' => $methodName, 'status' => 0, 'created_date' => $startTime),$tableNameWithPreFix);
                if (!empty($methodCallName) && method_exists($this, $methodCallName)) {
                    $methodCallParms = array_merge(['cron_id' => $row], ['extra' => $methodCallParms]);
                    $response = call_user_func_array(array($this, $methodCallName), $methodCallParms);
                } else {
                    $response = ['status' => false, 'error' => 'cron call method not exists.'];
                }
            } else {
                $response = ['status' => false, 'error' => 'cron process already run.'];
            }
        } else {
            $response = ['status' => false, 'error' => 'cron method not allowed.'];
        }
        return $response;
    }
}
?>