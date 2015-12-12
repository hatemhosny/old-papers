<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

//define vars
define('OIOPUB_APPROVALS', 1);
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

//get vars
$id = intval($_GET['id']);
$status = oiopub_clean($_GET['status']);
$opt = oiopub_clean($_GET['opt']);
$type = oiopub_clean($_GET['type']);

//process
oiopub_approve($status, $id);

//redirect user
header('Location: ' . $oiopub_set->admin_url . '/admin.php?page=oiopub-manager.php&opt=' . $opt . '&type=' . $type);
exit();

?>