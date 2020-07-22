<?php
class InvoiceStmtRead
{
    var $allFiles;
    var $invoiceId;
    var $invoiceFor;

    function __construct($htmlFiles, $id, $name)
    {
        $this->allFiles = $htmlFiles;
        $this->invoiceId = $id;
        $this->invoiceFor = $name;
    }

    /*---------------------------------------------------
    |   Find the Invoice Statment Name or Which Invoice
    ---------------------------------------------------*/
    function whichInvoice()
    {
        $type = '';
        foreach ($this->allFiles as $key => $file)
        {
            $doc = new DOMDocument();
            $doc->loadHTMLFile($file);
            $tags = $doc->getElementsByTagName('p');
        }
        foreach ($tags as $k => $tag)
        {
            $string = $tag->nodeValue;
            if (strpos($string, 'TAYLOR SUPPORT SERVICES') !== false){
                $type = 'TAYLOR SUPPORT SERVICES';
            }
            else if (strpos($string, 'www.everydayind.com.au') !== false){
                $type = 'Everyday Independence';
            }
            else if (strpos($string, 'www.ioe.org.au') !== false){
                $type = 'Interchange Outer East';
            }
            else if (strpos($string, 'thelittlebookworm.com.au') !== false){
                $type = 'The Little Bookworm';
            }
            else if(strpos($string, 'Yooralla') !== false){
                $type= 'Yooralla Invoice';
            }
            else if(strpos($string, 'supremehealthservices') !== false){
                $type= 'Supreme Health Services Invoice';
            }
            else if(strpos($string,'recreationbe.com') !== false){
                $type= 'ReCreation Invoice';
            }
        }
        return $type;
    }

    /*---------------------------------------------------
    |   Read Statment which Match the invoice Statment
    ---------------------------------------------------*/
    function readStmt()
    {
        $whichInvoice = $this->whichInvoice();
        switch ($whichInvoice)
        {
            case 'TAYLOR SUPPORT SERVICES':
                return $result = $this->invoice_ToTerry($whichInvoice);
            break;
            case 'Everyday Independence':
                return $result = $this->invoice_everydayIndependence($whichInvoice);
            break;
            case 'Interchange Outer East':
                  return $result = $this->invoice_InterchangeOuterEast($whichInvoice);
            break;
            case 'The Little Bookworm':
                  return $result = $this->invoice_Thelittlebookworm($whichInvoice);
            break;
            case 'Yooralla Invoice':
                  return $result = $this->invoice_yoorallaInvoice($whichInvoice);
            break;
            case 'Supreme Health Services Invoice':
                  return $result = $this->invoice_supremeHealthServices($whichInvoice);
            break;
            case 'ReCreation Invoice':
            return $result = $this->invoice_reCreationBe($whichInvoice);
            break;
            default:
                // code...
            break;
        }
    }

    /*---------------------------------------------------
    |   get Single value for tag
    ---------------------------------------------------*/
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

