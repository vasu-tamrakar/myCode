<?php
class BankStmtRead {
  var $allFiles;
  var $statementId;
  var $statementFor;

  function __construct($htmlFiles,$id,$name){
    $this->allFiles = $htmlFiles;
    $this->statementId = $id;
    $this->statementFor = $name;
  }
  function readStmt(){
          $whichBank = $this->whichBank();
         switch ($whichBank) {
          case '1':
            return $result =  $this->axisbank_read($id);
            break;
          case '2':
            return $result = $this->icicbank_read($id);
          default:
            // code...
            break;
        }
  }

  function cleanHtml($completeFile){
    $file = fopen($completeFile, "r") or die("Unable to pod open file!");
    $data = fread($file, filesize($completeFile));
    fclose($file);

    $data = str_replace("<br>", " ", $data);
    $data = str_replace("&#160;", "</p><p>", $data);
    // $data = str_replace("<p> </p>", "<p></p>", $data);
    // $data = str_replace("<p> </p>", "<p></p>", $data);
    $data = str_replace("<p></p>", "", $data);
    $data = str_replace("<b>", "", $data);
    $data = str_replace("</b>", "", $data);
    $data = str_replace("*", "", $data);
    $data = trim($data, "");

    $file = fopen($completeFile, "w") or die("Unable to open file!");
    fwrite($file, $data);
    fclose($file);
    return $file;
  }

  function axisbank_read(){
    //echo $total = count($this->allFiles);
    foreach($this->allFiles as $k => $file){
  //  echo $completeFile=FCPATH.$file;
    //  $this->cleanHtml($completeFile);
      $doc = new DOMDocument();
      $doc->loadHTMLFile($file);
      $tags =  $doc->getElementsByTagName('p');
      $stmt=array();
      $findBalance=false;
      $findTrns=false;
      $lno=0;
      $li=0;
      $j=0;
      $i=0;
      $stri='';
      $trans_date1=array();
      $dateValues=array();
      foreach ($tags as $key => $tag) {
        if($k==0){
          if(strpos($tag->nodeValue,'From :')!==false || strpos($tag->nodeValue,'To :')!==false){
            $d = explode('(',trim($tag->nodeValue));
            $v = explode(')',trim($d[1]));
            $repl = str_replace(' ','',$v[0]);
            $x = explode('From:',$repl);
            $y = explode('To:',$x[1]);
            $from_date=str_replace(' ','',$y[0]);
            $to_date=$y[1];
          }
        }

        if(strpos($tag->nodeValue, 'Detailed Statement for a/c no')!==false){
              $match_val = explode('to',$tag->nodeValue);
              preg_match('@(\b\d{1,2}-\d{1,2}-\d{4}\b)@',$match_val[0], $fmFrom);
              preg_match('@(\b\d{1,2}-\d{1,2}-\d{4}\b)@',$match_val[1], $fmTo);
              $from_date=$fmFrom[0];
              $to_date=$fmTo[0];
        }

        //other format of statement
         if(strpos($tag->nodeValue,'Opening Balance')!==false){
            $findCTrns = true;
         }
         if($findCTrns){
             $match = preg_match('@(\b\d{1,2}-\d{1,2}-\d{4}\b)@', $tag->nodeValue, $transactionDate);
             if($match==1)
             {
                //  var_dump($tag->nodeValue);
              //    $desc_find = explode(" ",$tag->nodeValue);
                    //$desc_find = explode(" ", $tag->nodeValue);

                   $trans_date[] =  date('Y-m-d',strtotime($transactionDate[1]));
                   if($trans_date){
                    $trans_desc[] = trim($tags[$i+1]->nodeValue);
                    $trans_debit[] = trim($tags[$i+2]->nodeValue);
                    $stri =$tags[$i+4]->nodeValue;
                    $exp = explode(' ',$stri);
                      $checkMatch =  preg_match('@(\b\d{1,2}-\d{1,2}-\d{4}\b)@', $exp[0], $creditV);
                      if($exp && $exp[0]!='TRANSACTION' && $exp[0]!='TOTAL' && !$checkMatch){
                        $credit = $exp[0];
                      }else {
                        $credit = $exp[1];
                      }
                      $trans_credit[] = trim($credit);
                      if(!empty($trans_debit)) {
                          $stmt_tr_type[]='2'; //debit
                      } else {
                         $stmt_tr_type[]='1'; //credit
                      }
                  }
             }
         }


        if(strpos($tag->nodeValue,'OPENING BALANCE')!==false || strpos($tag->nodeValue,'Legends :')!==false){
          $findTrns = true;
        }
          //$nodeTypes[] = strlen($tag->nodeValue).'-----'.(preg_match('/\s/',$tag->textContent))?'Yes':'No';
          if(strpos($tag->nodeValue,'CLOSING BALANCE')!==false){
            $findTrns = true;
          }
          if($findTrns){
            $match = preg_match('@(\b\d{1,2}-\d{1,2}-\d{4}\b)@', $tag->nodeValue, $transactionDate);
            if($match==1)
            {
              $li =  $tag->getLineNo();
              $trans_date[] = date('Y-m-d',strtotime($transactionDate[1]));
               if($trans_date){
                $trans_desc[] = trim($tags[$i+1]->nodeValue);
                $trans_debit[] = trim($tags[$i+2]->nodeValue);
                $stri =$tags[$i+4]->nodeValue;
                $exp = explode(' ',$stri);
                  $checkMatch =  preg_match('@(\b\d{1,2}-\d{1,2}-\d{4}\b)@', $exp[0], $creditV);
                  if($exp && $exp[0]!='TRANSACTION' && $exp[0]!='TOTAL' && !$checkMatch){
                    $credit = $exp[0];
                  }else {
                    $credit = $exp[1];
                  }
                  $trans_credit[] = trim($credit);
                  if(!empty($trans_debit)) {
                      $stmt_tr_type[]='2'; //debit
                  } else {
                     $stmt_tr_type[]='1'; //credit
                  }
              }
            }
          }
          $li++;
          $i++;
      }
    }

    // echo '<pre>'; print_r($trans_date);
    //  exit;
     //echo '<pre>'; print_r($trans_desc);
     // echo '<pre>'; print_r($trans_debit);
     // echo '<pre>'; print_r($trans_credit);

      foreach ($trans_date  as $f => $v) {
            $dateValues = array(
             'transaction_date' =>$v,
             'description' => $trans_desc[$f],
             'credit_amt' => $trans_credit[$f],
             'debit_amt' => $trans_debit[$f],
             'transaction_type' => $stmt_tr_type[$f],
             'statement_id' =>$this->statementId,
             'status'=>1
           );
           $stmt['transaction'][]=$dateValues;
         }
    $stmt['statement']['issue_date'] =  date('Y-m-d',strtotime($from_date));
    $stmt['statement']['due_date'] = date('Y-m-d',strtotime($to_date));;
    $stmt['statement']['statement_type'] = 'Axis Bank';
    $stmt['statement']['statement_for'] = $this->statementFor;
    $stmt['statement']['statement_number'] = time();
  //  $stmt['statement']['balance'] = $balance;
  //  echo '<pre>'; print_r($stmt); exit;
      return $stmt;
  }

