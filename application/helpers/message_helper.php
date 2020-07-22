<?php

function system_msgs($msg_key) {
    $global_ary = array(
        'something_went_wrong' => 'Something went wrong.',
        'wrong_username_password' => 'Invalid username or password.',
        'account_not_active' => 'Your account does not exist, please contact admin.',
        'success_login' => 'Login successfully.',
        'This_email_not_exist_oversystem' => 'This email not exist over system.',
        'forgot_password_send_mail_succefully' => 'Please visit your mail inbox to reset your password.',
        'verfiy_token_error' => 'Invalid request',
        'password_reset_successfully' => 'Password reset successfully',
        'verfiy_password_error' => 'Invalid request',
        'login_attempt' => 'Login attempt failed please reset password',
        'invalid_json' => 'Not a valid JSON',
		    'server_error' => 'Server error.',
        'user_not_exist' => 'User not Exist',
        'password_not_match' => 'Please enter valid password',
        'permission_error' => 'Sorry you have no permission to excess.',
        'password_empty' => 'Password field is required',
        'username_empty' => 'UserName field is required',
        'token_mismatch' => 'Invalid token or token mismatch',
        'forgot_password_success' => 'Forgot password successfully send email to your registered primary email.',
        'invalid_request' => 'Invalid Request',
        'new_password_not_same_as_old_password' => 'Your new password can not same as old password',
        'dont_have_portal_access' => 'Your dont have portal access'
    );
    return $global_ary[$msg_key];
}

?>
