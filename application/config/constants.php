<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Display Debug backtrace
|--------------------------------------------------------------------------
|
| If set to TRUE, a backtrace will be displayed along with php errors. If
| error_reporting is disabled, the backtrace will not display, regardless
| of this setting
|
*/
defined('SHOW_DEBUG_BACKTRACE') OR define('SHOW_DEBUG_BACKTRACE', TRUE);

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
defined('FILE_READ_MODE')  OR define('FILE_READ_MODE', 0644);
defined('FILE_WRITE_MODE') OR define('FILE_WRITE_MODE', 0666);
defined('DIR_READ_MODE')   OR define('DIR_READ_MODE', 0755);
defined('DIR_WRITE_MODE')  OR define('DIR_WRITE_MODE', 0755);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/
defined('FOPEN_READ')                           OR define('FOPEN_READ', 'rb');
defined('FOPEN_READ_WRITE')                     OR define('FOPEN_READ_WRITE', 'r+b');
defined('FOPEN_WRITE_CREATE_DESTRUCTIVE')       OR define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
defined('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE')  OR define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
defined('FOPEN_WRITE_CREATE')                   OR define('FOPEN_WRITE_CREATE', 'ab');
defined('FOPEN_READ_WRITE_CREATE')              OR define('FOPEN_READ_WRITE_CREATE', 'a+b');
defined('FOPEN_WRITE_CREATE_STRICT')            OR define('FOPEN_WRITE_CREATE_STRICT', 'xb');
defined('FOPEN_READ_WRITE_CREATE_STRICT')       OR define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

/*
|--------------------------------------------------------------------------
| Exit Status Codes
|--------------------------------------------------------------------------
|
| Used to indicate the conditions under which the script is exit()ing.
| While there is no universal standard for error codes, there are some
| broad conventions.  Three such conventions are mentioned below, for
| those who wish to make use of them.  The CodeIgniter defaults were
| chosen for the least overlap with these conventions, while still
| leaving room for others to be defined in future versions and user
| applications.
|
| The three main conventions used for determining exit status codes
| are as follows:
|
|    Standard C/C++ Library (stdlibc):
|       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
|       (This link also contains other GNU-specific conventions)
|    BSD sysexits.h:
|       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
|    Bash scripting:
|       http://tldp.org/LDP/abs/html/exitcodes.html
|
*/
defined('EXIT_SUCCESS')        OR define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR')          OR define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG')         OR define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE')   OR define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS')  OR define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') OR define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT')     OR define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE')       OR define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN')      OR define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX')      OR define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code
define('DATE_TIME_FORMAT',"Y-m-d H:i:s");
define('DATE_TIME',date(DATE_TIME_FORMAT));
define('ENABLE_MAIL_SEND',true);
define('APPLICATION_NAME','FinanceManager');
define('SMTP_USER','testteam.financemanager@gmail.com');
define('SMTP_PASSWORD', 'mmxfxbcfuzibhvjd');

define('USER_STATEMENT_PATH','./uploads/statement/');
define('USER_INVOICE_PATH','./uploads/invoices/');
define('USER_REPORT_TEMP_PATH','./uploads/report/tmp/');
define('USER_REPORT_UPLOAD_PATH','./uploads/report/');
define('NO_IMAGE_PATH','./assets/images/no-image.png');
define('USER_ATTACHMENT_PATH','./uploads/attachments/');
define('USER_EXPORT_PATH','./uploads/export/');

define('UPLOADS','./uploads/');
define('USER_PROFILE_PATH','./uploads/user_profile/');
define('ADMIN_PROFILE_PATH','./uploads/admin_profile/');
define('USER_SMALL_IMAGE', UPLOADS.'user_profile/small/');
define('ADMIN_SMALL_IMAGE',UPLOADS.'admin_profile/small/');
define('BACKSLASH', "/");
define('UNREADABLE_PDF',getenv('FINANCE_MANAGER_FRONT_URL').'user/manage_protected_pdf');
define('CRON_DEFAULT_LIMIT',25);
define('CRON_GMAIL_FETCH_USER_LIMIT',CRON_DEFAULT_LIMIT);
define('CRON_CALL_DEFAULT_TYPE',0);

define('TBL_PREFIX','tbl_fm_');

define('STMT_EMAIL','testteam.financemanager@gmail.com');
define('STMT_PASSWORD','test@12345');
define('FROM_EMAIL', 'no-reply@yourfinancemanager.in');

define('MYSQl_RESULT_KEY_DATA', json_encode(['1'=>'result','2'=>'result_array','3'=>'row','4'=>'row_array']));
define('RESETLINK_EXPIRETIME', 3600);
define('FRONT_URL', getenv('FINANCE_MANAGER_FRONT_URL'));
define('PYTHON_CALL','/usr/bin/python ');
define('PYTHON_COMMAND1', getenv('PYTHON_SOURCE').'python/part_one/model_two.py ');
define('PYTHON_COMMAND2', getenv('PYTHON_SOURCE').'python/part_two/scrape1.py ');
define('CATEGORY_COLOR', json_encode(['1'=>'#c5d0ff','2'=>'#7a85ed','3'=>'#a5b1f2','4'=>'#6495ED','5'=>'#5e64dd','6'=>'#157DEC','7'=>'#6495ED','8'=>'#38ACEC']));
define('DEFAULT_CATEGORY_COLOR', '#79BAEC');
define('INVOICE_PROCESS_ONLY', false);
define('DAFAULT_TICKS', 1000);

define('PDF_TO_HTML_BIN', (getenv('PDFTOHTMLBIN') ? getenv('PDFTOHTMLBIN') : APPPATH.'third_party/poppler/bin/pdftohtml.exe'));
define('PDF_TO_HTML_INFO', (getenv('PDFTOHTMLINFO') ? getenv('PDFTOHTMLINFO') : APPPATH.'third_party/poppler/bin/pdfinfo.exe'));
define('ADMIN_ATTACHMENT_COOKIE_TOKEN_NAME', 'FM_admin_Token');
define('USER_ATTACHMENT_COOKIE_TOKEN_NAME', 'FM_Token');
define('AI_REVIEW_MAPPING_REPORT_DEFAULT_DAY', 1);
define('FORECASTING_INVOICE_AMOUNT',10);
define('FORECASTING_STATEMENT_AMOUNT',FORECASTING_INVOICE_AMOUNT);
define('AI_REPORT_EMAIL', 'testteam.developer@gmail.com');
define('AI_REPORT_EMAIL_TO_CC', 'testteam.financemanager@gmail.com');
define('AMOUNT_REGEX_KEY', '/^(\d{0,8})(?:\.\d{1,2})?$/i');
define('BALANCE_REGEX_KEY','/^[-]?\d{0,8}(\.\d{1,2})?$/i');