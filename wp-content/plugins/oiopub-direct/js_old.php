<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

//define vars
define('OIOPUB_JS', 1);
define('OIOPUB_LOAD_LITE', 1);

//init
include_once(str_replace("\\", "/", dirname(__FILE__)) . "/index.php");

//don't show errors
@ini_set('display_errors', 0);

//is plugin enabled?
if($oiopub_set->enabled != 1) {
	exit();
}

//set vars
$css = '';
$html = '';
$output = 'Invalid Ad Zone';

//clear purchase IDs
$oiopub_set->pids = array();

//sanitize input array
$_GET = array_map('oiopub_clean', $_GET);

//get zone vars
$zone = (int) $_GET['zone'];
$div = oiopub_clean($_GET['div']);
$type = oiopub_clean($_GET['type']);

//no echo
$_GET['echo'] = false;

//ad zone
if($zone > 0) {
	//get function
	$function = "oiopub_" . $type . "_zone";
	//function exists?
	if(function_exists($function)) {
		//get html output
		$html = $function($zone, $_GET);
		//remove line breaks
		$html = trim(str_replace(array("\r\n", "\r", "\n"), "", $html));
	}
	if(empty($html)) {
		//nothing found
		$output = ucfirst($type) . " Ad zone " . $zone . " not defined";
	} else {
		//css & html
		$v = str_replace('.', '', $oiopub_set->version);
		$css = '<link rel="stylesheet" href="' . $oiopub_set->plugin_url_org . '/images/style/output.css?' . $v . '" type="text/css" />';
		$output = '<div class="oio-body">' . $html . '</div>';
	}
}

//js output hook
$oiopub_hook->fire('javascript_output');

//strip elements
$strip = array( "<!--", "//-->", "-->", "<![CDATA[", "]]>" );
$output = str_replace($strip, "", $output);

//format for javascript
$js = oiopub_js($css . $output);

//output
if(!empty($div)) {
	echo "document.getElementById('" . $div . "').innerHTML = '" . $js . "';";
} else {
	echo "document.write('" . $js . "');";
}

?>