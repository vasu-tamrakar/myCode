<?php

require_once APPPATH . 'classes/StatementCheck.php';
require_once APPPATH . 'classes/CreditCardStmtRead.php';
require_once APPPATH . 'classes/BankStmtRead.php';
require_once APPPATH . 'classes/InvoiceStmtRead.php';

class StatementCheck {
  var $allFiles;
  var $statementId;
  var $statementFor;

  function __construct($htmlFiles,$id){
    $this->allFiles = $htmlFiles;
    $this->statementId = $id;
  }

  function checkTypeStmt(){

      //  $files = explode(",",$this->allFiles);
        //echo FCPATH.$this->allFiles[0];
        $doc = new DOMDocument();
        @$doc->loadHTMLFile($this->allFiles[0]);
        $tags   = $doc->getElementsByTagName('p');
        foreach($tags as $tag){
        // echo $tag->nodeValue.'<br/>';
          if (strpos($tag->nodeValue, 'Credit Card') !== false
            ||  strpos($tag->nodeValue, 'Regalia MasterCard') !== false
            ||  strpos($tag->nodeValue, 'PLATINUM STATEMENT') !== false
            ) {
              $this->statementFor = '2'; // Credit Card

          } else if (strpos($tag->nodeValue, 'Bank Statement') !== false
          || strpos($tag->nodeValue, 'axisbank') !== false
          || strpos($tag->nodeValue, 'Axis Account') !== false
          || strpos($tag->nodeValue, 'icicibank') !== false
          ) {
              $this->statementFor = '1'; // Bank 

          } else if (strpos($tag->nodeValue, 'Invoice') !== false || strpos($tag->nodeValue, 'invoice') !== false) {
              $this->statementFor = 'Invoice';
          }
        }
      //  echo 'll'.$stmtIs; exit;
        switch ($this->statementFor) {
          case '2':
            return $this->readCCStatement();
            break;
          case '1':
           return $this->readBKStatement();
            break;
          case 'Invoice':
            return   $this->readINStatement();
            break;
          case 'Nothing':
                echo json_encode(array('status'=>false,'msg'=>'Not Get which statement'));
            break;

          default:
            break;
        }
  }
  function readCCStatement(){
    $cC = new CreditCardStmtRead($this->allFiles,$this->statementId,$this->statementFor);
    return $cC->readStmt();
  }

  function readBKStatement(){
    $bK = new BankStmtRead($this->allFiles,$this->statementId,$this->statementFor);
    return $bK->readStmt();
  }

  function readINStatement(){
    $iN = new InvoiceStmtRead($this->allFiles,$this->statementId,$this->statementFor);
   return $iN->readStmt();
  }


}
?>
