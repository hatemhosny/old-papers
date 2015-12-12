<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

//define vars
define('OIOPUB_EXPORT', 1);
define('OIOPUB_ADMIN', 1);

//init
include_once(str_replace("\\", "/", dirname(__FILE__)) . "/index.php");

//is plugin enabled?
if($oiopub_set->enabled != 1) {
	die("Plugin Currently Offline");
}

//check access
if(!oiopub_auth_check()) {
	die("Access Denied");
}

//clear vars
$array = array();

//get vars
foreach($_GET as $key => $val) {
	if($key != "do" && $key != "type") {
		$args[$key] = $val;
	}
}

//get report
$output = oiopub_reports($_GET['do'], $_GET['type'], $args);
$output = empty($output) ? __oio("No data to export") : $output;

//output
echo $output;
exit();

?>