<?php

const stmt_Date           = 11;
const stmt_DueDate        = 15;
const stmt_Type  = 'Hdfc Credit Card';

class CreditCardStmtRead {
  var $allFiles;
  var $statementId;
  var $statementFor;

  function __construct($htmlFiles,$id,$name){
    $this->allFiles = $htmlFiles;
    $this->statementId = $id;
    $this->statementFor=$name;
  }

  function readStmt(){
      $id =   $this->statementId;
      $completeData=array();
      $total = count($this->allFiles);
      if(!empty($this->allFiles)){
        $transactionDetails=false;
        $flag=false;
        $transaction_desc=array();
        $stmt_transaction_date=array();
        $stmt_transaction_amount=array();
        $stmt_transaction_desc=array();
        $i=1;
        $description='';
        foreach ($this->allFiles as $key => $file) {
        //  echo FCPATH.$file;
          $doc = new DOMDocument();
          $doc->loadHTMLFile($file);
          $tags =  $doc->getElementsByTagName('p');
          $j=0;

          $typeOfCreditCard = $this->ccFilter($tags);
          if($typeOfCreditCard==1){ //Regalia MasterCard Credit Card Statement
          foreach ($tags as $key2 => $tag) {
              if (strpos($tag->nodeValue, 'Transaction Description') !== false) {
                $transactionDetails=true;
                $flag=false;
                $cnt_val = 0;
              }
              $page = 'Page '.$i.' of '.$total;
              $pages = 'Page';
              if(strpos($tag->nodeValue, $pages) !== false) {
                 $flag=true;
                 $cnt_val = 0;
                 $line_no = $tag->getLineNo();

              }

              if(strpos($tag->nodeValue, 'Card No:  ') !== false) {
                 $flag=false;
              }



               if($flag){
                         $description = trim($tags[$j+1]->textContent);
                    //  echo strlen($description).'<br/>';
                      if(strlen($description)!=0 && strlen($description)!=2){
                        if($key<$total-1){
                          $pos = $this->strpos_arr($description,
                          array('Card No:  ',
                          'Transaction Description',
                          'Digitally signed',
                          'Date',
                          'Signature Not Verified',
                          'INFORMATION',
                          'Page',
                          ));

                          if ($pos === false) {
                              $description = str_replace('  ','',$description);
                              if($description!='  '){
                               $stmt_transaction_desc[$key][]  = $description;
                              }
                            }
                        }
                    }
               }
              if($transactionDetails){
                $match = preg_match('@(\b\d{1,2}/\d{1,2}/\d{4}\b)@', $tag->nodeValue, $transactionDate);
                if($match==1)
                {
                  $stmt_transaction_date[$key][] = date('Y-m-d', strtotime(strtr($transactionDate[0], '/', '-')));
                  $stmt_transaction_amount[$key][] = $tags[$j+1]->nodeValue;
                  if (strpos($tags[$j+1]->nodeValue, 'Cr') !== false) {
                      $stmt_tr_type[$key][]='1'; //credit
                  }else{
                      $stmt_tr_type[$key][]='2'; //debit
                  }
                }
              }
              $j++;
              if($transactionDetails && $line_no>1){
                $cnt_val++;
              }
          }

          $i++;
          if($key==0){
            $issue_date = explode(':', $tags[stmt_Date]->nodeValue)[1];
            $completeData['statement']['issue_date'] =   date('Y-m-d',strtotime(strtr($issue_date, '/', '-')));
            $completeData['statement']['statement_type'] = 'HDFC Bank - Regalia MasterCard';
            $completeData['statement']['statement_for'] = $this->statementFor;
            $completeData['statement']['due_date'] = date('Y-m-d',strtotime($tags[stmt_DueDate]->nodeValue));
            $completeData['statement']['statement_notes'] = 'read completed';
            $completeData['statement']['statement_number'] = time();
          }
          $dateValues = array();

        //  var_dump($stmt_transaction_date[$key]); exit;
          foreach($stmt_transaction_date[$key] as $ky => $v){
          //    $stmt_transaction_amount[$key][$ky];
              $dateValues = array(
                'transaction_date' => $v,
                'amount' => str_replace(',', '', $stmt_transaction_amount[$key][$ky]),
                'description' => array_reverse($stmt_transaction_desc[$key])[$ky],
                'transaction_type' =>$stmt_tr_type[$key][$ky],
                'statement_id' =>$id,
                'status'=>1
              );
              $completeData['transaction'][] = $dateValues;
          }
        //  var_dump($completeData); exit;

        // platinum_plus_credit_card
      } else if($typeOfCreditCard==2){
        foreach ($tags as $key2 => $tag) {
            if (strpos($tag->nodeValue, 'Transaction Description') !== false) {
              $transactionDetails=true;
              $flag=false;
              $cnt_val = 0;
            }
            $page = 'Page '.$i.' of '.$total;
            $pages = 'Page';
            if(strpos($tag->nodeValue, $pages) !== false) {
               $flag=true;
               $cnt_val = 0;
               $line_no = $tag->getLineNo();

            }

            // if(strpos($tag->nodeValue, 'Card No:  ') !== false) {
            //    $flag=false;
            // }
            //
            // if(strpos($tag->nodeValue, ':') !== false) {
            //    $flag=false;
            // }

             if($flag){
              //   if (strpos($tag->nodeValue, 'Domestic Transactions') === false||strpos($tag->nodeValue, 'International Transactions') === false) {
                      $description = trim($tags[$j+1]->textContent);
                    // echo ($description).'<br/>';
                    if(strlen($description)!=0 && strlen($description)!=2){
                      if($key<$total-1){
                        $pos = $this->strpos_arr($description,
                        array('Card No:  ',
                        'Transaction Description',
                        'Digitally signed',
                        'Date',
                        'Signature Not Verified',
                        'INFORMATION',
                        $page,
                        'Email',
                        'Name',
                        'In case any of your personal details',
                        ':',
                        'our Customer Service.'

                        // ':'
                        ));
                        if ($pos === false) {
                             $stmt_transaction_desc[$key][]  = $description;
                          }
                      }
                  }
              //  }
             }
            if($transactionDetails){
              $match = preg_match('@(\b\d{1,2}/\d{1,2}/\d{4}\b)@', $tag->nodeValue, $transactionDate);
              if($match==1)
              {
                $stmt_transaction_date[$key][] = date('Y-m-d', strtotime(strtr($transactionDate[0], '/', '-')));
                //date('Y-m-d',strtotime($transactionDate[0]));
                $stmt_transaction_amount[$key][] = $tags[$j+1]->nodeValue;
                if (strpos($tags[$j+1]->nodeValue, 'Cr') !== false) {
                    $stmt_tr_type[$key][]='1'; //credit
                }else{
                    $stmt_tr_type[$key][]='2'; //debit
                }

              }
            }
            $j++;
            if($transactionDetails && $line_no>1){
              $cnt_val++;
            }

        }

        if($key==0){
          $due_date = explode(':', $tags[30]->nodeValue);
          $issue_date = explode(':', $tags[26]->nodeValue);
          $completeData['statement']['issue_date'] =  date('Y-m-d',strtotime(strtr($issue_date[1], '/', '-')));
          $completeData['statement']['statement_type'] = 'HDFC Bank - Platinum Plus Credit Card';
          $completeData['statement']['statement_for'] = $this->statementFor;
          $completeData['statement']['due_date'] = date('Y-m-d',strtotime($due_date[0]));
        //  $completeData['statement']['statement_notes'] = 'read completed';
          $completeData['statement']['statement_number'] = time();
        }

        //var_dump($stmt_transaction_desc);

        $dateValues = array();

         foreach($stmt_transaction_date[$key] as $ky => $v){
             $stmt_transaction_desc[$key][$ky].'...<br/>';;
             $dateValues = array(
              'transaction_date' =>$v,
              'amount' => str_replace(',', '', $stmt_transaction_amount[$key][$ky]),
              'description' => array_reverse($stmt_transaction_desc[$key])[$ky],
              'transaction_type' =>$stmt_tr_type[$key][$ky],
              'statement_id' =>$id,
              'status'=>1
            );
            $completeData['transaction'][] = $dateValues;
        }
      } else if($typeOfCreditCard==3) {
        foreach ($tags as $key2 => $tag) {
            if (strpos($tag->nodeValue, 'Transaction Description') !== false) {
              $transactionDetails=true;
              $flag=false;
              $cnt_val = 0;
            }

            if(strpos($tag->nodeValue, 'Reward Points Summary') !== false) {
               $transactionDetails=false;
            }
             //
            if($transactionDetails){
            //  $pos = $this->strpos_arr($description, array());
              if (strpos($tag->nodeValue, 'Domestic Transactions') === false) {
                $match = preg_match('@(\b\d{1,2}/\d{1,2}/\d{2}\b)@', $tag->nodeValue, $transactionDate);
              if($match==1)
              {
                //echo $transactionDate[0];
                $stmt_transaction_date[$key][] = date('Y-m-d', strtotime(strtr($transactionDate[0], '/', '-')));
                $stmt_transaction_desc[$key][] = $tags[$j+1]->nodeValue;
                $stmt_transaction_amount[$key][] = $tags[$j+2]->nodeValue;
                if (strpos($tags[$j+3]->nodeValue, 'Cr') !== false) {
                    $stmt_tr_type[$key][]='1'; //credit
                }else{
                    $stmt_tr_type[$key][]='2'; //debit
                }

              }
            }
              //var_dump($tags[$j]->nodeValue);
            }
            $j++;
            if($transactionDetails && $line_no>1){
              $cnt_val++;
            }

        }

        if($key==0){
          $due_date = explode(':', $tags[6]->nodeValue);
          $issue_date = explode(':', $tags[5]->nodeValue);
          $completeData['statement']['issue_date'] =   date('Y-m-d',strtotime(strtr($issue_date[1], '/', '-')));
          $completeData['statement']['statement_type'] = 'HDFC Bank - Platinum Statement';
          $completeData['statement']['statement_for'] = $this->statementFor;
          $completeData['statement']['due_date'] = date('Y-m-d',strtotime($due_date[0]));
        //  $completeData['statement']['statement_notes'] = 'read completed';
          $completeData['statement']['statement_number'] = time();
        }
        $dateValues = array();
         foreach($stmt_transaction_date[$key] as $ky => $v){
            // $stmt_transaction_desc[$key][$ky].'...<br/>';;
             $dateValues = array(
              'transaction_date' =>$v,
              'amount' => str_replace(',', '', $stmt_transaction_amount[$key][$ky]),
              'description' =>  $stmt_transaction_desc[$key][$ky],
              'transaction_type' =>$stmt_tr_type[$key][$ky],
              'statement_id' =>$id,
              'status'=>1
            );
            $completeData['transaction'][] = $dateValues;
        }
      }

      // condition check for which credit card

      } // loop end for html files
        // var_dump($completeData);
        // exit;
        return $completeData;
      }

  }


  function strpos_arr($haystack, $needle) {
    if(!is_array($needle)) $needle = array($needle);
    foreach($needle as $what) {
        if(($pos = strpos($haystack, $what))!==false) return $pos;
    }
    return false;
  }

  function ccFilter($tags) {
    $type ='';
    foreach($tags as $k => $tag){
         $string = $tag->nodeValue;
        if(strpos($string, 'Regalia MasterCard Credit Card Statement') !== false){
            $type= 1;
        } else if(strpos($string, 'Platinum Plus MasterCard') !== false){
            $type= 2;
        } else if(strpos($string, 'PLATINUM STATEMENT') !== false){
            $type= 3;
        }
    }
  return $type;
  }


}
?>
