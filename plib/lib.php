<?php
/**
 * 4PSA VoipNow App: Call Me Button
 *
 * File contains all the functioned used when making a call
 *
 * @version 2.0.0
 * @license released under GNU General Public License
 * @copyright (c) 2012 4PSA. (www.4psa.com). All rights reserved.
 * @link http://wiki.4psa.com
 *
 */

/**
 * Sets a handle for uncaught exceptions.
 * @param Exception $exception
 */

function logWrite2File($stroka) {
	$file = "/tmp/callmebutton.log";
	$d = date("Y-m-d H:i:s");
	file_put_contents($file, $d."\t".$stroka."\n", FILE_APPEND);
}


function exception_handler($exception) {
    echo "Uncaught exception: " , $exception->getMessage(), "\n";
}

set_exception_handler('exception_handler');


/**
 * Generate a new token based on App ID and App secret
 *
 * @return string token
 * @return boolean FALSE when token could not be generated
 */
function generateToken() {
    global $config;

    $reqUrl = 'https://'.$config['VN_SERVER_IP'].'/oauth/token.php';

    $request = new cURLRequest();
    $request->setMethod(cURLRequest::METHOD_POST);

    $fields = array(
        'grant_type' => 'client_credentials',
        'redirect_uri' => $_SERVER['PHP_SELF'],
        'client_id' =>  urlencode($config['OAUTH_APP_ID']),
        'client_secret' => urlencode($config['OAUTH_APP_SECRET']),
        'state' => '0',

    );
    $request->setBody($fields);
    $response = $request->sendRequest($reqUrl);
    $respBody = $response->getBody();
    if ($response->getStatus() == Response::STATUS_OK && isset($respBody['access_token'])) {
        $_SESSION['CallMeButton']['token'] = 'Bearer '.$respBody['access_token'];
        return 'Bearer '.$respBody['access_token'];
    }
    return false;
}

/**
 * Get the token used for previous requests, or generate a new one if none exists
 *
 * @return string token
 */
function getToken() {
	#print_r($_SESSION);
	logWrite2File('func: getTocket - start');
    if (isset($_SESSION['CallMeButton']['token']) && $_SESSION['CallMeButton']['token']) {
        $token = $_SESSION['CallMeButton']['token'];
        logWrite2File('func: getTocket - toking FROM session conist>>> '.$token);
    } else {
        /* generate token */
        $token = generateToken();
        logWrite2File('func: getTocket - generate NEW token>>> '.$token);
    }
    return token;
}

/**
 * Make the UnifiedAPI request for calling the phone number. (PhoneCalls Create)
 *
 * @param string $phoneNumber The phone number.
 *
 * @return TRUE on sucess
 * @return FALSE on error
 *
 */
function sendRequest($phoneNumber){
    global $config;


   $token = getToken();
    if (!$token) {
        return false;
    }

    $headers = array(
        'Content-type' => 'application/json',
        'Authorization' => $token
    );

    /* This is the URL accessed using the REST protocol */
    $reqUrl = 'https://'.$config['VN_SERVER_IP'].'/unifiedapi/phoneCalls/@me/simple';

    $request = new cURLRequest();
    $request->setMethod(cURLRequest::METHOD_POST);
    $request->setHeaders($headers);


    $jsonRequest = array(
        'extension' => $config['VN_EXTENSION'],
        'phoneCallView' => array(array(
            'source' => array($config['VN_EXTENSION']),
            'destination' => $phoneNumber))
    );

    $request->setBody(json_encode($jsonRequest));
    $response = $request->sendRequest($reqUrl);

    if ($response->getStatus() == Response::STATUS_FORBIDDEN) {
		logWrite2File('func: sendRequest RESPONSE STATUS_FORBIDDEN>>> '.$response->getStatus());
        // try to regenerate token
        $headers['Authorization'] = generateToken();
        $request->setHeaders($headers);
        // retry request
        $response = $request->sendRequest($reqUrl);
    }
    if ($response->getStatus() == '401') {
		logWrite2File('func: sendRequest STATUS IS 401>>> '.$response->getStatus());
        // try to regenerate token
        $headers['Authorization'] = generateToken();
        $request->setHeaders($headers);
        // retry request
        $response = $request->sendRequest($reqUrl);
	} else {
		logWrite2File('func: sendRequest !!!!!!!!!!!!!>> '.$response->getStatus());
	}    
    return $response->getBody(true);
}

/**
 * This functions gets the call parameters from VoipNow using the UnifiedAPI.
 * @param string $url
 */
function getStatusResponse($url) {

    $token = getToken();

    $request = new cURLRequest();
    $request->setMethod(cURLRequest::METHOD_GET);

    $request->setHeaders(array(
        'Content-type' => 'application/json',
        'Authorization' => $token
    ));
    $response = $request->sendRequest($url);

    return $response->getBody(true);
}

/**
 * Get language message associated with the code
 *
 * @param string $code code to translate
 *
 * @return message on success
 * @return code if not found
 *
 */
function getLangMsg($code){
    global $msgArr;

    if(isset($msgArr[$code])) {
        return $msgArr[$code];
    }
    return $code;
}
?>
