<?php
/**
 * 4PSA VoipNow App: CallMeButton
 *
 * This file stores all the configuration parameters like:
 * IP/Hostname of the server
 * OAuth credentials
 *
 * @version 2.0.0
 * @license released under GNU General Public License
 * @copyright (c) 2012 4PSA. (www.4psa.com). All rights reserved.
 * @link http://wiki.4psa.com
 */

/**
 * The IP/Hostname of the VoipNow Professional server
 * @global string
 */
$config['VN_SERVER_IP'] = '';
/**
 * Number of the extension that will initiate the call
 * @global string
 */
$config['VN_EXTENSION'] =  '';

/**
 * APP ID for 3-legged OAuth
 * Must be fetched from VoipNow interface
 * @global string
 */
$config['OAUTH_APP_ID'] = '';

/**
 * APP Secret for 3-legged OAuth
 * Must be fetched from VoipNow interface
 * @global string
 */
$config['OAUTH_APP_SECRET'] = '';

?>
