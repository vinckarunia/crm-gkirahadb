<?php
/*******************************************************************************
 *
 *  filename    : Include/Config.php
 *  website     : https://churchcrm.io
 *  description : global configuration
 *
 ******************************************************************************/

// Database connection constants
$sSERVERNAME = '127.0.0.1';
$dbPort = '3306';
$sUSER = 'admin';
$sPASSWORD = 'Bersyukursenantias@0';
$sDATABASE = 'gkirahachurchcrm';
#$TwoFASecretKey = '1';

// Root path of your ChurchCRM installation ( THIS MUST BE SET CORRECTLY! )
//
// Examples:
// - if you will be accessing from http://www.yourdomain.com/churchcrm then you would enter '/churchcrm' here.
// - if you will be accessing from http://www.yourdomain.com then you would enter '' ... an empty string for a top level installation.
//
// NOTE:
// - the path SHOULD Start with slash, if not ''.
// - the path SHOULD NOT end with slash.
// - the path is case sensitive.
$sRootPath = '/';

// Set $bLockURL=TRUE to enforce https access by specifying exactly
// which URL's your users may use to log into ChurchCRM.
$bLockURL = FALSE;

// URL[0] is the URL that you prefer most users use when they
// log in.  These are case sensitive.
$URL[0] = 'localhost:8000';
// List as many other URL's as may be needed. Number them sequentially.
//$URL[1] = 'https://www.mychurch.org/churchcrm/';
//$URL[2] = 'https://www.mychurch.org:8080/churchcrm/';
//$URL[3] = 'https://www.mychurch.org/churchcrm/';
//$URL[4] = 'https://ssl.sharedsslserver.com/mychurch.org/churchcrm/';
//$URL[5] = 'https://crm.mychurch.org/';

// If you are using a non-standard port number be sure to include the
// port number in the URL. See example $URL[2]

// To enforce https access make sure that "https" is specified in all of the
// the allowable URLs.  Safe exceptions are when you are at the local machine
// and using "localhost" or "127.0.0.1"

// When using a shared SSL certificate provided by your webhost for https access
// you may need to add the shared SSL server name as well as your host name to
// the URL.  See example $URL[4]

// Sets which PHP errors are reported see http://php.net/manual/en/errorfunc.constants.php
error_reporting(E_ERROR);

//
// SETTINGS END HERE.  DO NOT MODIFY BELOW THIS LINE
//
// Absolute path must be specified since this file is called
// from scripts located in other directories
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'LoadConfigs.php');
