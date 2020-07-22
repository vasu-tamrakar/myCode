<?php

defined('BASEPATH') OR exit('No direct script access allowed');

//class Master extends MX_Controller
class Common_model extends CI_Model {

    function __construct() {

        parent::__construct();
    }

   
    public function file_content_media($type, $file_name, $userId=0, $checkToken = 0, $extratParm = []) {
        $this->load->helper('file');
        $token = isset($extratParm['token']) ? $extratParm['token'] : '';
        $defaultImageShow = isset($extratParm['defaultImageShow']) ? $extratParm['defaultImageShow'] : '0';
        $loginType = isset($extratParm['login_type']) ? $extratParm['login_type'] : '0';
        $filePath = FCPATH;
        $permission_key = 0;

        if ($type == 're') {
            $filePath .= USER_REPORT_UPLOAD_PATH . $file_name;
        }

        if ($type == 'xls') {
            $filePath .= USER_EXPORT_PATH . $file_name;
        }
        //user profile image
        if ($type == 'u_prf') {
            $filePath .= USER_PROFILE_PATH . $file_name;
        }
        //user profile small image
        if ($type == 'u_prf_s') {
            $filePath .= USER_SMALL_IMAGE . $file_name;
        }
        //admin  profile image
        if ($type == 'a_prf') {
            $filePath .= ADMIN_PROFILE_PATH . $file_name;
        }
        //admin profile small image
        if ($type == 'a_prf_s') {
            $filePath .= ADMIN_SMALL_IMAGE . $file_name;
        }
        // Download unread pdf
        if ($type == 'ur_pdf') {
            $filePath .= USER_ATTACHMENT_PATH . $file_name;
        }

        if ($type == 'i_pdf' && $userId > 0) {
            $filePath .= USER_INVOICE_PATH .$userId.'/'.$file_name;
        }else if ($type == 'i_pdf' && $userId == 0) {
            $filePath .= USER_INVOICE_PATH .'/no_preview.pdf';
        }
        
        

        
        
        if ($checkToken == 1) {
            $checkTokenExtratParam = $loginType==1 ? ['user_type'=>'admin']:[];
            $response =token_check((object) ['token' => $token],$checkTokenExtratParam);
            if (!$response['status']) {
                return ['status' => false, 'msg' => 'Access denied'];
            }
        }
        $mimeType = '';
        $string = '';
        $status = true;
        if (is_file($filePath) && file_exists($filePath)) {
            $mimeType = get_mime_by_extension($filePath);
            header('content-type: ' . $mimeType);
            $string = read_file($filePath);
        } else if (!is_file($filePath) && $defaultImageShow == 1) {
            $filePath = FCPATH . NO_IMAGE_PATH;
            $mimeType = get_mime_by_extension($filePath);
            $string = read_file($filePath);
        } else {
            $status = false;
            $string = 'File not found';
        }

        return ['status' => $status, 'msg' => $string, 'mimetype' => $mimeType];
    }

    // for export 
	function export_as_excel($dataHearder = [], $dataRes = [], $extraParm = []) {
        $this->load->library("Excel");
        $object = new PHPExcel();
        $object->setActiveSheetIndex(0);
        $fileName = $extraParm['file_name'] ?? time() . 'export.xls';
		$fileDirPath = $extraParm['file_dir_path'] ?? FCPATH . USER_EXPORT_PATH . '/';
		$keySheet  =  $extraParm['sheet_name'] ?? 'statement';
        $column = 0;
        foreach ($dataHearder as $field) {
            $object->getActiveSheet()->setCellValueByColumnAndRow($column, 1, $field);
            $column++;
        }
        if (!empty($dataRes)) {
            $excel_row = 2;
            foreach ($dataRes as $row) {
				
                $columnRound = 0;
                foreach ($dataHearder as $key => $value) {
                    $object->getActiveSheet()->setCellValueByColumnAndRow($columnRound, $excel_row, $row[$key] ?? '');
                    $columnRound++;
                }
                $excel_row++;
            }
        }
        $lastColumn = getEcxcelColumnNameGetByIndex(count($dataHearder));
        $object->setActiveSheetIndex()
                ->getStyle('A1:' . $lastColumn . '1')
                ->applyFromArray(
                        array(
                            'fill' => array(
                                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                'color' => array('rgb' => 'C0C0C0:')
                            )
                        )
		);
		$object->setActiveSheetIndex()->setTitle("$keySheet");
        $object_writer = PHPExcel_IOFactory::createWriter($object, 'Excel5');
        $csv_fileFCpath = $fileDirPath . $fileName;
        $response = $object_writer->save($csv_fileFCpath);
        if (file_exists($csv_fileFCpath)) {
            return ['status' => true, 'filename' => $fileName];
        }
        return ['status' => false, 'error' => 'csv file not exist'];
    }
}
