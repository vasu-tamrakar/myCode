<?php
// send mails not in use send_mail_smtp
// Changed name from send_mail_smtp to send_mail 10/8/2018
function send_mail($to_email,$subject,$body,$cc_email_address = null)
{
	if(ENABLE_MAIL_SEND)
	{
		$obj =& get_instance();
		$msg = $body;
		$from_email = FROM_EMAIL;
		$obj->load->library('email');

		$config = array('protocol'=>'smtp',
			'smtp_host'=>'ssl://smtp.gmail.com',
			'smtp_port'=>465,
			'smtp_user'=>SMTP_USER,
			'smtp_pass'=>SMTP_PASSWORD,
			'charset'=>'utf-8',
			'mailtype'=>'html',
			'newline'=>"\r\n",
			'priority' =>3
		);

		$obj->email->initialize($config);
		$obj->email->from($from_email,APPLICATION_NAME);
		$obj->email->to($to_email);
		#$obj->email->cc($cc_email_address,$to_email);
		if(!empty($cc_email_address)){
			$obj->email->cc($cc_email_address);
		}
		$obj->email->subject($subject);
		$obj->email->message($msg);
		$obj->email->send();
		$output =  $obj->email->print_debugger();
	}
	return true;
}

function mailHeader()
{
	return $header = '<div style="padding:50px 0px;margin:0px;background:#f6f6f6;">
		 <table cellpadding="0" cellspacing="0" width="700px" style="margin:0px auto; font-family:sans-serif;"> 
<tbody>
       <tr>
          <td style="border-radius:15px;background:#fff;overflow:hidden" width="100%">
             <table cellpadding="0" cellspacing="0" width="100%">
    <tbody>
       <tr>
		      <td style="background:#5e64dd;padding:30px" align="center">
		         <img width="200px"
		            src=""
		            class="CToWUd" />
		      </td>
		   </tr>
		   <tr>
';
}

function mailFooter()
{
	return $footer = '</tr>
	<tr>
   <td>
      <table style="width:80%;margin:0px auto;padding:20px">
         <tbody>
            <tr style="margin-bottom:20px;font-size:12px;float:left;width:100%">
               <td
                  style="line-height:20px;width:100%;text-align:center;display:block;color:#909090">
                  @' . date("Y") . ' All Rights Reserved <b>FM</b>
               </td>
            </tr>
         </tbody>
      </table>
      <table width="80%" align="center"
         style="border-top:1px solid #38296e;padding:15px 0px;margin-top:15px;font-family:sans-serif">
         <tbody>
            <tr>
               <td align="right"
                  style="border-right:1px solid #38296e;padding-right:30px">
                  <a
                  href="http://yourfinancemanager.in" target="_blank"
                  data-saferedirecturl="https://www.google.com/url?q=http://yourfinancemanager.in&amp;source=gmail&amp;ust=1582262280254000&amp;usg=AFQjCNEIO-6mSuNnhynVz-IVY3_pzXujGg">
                  yourfinancemanager.in
                  </a>
               </td>
               <td align="left" style="padding-left:30px"> <b>(91) 80 61939100</b>
               </td>
            </tr>
            <tr>
               <td colspan="2" align="center"
                  style="padding-top:10px;font-size:14px">Prestige Shantiniketan, Whitefield, Bangalore, 560066
               </td>
            </tr>
         </tbody>
      </table>
   </td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
<div class="yj6qo"></div>
<div class="adL">
</div>
</div>
';
}


function activate_account_mail($data){
	$contentBody = '
<td>
   <table
      style="max-width:80%;min-width:80%;margin:0px auto;border-collapse:collapse;font-family:sans-serif"
      cellpadding="0">
      <tbody>
         <tr>
            <td
               style="padding:15px 30px 0px;font-size:16px;font-weight:600;color:#443d3d">
               <h3 style="text-align:center">Finance Manager Verify Email</h3>
            </td>
         </tr>
         <tr>
            <td
               style="padding:15px 0px ; font-size:15px;font-weight:600;color:#443d3d">
               Hi ' . $data['username'] . '!!<br/>
               Please Click on this button to activate your account<br/>
            </td>
          </tr>
          <tr>
            <td>
               <a href="' .  $data['url'] . '" style="text-decoration:none; display:block; text-align:center; margin-top:15px;">
               <button style="background:#5e64dd; padding:10px 20px; color:#fff; text-decoration:none; border:none; border-radius:5px; cursor:pointer">Activate</button>
               </a>
            </td>
         </tr>
           
         <tr>
         </tr>
         <tr>
            <td width="100%" style="margin-bottom:30px;float:left;width:100%">
            </td>
         </tr>
      </tbody>
   </table>
</td>
';

	$body = mailHeader() . $contentBody . mailFooter();
	$mail = send_mail($data['email'], 'FM:Activate Account', $body);
  	return $mail;

}

