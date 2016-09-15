<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
/**
 * 4PSA VoipNow App: CallMeButton
 *
 * This file is called when making a UnifiedAPI request
 *
 * @version 2.0.0
 * @license released under GNU General Public License
 * @copyright (c) 2012 4PSA. (www.4psa.com). All rights reserved.
 * @link http://wiki.4psa.com
 *
 */

/* Include the configuration file */
require_once('config/config.php');

/*Include file containing the function needed */
require_once('plib/lib.php');

/* Include the language file */
require_once('language/en.php');
require_once('plib/cURLRequest.php');

error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING );

// init session, we will keep tokens in the session
session_start();

if(empty($_GET)) {
    echo getLangMsg('err_req_empty');
    exit(0);
}
//if we want to retrieve the call status. The url is passed as a request parameter.
if (isset($_GET['status'])) {
    header('Content-type: application/json');
    echo getStatusResponse($_GET['url']);
    exit();
}

//if we want to call somebody
$response = sendRequest($_GET['number']);

if (!empty($response)) {
    header('Content-type: application/json');
    exit($response);
}
exit(1);
