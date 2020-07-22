<?php
use Gufy\PdfToHtml\Config;
require_once APPPATH.'third_party/pdf_to_html_converter/vendor/autoload.php';

function convertPdfToHtml($pdfFileName,$pdfPath,$storePath)
{
	error_reporting(0);
	make_path($storePath);
	Config::set('pdftohtml.bin', PDF_TO_HTML_BIN);
	Config::set('pdfinfo.bin', PDF_TO_HTML_INFO);
	Config::set('pdftohtml.output', $storePath);
	$pdf = new Gufy\PdfToHtml\Pdf($pdfPath);
	if (!empty($pdf->getInfo())) {
			 $pdf->html();
		  $total_pages = $pdf->getPages();
			$html_files = array();
			for ($i = 1;$i <= $total_pages;$i++) {
       array_push($html_files, $storePath.$pdfFileName. '-' . $i . '.html');
		 }
		 return $html_files = implode(",",$html_files);
	} else {
		return false;
	}
}


function checkStatementType($pdfFileName,$id){
	error_reporting(0);
	if(!empty($pdfFileName)){
		
		$fileName = explode('.pdf',$pdfFileName['file'])[0];
		$pdfPath=  FCPATH . USER_ATTACHMENT_PATH.$pdfFileName['file'];
		$storePath=  FCPATH . USER_ATTACHMENT_PATH.'html/';
		Config::set('pdftohtml.bin', PDF_TO_HTML_BIN);
		Config::set('pdfinfo.bin', PDF_TO_HTML_INFO);
		Config::set('pdftohtml.output', $storePath);
		$pdf = new Gufy\PdfToHtml\Pdf($pdfPath);
		  
		if (!empty($pdf->getInfo())) {
				$pdf->html();
				$total_pages = $pdf->getPages();
				$html_files = array();
				$alldata=array();
				$row=array();
				for ($i = 1;$i <= $total_pages;$i++) {
					array_push($html_files, $storePath.$fileName. '-' . $i . '.html');
			}
			$html_all_files =  $html_files;
			 
			require_once APPPATH . 'classes/FindStatementType.php';
			$statementCheck= new FindStatementType($html_all_files,$id);
		 
			$stmtDetails = $statementCheck->findStmtType();
			if(!empty($stmtDetails) && $stmtDetails['status']){
				if($stmtDetails['statementType']=='Bank'){
						$tableName = TBL_PREFIX.'statement';
						$row['statement_file_name'] = $pdfFileName['file'];
						$row['user_id'] = $id;
						$row['change_status'] = 1;
						$row['status'] = 0;
						$row['source_type'] = 3;
						$row['created'] = create_date_store_in_db();
					$alldata[$tableName] = $row;
				} else {
						$tableName = TBL_PREFIX.'invoice';
						$row['invoice_file'] = $pdfFileName['file'];
						$row['user_id'] = $id;
						$row['change_status'] = 1;
						$row['status'] = 0;
						$row['source_type'] = 3;
						$row['created'] = create_date_store_in_db();
						$alldata[$tableName] = $row;
				}
				 
				$newFilePath = user_directory($stmtDetails['storePath'],$id);
				rename($pdfPath,$stmtDetails['storePath'].$newFilePath.'/'.$pdfFileName['file']);
				return $alldata;
			}
			return true;
		} else {
			return false;
		}
	}
}

function encrypyted_pdf_to_decrypt($targetPath = FCPATH.'unencrypted.pdf',$sourcetPath = FCPATH.'sample-protected.pdf',$userPassword =123456){
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	$command = 'ghostscript -dSAFER -dBATCH -dNOPAUSE -sDEVICE=pdfwrite -dPDFSTOPONERROR -sPDFPassword='.$userPassword.' -sOutputFile='.$targetPath.' '.$sourcetPath.'';
	exec($command,$output);
	if(!empty($output) && !preg_grep('/^Error:\s.*/', $output)){
		return true;
	} else{
		return false;
	}
}

function check_pdf_is_encrypted($pdfPath= FCPATH.'test.pdf'){
	$filename= $pdfPath; 
	$handle = fopen($filename, "r");
	$contents = fread($handle, filesize($filename));
	fclose($handle);
	$res = false;
	if (stristr($contents, "/Encrypt")) {
		$res = true;
	}  
	return  $res;
} 

?>