function user_created_mail_to_user($data){
$contentBody = '
<td>
   <table
      style="max-width:80%;min-width:80%;margin:0px auto;border-collapse:collapse;font-family:sans-serif"
      cellpadding="0">
      <tbody>
         <tr>
            <td
               style="padding:15px 30px 0px;font-size:16px;font-weight:600;color:#443d3d">
               <h3 style="text-align:center">Finance Manager Account created</h3>
            </td>
         </tr>
         <tr>
            <td
               style="padding:15px 0px ; font-size:15px;font-weight:600;color:#443d3d">
               Hi ' . $data['username'] . '!!<br/>
               Please click on this button to set your new password.<br/>
            </td>
          </tr>
          <tr>
            <td><b>Email:-</b>' . $data['email'] . '</td>
          </tr>
          <tr>
            <td>
               <a href="' .  $data['url'] . '" style="text-decoration:none; display:block; text-align:center; margin-top:15px;">
               <button style="background:#5e64dd; padding:10px 20px; color:#fff; text-decoration:none; border:none; border-radius:5px; cursor:pointer">Set Password</button>
               </a>
            </td>
         </tr>
           
         <tr>
         </tr>
         <tr>
            <td width="100%" style="margin-bottom:30px;float:left;width:100%">
            </td>
         </tr>
      </tbody>
   </table>
</td>
';

  $body = mailHeader() . $contentBody . mailFooter();
  $mail = send_mail($data['email'], 'FM:Account Created', $body);
    return $mail;
}


function forgot_password_mail($data){
  $contentBody = '
<td>
   <table
      style="max-width:80%;min-width:80%;margin:0px auto;border-collapse:collapse;font-family:sans-serif"
      cellpadding="0">
      <tbody>
         <tr>
            <td
               style="padding:15px 30px 0px;font-size:16px;font-weight:600;color:#443d3d">
               <h3 style="text-align:center">Finance Manager Reset Password</h3>
            </td>
         </tr>
         <tr>
            <td
               style="padding:15px 0px ; font-size:15px;font-weight:600;color:#443d3d">
               Hi ' . $data['username'] . '!!<br/>
               Please Click on this button to reset your password<br/>
            </td>
          </tr>
          <tr>
            <td>
               <a href="' .  $data['url'] . '" style="text-decoration:none; display:block; text-align:center; margin-top:15px;">
               <button style="background:#5e64dd; padding:10px 20px; color:#fff; text-decoration:none; border:none; border-radius:5px; cursor:pointer">Reset</button>
               </a>
            </td>
         </tr>
           
         <tr>
         </tr>
         <tr>
            <td width="100%" style="margin-bottom:30px;float:left;width:100%">
            </td>
         </tr>
      </tbody>
   </table>
</td>
';

  $body = mailHeader() . $contentBody . mailFooter();
  $mail = send_mail($data['email'], 'FM:Reset Password', $body);
    return $mail;

}


function ai_export_data_mail($data){
   $contentBody = '
   <td>
   <table
      style="max-width:80%;min-width:80%;margin:0px auto;border-collapse:collapse;font-family:sans-serif"
      cellpadding="0">
      <tbody>
         <tr>
            <td
               style="padding:15px 30px 0px;font-size:16px;font-weight:600;color:#443d3d">
               <h3 style="text-align:center">Finance Manager Verify Email</h3>
            </td>
         </tr>
         <tr>
            <td
               style="padding:15px 0px ; font-size:15px;font-weight:600;color:#443d3d">
               Hi ' . $data['name'] . '!!<br/>
               Please Click on this button to check statment report on category changes<br/>
            </td>
          </tr>
          <tr>
            <td>
               <a href="' .  $data['url'] . '" style="text-decoration:none; display:block; text-align:center; margin-top:15px;">
               <button style="background:#5e64dd; padding:10px 20px; color:#fff; text-decoration:none; border:none; border-radius:5px; cursor:pointer">Download</button>
               </a>
            </td>
         </tr>
           
         <tr>
         </tr>
         <tr>
            <td width="100%" style="margin-bottom:30px;float:left;width:100%">
            </td>
         </tr>
      </tbody>
   </table>
</td>
';
$body = mailHeader() . $contentBody . mailFooter();  
$mail = send_mail($data['email'], 'FM:AI '.$data['type'].' Review ', $body,$data['cc']);
return $mail;
 
 }