  function strpos_arr($haystack, $needle) {
    if(!is_array($needle)) $needle = array($needle);
    foreach($needle as $what) {
        if(($pos = strpos($haystack, $what))!==false) return $pos;
    }
    return false;
  }

  function icicbank_read(){
      foreach ($this->allFiles as $key => $file) {
          //$file = './uploads/user/2/21/20200413_2_1586750497-1.html';
          // $completeFile=FCPATH.$file;
          // $this->cleanHtml($completeFile);
          $doc = new DOMDocument();
          $doc->loadHTMLFile($file);
          $tags =  $doc->getElementsByTagName('p');
          $trans_start=false;
          $j=0;
          foreach ($tags as $key => $tag) {
          //echo  $tag->nodeValue;
           if(strpos($tag->nodeValue,'Statement of Transactions')!==false){
             $trans_start=true;
           }

           if($trans_start){
             $match = preg_match('@(\b\d{1,2}-\d{1,2}-\d{4}\b)@', $tag->nodeValue, $transactionDate);
             if($match==1){
                $trans_date[] = date('Y-m-d',strtotime($tag->nodeValue)) ;
                //$transactionDate[0];
                if($trans_date){

                    $firstChar1 = strlen(mb_substr($tags[$j+2]->nodeValue, 0, 1, "UTF-8"));
                    if($firstChar1!=2){
                      $trans_desc[] = trim($tags[$j+2]->nodeValue);
                        $trans_credit[] = '';
                    } else {
                      $trans_desc[] ='';
                      $trans_credit[] = trim($tags[$j+2]->nodeValue);
                      $stmt_tr_type[]='1'; //credit
                    }


                     $firstChar2 = strlen(mb_substr($tags[$j+2]->nodeValue, 0, 1, "UTF-8"));
                     if($firstChar2!=2){
                      $trans_credit[] = '';
                     }

                     $firstChar3 = strlen(mb_substr($tags[$j+3]->nodeValue, 0, 1, "UTF-8"));
                     if($firstChar3!=2){
                      $trans_debit[] = '';
                      } else {
                        $trans_debit[] =  trim(str_replace(' ','',$tags[$j+3]->nodeValue));
                        $stmt_tr_type[]='2';//debit
                      }
                }
             }
           }
           $j++;
          }
        }


        foreach ($trans_date  as $f => $v) {
              $dateValues = array(
               'transaction_date' =>$v,
               'description' => $trans_desc[$f],
               'credit_amt' => $trans_credit[$f],
               'debit_amt' => $trans_debit[$f],
               'transaction_type' => $stmt_tr_type[$f],
               'statement_id' =>$this->statementId,
               'status'=>1
             );
             $stmt['transaction'][]=$dateValues;
           }
      // $stmt['statement']['from_date'] =  $from_date;
      // $stmt['statement']['to_date'] = $to_date;
      $stmt['statement']['statement_type'] = 'ICICI Bank';
      $stmt['statement']['statement_for'] = $this->statementFor;
      $stmt['statement']['statement_number'] = time();
      return $stmt;
  }

  function whichBank() {
    $type ='';
    foreach ($this->allFiles as $key => $file) {
      $doc = new DOMDocument();
      $doc->loadHTMLFile($file);
      $tags =  $doc->getElementsByTagName('p');
    }
    foreach($tags as $k => $tag){
         $string = $tag->nodeValue;
        if(strpos($string, 'axisbank') !== false){
            $type= 1;
        } else if(strpos($string, 'icicibank') !== false){
            $type= 2;
        } else if(strpos($string, 'hdfcbank') !== false){
            $type= 3;
        }
    }
    return $type;
  }

}
?>
