<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class MpdfLib {
    
    function m_pdf()
    {
        $CI = & get_instance();
        log_message('Debug', 'mPDF class is loaded.');
        #die('sssss');
    }
    function load($param=NULL)
    {
        include_once APPPATH.'/third_party/mpdf/mpdf60/mpdf.php';
         
        if ($param == NULL)
        {
            $param = '"en-GB-x","A4","","",10,10,10,10,6,3';          		
        }
        return new mPDF($param);
    }
}