<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/


//define vars
define('OIOPUB_LOAD_LITE', 1);

//dont cache
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

//301 redirect
header("HTTP/1.1 301 Moved Permanently");

//init
include_once("../../index.php");

//set vars
$url = '';
$id = oiopub_var('id', 'get');

//log click?
if(isset($oiopub_plugin['tracker'])) {
	$url = $oiopub_plugin['tracker']->log_click($id);
}

//format url
$url = $url ? $url : $oiopub_set->site_url;
$url = str_replace("amp;", "", $url);

//redirect user
header("Location: " . $url);
exit();