    /*---------------------------------------------------
    |   get Line value for tag
    ---------------------------------------------------*/
    function get_line_value($data, $reading_start, $counter)
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
                    $required_field = trim($inv->getLineNo());
                }
            }
            if ($cheak_required_field)
            {
                $cnt_val++;
            }
        }
        return $required_field;
    }

    /*---------------------------------------------------
    |   invoice_ToTerry
    ---------------------------------------------------*/
    function invoice_ToTerry($invoiceName)
    {
      $i=0;
      foreach ($this->allFiles as $key => $file)
      {
        //echo $file; exit;
        $doc = new DOMDocument();
        $doc->loadHTMLFile($file);
        $tags = $doc->getElementsByTagName('p');
        $trans_start = false;
        $j = 0;
        $line_no = 0;
        $invoice_date = $this->get_single_value($tags, 'Date', '1');
        $invoice_num = $this->get_single_value($tags, 'Tax Invoice #', '1');
        $invoice_total = $this->get_single_value($tags, 'TOTAL AUD', '1');
        $sub_total = $this->get_single_value($tags, 'Subtotal', '1');
        $gst='';
        //$i=26;
         $i= $this->get_line_value($tags, 'Description', '1')-5;
         foreach ($tags as $f => $tag)
         {
           $present = $this->get_single_value($tags, 'Description', '1');
           if($present){
             $trans_start=true;
           }
           if($trans_start){
             $k=1;
             while($i<=count($tags)){
               $i=$i+6;
               if(!empty($tags[$i]->nodeValue)){
                 $trans_desc  =  $tags[$i]->nodeValue;
                 $trans_qty  =   $tags[$i+1]->nodeValue;
                 $trans_up  =   $tags[$i+2]->nodeValue;
                 $trans_gst  =   $tags[$i+5]->nodeValue;
                 $trans_amt  =   $tags[$i+3]->nodeValue;
                 $dateValues[] = array(
                     'line_item_name' => $k,
                     'description' => $trans_desc,
                     'qty' => $trans_qty,
                     'unit_price' => $trans_up,
                     'gst' => $trans_gst,
                     'amount' => $trans_amt,
                     'invoice_id' => $this->invoiceId,
                 );
               }
               $k++;
             }
           }
           $j++;
         }
      }
      $removedLast = array_pop($dateValues);
      $sub_total  = $removedLast['description'];
      $invoice_total =  $removedLast['qty'];
      $gst = $removedLast['gst'];

      $invoice['transaction'] = $dateValues;
      $invoice['invoice']['invoice_type'] = $invoiceName;
      $invoice['invoice']['invoice_date'] =  $invoice_date;
      $invoice['invoice']['invoice_number'] = $invoice_num;
      $invoice['invoice']['invoice_for'] = 'Invoice';
      $invoice['invoice']['sub_total'] = $sub_total;
      $invoice['invoice']['total'] = $invoice_total;
      $invoice['invoice']['gst'] = $gst;
      return $invoice;
    }

    /*---------------------------------------------------
    |   invoice_everydayIndependence
    ---------------------------------------------------*/
    function invoice_everydayIndependence($invoiceName)
    {
        $i=0;
        foreach ($this->allFiles as $key => $file)
        {
            $doc = new DOMDocument();
            $doc->loadHTMLFile($file);
            $tags = $doc->getElementsByTagName('p');
            $trans_start = false;
            $j = 0;
            $line_no = 0;
            $invoice_date = $this->get_single_value($tags, 'Due Date', '1');
            $invoice_num = $this->get_single_value($tags, 'Invoice Number', '1');
            $invoice_total = $this->get_single_value($tags, 'TOTAL AUD', '1');
            $sub_total = $this->get_single_value($tags, 'Subtotal', '1');
            $gst='';
            foreach ($tags as $f => $tag)
            {
                $present = $this->get_single_value($tags, 'Amount AUD', '1');
                if($present){
                  $trans_start=true;
                }
                if($trans_start){
                  $description = $this->get_single_value($tags, 'Amount AUD', '1');
                  $qty = $this->get_single_value($tags, $description, '1');
                  $unitPrice = $this->get_single_value($tags, $qty, '1');
                  $gst = $this->get_single_value($tags, $unitPrice, '1');
                  $amt = $this->get_single_value($tags, $gst, '1');

                  $trans_desc[] = $description;
                  $trans_qty[] = $qty;
                  $trans_up[] = $unitPrice;
                  $trans_gst[] = $gst;
                  $trans_amt[] = $amt;
                }
              $j++;
            }
            $dateValues = array(
                'line_item_name' => $i,
                'description' => $trans_desc[$f],
                'qty' => $trans_qty[$f],
                'unit_price' => $trans_up[$f],
                'gst' => $trans_gst[$f],
                'amount' => $trans_amt[$f],
                'invoice_id' => $this->invoiceId,
            );
            $invoice['transaction'][] = $dateValues;
            $i++;
        }
        $invoice['invoice']['invoice_type'] = $invoiceName;
        $invoice['invoice']['invoice_date'] =  $invoice_date;
        $invoice['invoice']['invoice_number'] = $invoice_num;
        $invoice['invoice']['invoice_for'] = 'Invoice';
        $invoice['invoice']['sub_total'] = $sub_total;
        $invoice['invoice']['total'] = $invoice_total;
        $invoice['invoice']['gst'] = $gst;

        return $invoice;
    }


    /*---------------------------------------------------
    |   invoice_InterchangeOuterEast($invoiceName)
    ---------------------------------------------------*/

    function invoice_InterchangeOuterEast($invoiceName)
    {
        $i=0;
        foreach ($this->allFiles as $key => $file)
        {
            $doc = new DOMDocument();
            $doc->loadHTMLFile($file);
            $tags = $doc->getElementsByTagName('p');
            $trans_start = false;
            $j = 0;
            $line_no = 0;
            $termData = $this->get_line_value($tags, 'Tax Invoice', '1');
            $term = $this->get_single_value($tags, $termData, '1');

            $invoice_date = $this->get_single_value($tags, $termData, '0');

            $inc_data = $invoice_date;

            $invoice_num = $this->get_single_value($tags, $invoice_date, '0');

            // echo " xxxxxxxx2=> ";
            // print_r($invoice_num);

            // $invoice_num = $this->get_single_value($tags, 'Invoice Number', '1');
            // $invoice_total = $this->get_single_value($tags, 'TOTAL AUD', '1');
            // $sub_total = $this->get_single_value($tags, 'Subtotal', '1');
            $gst='';
            foreach ($tags as $f => $tag)
            {
                $present = $this->get_single_value($tags, 'Amount AUD', '1');
                if($present){
                  $trans_start=true;
                }
                if($trans_start){
                  $description = $this->get_single_value($tags, 'Amount AUD', '1');
                  $qty = $this->get_single_value($tags, $description, '1');
                  $unitPrice = $this->get_single_value($tags, $qty, '1');
                  $gst = $this->get_single_value($tags, $unitPrice, '1');
                  $amt = $this->get_single_value($tags, $gst, '1');

                  $trans_desc[] = $description;
                  $trans_qty[] = $qty;
                  $trans_up[] = $unitPrice;
                  $trans_gst[] = $gst;
                  $trans_amt[] = $amt;
                }
              $j++;
            }
            $dateValues = array(
                'line_item_name' => $i,
                'description' => $trans_desc[$f],
                'qty' => $trans_qty[$f],
                'unit_price' => $trans_up[$f],
                'gst' => $trans_gst[$f],
                'amount' => $trans_amt[$f],
                'invoice_id' => $this->invoiceId,
            );
            $invoice['transaction'][] = $dateValues;
            $i++;
        }
        $invoice['invoice']['invoice_type'] = $invoiceName;
        $invoice['invoice']['invoice_date'] =  $invoice_date;
        $invoice['invoice']['invoice_number'] = $invoice_num;
        $invoice['invoice']['invoice_for'] = 'Invoice';
        $invoice['invoice']['sub_total'] = $sub_total;
        $invoice['invoice']['total'] = $invoice_total;
        $invoice['invoice']['gst'] = $gst;
        print_r($invoice); exit;
        return $invoice;
    }

    /*---------------------------------------------------
    |   invoice_Thelittlebookworm_old($invoiceName)
    ---------------------------------------------------*/
    function invoice_Thelittlebookworm_old($invoiceName)
    {
        $i=0;
        foreach ($this->allFiles as $key => $file)
        {
            $doc = new DOMDocument();
            $doc->loadHTMLFile($file);
            $tags = $doc->getElementsByTagName('p');
            $trans_start = false;
            $j = 0;
            $line_no = 0;
            $invoice_date = $this->get_single_value($tags, 'Invoice Date:', '1');

            $received = $this->get_single_value($tags, 'Amount Received', '1');
            $paidAmtdata = explode('$', $received);
            $paidAmt = $paidAmtdata[2];
            $gst='';
            foreach ($tags as $f => $tag)
            {
                $present = $this->get_single_value($tags, 'GST', '1');
                if($present){
                  $trans_start=true;
                }
                if($trans_start){

                  $dated = $this->get_single_value($tags, 'GST', '1');
                  $qtydata = $this->get_single_value($tags, 'GST', '2');
                  $qtyD = preg_split('/\s+/', $qtydata);

                  $qty = isset($qtyD[0]) ? preg_split('/\s/', $qtyD[0], null, PREG_SPLIT_NO_EMPTY):0;

                  $qty2 = isset($qty[0]) ? preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $qty[0]):0;

                  $description = $this->get_single_value($tags, $qtydata, '2');

                  $price = $this->get_single_value($tags, $description, '1');

                  $amtdata = preg_split('/\s/', $price, null, PREG_SPLIT_NO_EMPTY);
                  $unitPrice = isset($amtdata[0]) ? preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $amtdata[0]):0;
                  $amt = isset($amtdata[1]) ? preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $amtdata[1]):0;
                  $gst = $this->get_single_value($tags, $price, '1');
                  $invoice_totalD = $this->get_single_value($tags, $gst, '1');
                  $invoice_total = ltrim($invoice_totalD,'$');
                  $item = isset($qty[2])?$qty[2]:'';

                  $trans_desc[] = $description;
                  $trans_qty[] = $qty2;
                  $trans_up[] = ltrim($unitPrice,'$');
                  $trans_gst[] = $gst;
                  $trans_amt[] = $amt;
                  $trans_item[] = $item;
                }
              $j++;
            }
            $dateValues = array(
                'start_date' => $dated,
                'line_item_name' => $i,
                'line_item_number' => $item,
                'description' => $trans_desc[$f],
                'qty' => $trans_qty[$f],
                'unit_price' => $trans_up[$f],
                'gst' => $trans_gst[$f],
                'amount' => $trans_amt[$f],
                'line_item_name' => $trans_item[$f],
                'invoice_id' => $this->invoiceId,
                'created_date' => DATE_TIME
            );

            $invoice['transaction'][] = $dateValues;
            $i++;
        }
        $invoice['invoice']['invoice_type'] = $invoiceName;
        $invoice['invoice']['invoice_date'] =  ($invoice_date > 0)?date('Y-m-d', strtotime($invoice_date)):'';
        $invoice['invoice']['invoice_number'] = $invoice_num;
        $invoice['invoice']['invoice_for'] = 'Invoice';
        $invoice['invoice']['sub_total'] = $sub_total;
        $invoice['invoice']['total'] = $invoice_total;
        $invoice['invoice']['gst'] = $gst;
        // print_r($invoice); exit();
        return $invoice;
    }


    /*---------------------------------------------------
    |   invoice_Thelittlebookworm($invoiceName)
    ---------------------------------------------------*/
    function invoice_Thelittlebookworm($invoiceName)
    {
        $i=0;
        foreach ($this->allFiles as $key => $file)
        {
            $doc = new DOMDocument();
            $doc->loadHTMLFile($file);
            $tags = $doc->getElementsByTagName('p');
            $trans_start = false;
            $j = 0;
            $line_no = 0;
            $invoice_date = $this->get_single_value($tags, 'Invoice Date:', '1');
            $invoice_num = $this->get_single_value($tags, 'Tax Invoice', '1');

            $received = $this->get_single_value($tags, 'Amount Received', '1');
            $paidAmtdata = explode('$', $received);
            $paidAmt = $paidAmtdata[2];
            $gst='';
            $i = $this->get_line_value($tags, 'GST', '1');

            foreach ($tags as $f => $tag)
            {
                $present = $this->get_single_value($tags, 'GST', '1');

                $presentmm = $this->get_single_value($tags, 'GST', '1');

                $ext = $this->get_line_value($tags, 'Payment', '1');
                // echo " presentmm=>".$presentmm;
                // echo " ext=>".$ext;
                if($present){

                    $i = $i-4;
                    while($i <= count($tags)){

                        if(!empty($tags[$i]->nodeValue)){

                            $item_date = $tags[$i]->nodeValue;
                            $rawData = $tags[$i+1]->nodeValue;
                            $rawData2 = preg_split('/\s/', $rawData);
                            $strDataone = $rawData2[0];
                            $strData22 = preg_split('/[\s]+/',$strDataone);
                            $qty = isset($strData22[0]) ? preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $strData22[0]):0;
                            // print_r($strData22);
                            $line_item_number = isset($rawData2[2])?$rawData2[2]:0;

                            $description = $tags[$i+3]->nodeValue;

                            $unit_data = $tags[$i+4]->nodeValue;
                            $unit_array = explode("$",$unit_data);
                            $unit_price = isset($unit_array[1])?$unit_array[1]:0;
                            $amount_data = isset($unit_array[2])?$unit_array[2]:0;
                            $gst = $tags[$i+5]->nodeValue;
                            $amount = $tags[$i+6]->nodeValue;

                            /*echo "<hr>";
                            echo "item_date => ".$item_date;
                            echo "line_item_name => ".$i;
                            echo "line_item_number => ".$line_item_number;
                            echo "description => ".$description;
                            echo "qty => ".$qty;
                            echo "unit_price => ".$unit_price;
                            echo "gst => ".$gst;
                            echo "amount => ".$amount_data;*/



                            $dateValues[] = array(
                                'start_date' => $item_date,
                                'line_item_name' => $i,
                                'line_item_number' => $line_item_number,
                                'description' => $description,
                                'qty' => $qty,
                                'unit_price' => ltrim($unit_price,'$'),
                                'gst' => ltrim($gst, '$'),
                                // 'sub_amount' => $amount_data,
                                'amount' => ltrim($amount,'$'),
                                'invoice_id' => $this->invoiceId,
                                'created_date' => DATE_TIME
                            );
                        }
                        $i++;
                    }
                }
            }


            //$i++;
        }
        $invoice['transaction'][] = $dateValues[0];
        $invoice['invoice']['invoice_type'] = $invoiceName;
        $invoice['invoice']['invoice_date'] =  ($invoice_date > 0)?date('Y-m-d', strtotime($invoice_date)):'';
        $invoice['invoice']['invoice_number'] = $invoice_num;
        $invoice['invoice']['invoice_for'] = 'Invoice';
        $invoice['invoice']['sub_total'] = $dateValues[0]['amount'];
        $invoice['invoice']['total'] = $dateValues[0]['amount'];
        $invoice['invoice']['gst'] = $dateValues[0]['gst'];
        // print_r($invoice); exit();
        return $invoice;
    }

    /*---------------------------------------------------
    |   invoice_yoorallaInvoice
    ---------------------------------------------------*/
    function invoice_yoorallaInvoice($invoiceName)
    {
      $i=0;
      foreach ($this->allFiles as $key => $file)
      {
        $doc = new DOMDocument();
        $doc->loadHTMLFile($file);
        $tags = $doc->getElementsByTagName('p');
        $trans_start = false;
        $j = 0;
        $line_no = 0;
        $invoice_date = $this->get_single_value($tags, 'Date', '1');
        $invoice_num = $this->get_single_value($tags, 'KEW  VIC  3101', '1');
        $invoice_total = $this->get_single_value($tags, 'Net Amount', '1');
        $sub_total = $this->get_single_value($tags, 'Subtotal', '1');
        $gst=$this->get_single_value($tags, 'GST', '-2');
        //$i=26;
         $i= $this->get_line_value($tags, 'Description', '1')-5;
         foreach ($tags as $fb => $tag)
         {
           if(strpos($tag->nodeValue,'Description')!==false){
             $trans_start=true;
           }

           if($trans_start)
           {
               $match = preg_match('@(\b\d{1,2}/\d{1,2}/\d{4}\b)@', $tag->nodeValue, $transactionDate);
                if($match==1)
                {
                    if(strpos($tags[$j]->nodeValue,$transactionDate[1])!==false){
                      $amount = $tags[$j-3]->nodeValue;
                      $qty = $tags[$j-2]->nodeValue;
                    }
                    if(strpos($tag->nodeValue,$amount)==false){
                      $gst = $tags[$j-4]->nodeValue;
                      $desc=$tags[$j-6]->nodeValue;
                    }
                    $unitPrice=$tags[$j+2]->nodeValue;
                    $trans_date[]  =  $transactionDate[1];
                    $trans_desc[]  = $desc;
                    $trans_up[] =   $unitPrice;
                    $trans_amt[] =   $amount;
                    $trans_gst[] =   $gst;
                    $trans_qty[] =   $qty;
               }
           }
           $j++;
         }
      }
      foreach ($trans_date  as $f => $v) {
            $dateValues = array(
              //'line_item_name' => $f+1,
              'description' => $trans_desc[$f],
              'qty' => $trans_qty[$f],
              'unit_price' => $trans_up[$f],
              'gst' => $trans_gst[$f],
              'amount' => $trans_amt[$f],
              'invoice_id' => $this->invoiceId,
           );
           $invoice['transaction'][]=$dateValues;
      }
      $invoice['invoice']['invoice_type'] = $invoiceName;
      $invoice['invoice']['invoice_date'] =  $invoice_date;
      $invoice['invoice']['invoice_number'] = $invoice_num;
      $invoice['invoice']['invoice_for'] = 'Invoice';
      $invoice['invoice']['sub_total'] = $sub_total;
      $invoice['invoice']['total'] = $invoice_total;
      $invoice['invoice']['gst'] = $gst;
      return $invoice;
    }

    /*---------------------------------------------------
    |   invoice_supremeHealthServices
    ---------------------------------------------------*/
    function invoice_supremeHealthServices($invoiceName)
    {
      $i=0;
      foreach ($this->allFiles as $key => $file)
      {
        $doc = new DOMDocument();
        $doc->loadHTMLFile($file);
        $tags = $doc->getElementsByTagName('p');
        $trans_start = false;
        $j = 0;
        $line_no = 0;
        $invoice_date = $this->get_single_value($tags, 'Invoice Date:', '1');
        $invoice_num = $this->get_single_value($tags, 'Invoice #:', '1');
        $invoice_total = $this->get_single_value($tags, 'Supreme Health Services', '1');
        $sub_total = $this->get_single_value($tags, $invoice_date, '1');
        $gst=$this->get_single_value($tags, $invoice_date, '2');
        foreach ($tags as $fb => $tag)
         {
           if(strpos($tag->nodeValue,'Invoice #:')!==false){
             $trans_start=true;
           }
           if($trans_start)
           {
               $match = preg_match('@(\b\d{1,2}/\d{1,2}/\d{4}\b)@', $tag->nodeValue, $transactionDate);
                if($match==1)
                {
                    $trans_date[]  =  $transactionDate[1];
                    $trans_desc[]  =  $tags[$j+2]->nodeValue;
                    $trans_up[]   =   $tags[$j+4]->nodeValue;
                    $trans_amt[] =   $tags[$j+5]->nodeValue;
                    $trans_gst[] =   $gst;
                    $trans_qty[] =   $tags[$j+3]->nodeValue;;
               }
           }
           $j++;
         }
      }
      foreach ($trans_date  as $f => $v) {
            $dateValues = array(
              //'line_item_name' => $f+1,
              'description' => $trans_desc[$f],
              'invoice_date' => date('Y-m-d',strtotime($v)),
              'qty' => $trans_qty[$f],
              'unit_price' => $trans_up[$f],
              'gst' => $trans_gst[$f],
              'amount' => $trans_amt[$f],
              'invoice_id' => $this->invoiceId,
           );
           $invoice['transaction'][]=$dateValues;
      }
      $invoice['invoice']['invoice_type'] = $invoiceName;
      $invoice['invoice']['invoice_date'] =  $invoice_date;
      $invoice['invoice']['invoice_number'] = $invoice_num;
      $invoice['invoice']['invoice_for'] = 'Invoice';
      $invoice['invoice']['sub_total'] = $sub_total;
      $invoice['invoice']['total'] = $invoice_total;
      $invoice['invoice']['gst'] = $gst;
      return $invoice;
    }

    /*---------------------------------------------------
    |   invoice_reCreationBe
    ---------------------------------------------------*/
    function invoice_reCreationBe($invoiceName)
    {
      $i=0;
      foreach ($this->allFiles as $key => $file)
      {
        $doc = new DOMDocument();
        $doc->loadHTMLFile($file);
        $tags = $doc->getElementsByTagName('p');
        $trans_start = false;
        $j = 0;
        $invoice_date ='';
        $invoice_num = '';
        $invoice_total = $this->get_single_value($tags, 'Amount Applied', '1');
        $sub_total = "";
        $gst="";
        $page = $key+1;
        foreach ($tags as $fb => $tag)
         {
           if(strpos($tag->nodeValue,'Invoice No.: ')!==false){
             $invoice_num =  ltrim(explode(':',trim($tag->nodeValue))[1]);
           }
           if(strpos($tag->nodeValue,'Date: ')!==false){
             $invoice_date =  explode(':',str_replace(' ', '',$tag->nodeValue))[1];
           }
           if(strpos($tag->nodeValue,'DESCRIPTION')!==false){
             $trans_start=true;
           }
           if(strpos($tag->nodeValue,'Total Inc GST')!==false){
             $gst = $tags[$j-1]->nodeValue;
           }
           if($trans_start && $page==2)
           {
                $trans_desc  =  $tags[$j+3]->nodeValue;
                $trans_amt =   ($tags[$j+4]->nodeValue=='GST')?$trans_start=false:$tags[$j+4]->nodeValue;
                $trans_gst =   $gst;
                $dateValues[] = array(
                 'description' => $trans_desc,
                 'gst' => $trans_gst,
                 'amount' => $trans_amt,
                 'invoice_id' => $this->invoiceId,
              );
           }
           $j++;
         }
      }
        array_pop($dateValues);
        $invoice['transaction']=$dateValues;
        $invoice['invoice']['invoice_type'] = $invoiceName;
        $invoice['invoice']['invoice_date'] =  $invoice_date;
        $invoice['invoice']['invoice_number'] = $invoice_num;
        $invoice['invoice']['invoice_for'] = 'Invoice';
        $invoice['invoice']['sub_total'] = $sub_total;
        $invoice['invoice']['total'] = $invoice_total;
        $invoice['invoice']['gst'] = $gst;
        return $invoice;
    }
}
?>
