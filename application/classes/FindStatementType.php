<?php

class FindStatementType {
  var $allFiles;

  var $statementType;
  function __construct($htmlFiles,$id){
    $this->allFiles = $htmlFiles;

  }
  function findStmtType(){
        $doc = new DOMDocument();
        @$doc->loadHTMLFile($this->allFiles[0]);
        $tags   = $doc->getElementsByTagName('p');
        foreach($tags as $tag){
        // echo $tag->nodeValue.'<br/>';
        $check1  = $this->get_single_value($tags, 'Invoice', '1');
        $check2  = $this->get_single_value($tags, 'invoice', '1');
          if(!empty($check1) || !empty($check2)) {
            $statementFor = 'Invoice';
          } else {
             $statementFor = 'Bank';
          }
        }
        if(!empty($statementFor)){
          if($statementFor=='Bank'){
            $response = array(
            'status'=>true,
            'storePath'=>USER_STATEMENT_PATH,
            'statementType'=>$statementFor
            );
          } else {
            $response = array(
            'status'=>true,
            'storePath'=>USER_INVOICE_PATH,
            'statementType'=>$statementFor
            );
          }
        } else {
          $response = array(
          'status'=>false,
          'storePath'=>'',
          'statementType'=>'Not able to find which statment'
          );
        }
        unlink($this->allFiles);
        return $response;
  }

  function get_single_value($data, $reading_start, $counter)
  {
      $required_field = '';
      $cheak_required_field = false;
      foreach ($data as $inv)
      {
          if (strpos($inv->nodeValue, $reading_start) !== false)
          {
              $cheak_required_field = true;
              $cnt_val = 0;
          }
          if ($cheak_required_field && $cnt_val >= $counter)
          {
              if (!$required_field)
              {
                  $required_field = trim($inv->nodeValue);
              }
          }
          if ($cheak_required_field)
          {
              $cnt_val++;
          }
      }
      return $required_field;
  }


}
?>
