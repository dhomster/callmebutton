<?php
/**
 * 4PSA VoipNow App: CallMeButton
 *  
 * Application displays a field in which you can enter a number to call
 *
 * @version 2.0.0
 * @license released under GNU General Public License
 * @copyright (c) 2012 4PSA. (www.4psa.com). All rights reserved.
 * @link http://wiki.4psa.com
*/
require_once('plib/lib.php');
require_once('language/en.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta http-equiv="X-UA-Compatible" content="IE=9" />
<script>
	/* Use the same messages as in php for JS*/
	var msgArr = [];
	<?php foreach($msgArr as $key => $value) {
		echo 'msgArr["'.$key.'"] = "'.$value.'"; ';
	} ?>;
	
</script>
<script type="text/javascript" src="js/lib.js"></script>
<link rel="stylesheet" href="skin/main.css">
<title><?php echo getLangMsg('app_title')?></title>
</head>
<body>
	<div class="background">
		<div class="container">
			<div id="html_custom_message_text_info" class="header_msg">
				CallMe Button
			</div>
			<!-- Completed from javascript with error messages -->
			<div id="msg_warn" class="warning" style="display:none">
				
			</div>
			<div id="textInfo">
				<?php echo getLangMsg('help_msg')?>
			</div>
			<div class="input">
				<input type="text" size="30" id="phone_number" name="phone_number" value="" />
			</div>
			<!-- Call button -->
			<div class="button">
				<button type="button" onclick="verifyFieldValue()" ><?php echo getLangMsg('btn_callme')?></button>
			</div>
		</div>
	</div>
</body>
</html>