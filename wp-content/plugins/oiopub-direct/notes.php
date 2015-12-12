<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

//define vars
define('OIOPUB_NOTES', 1);
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

//get type
$id = intval($_GET['id']);
$type = oiopub_clean($_GET['type']);

//get data
if($type == "custom") {
	$notes = $oiopub_db->GetOne("SELECT item_notes FROM " . $oiopub_set->dbtable_purchases . " WHERE item_id='$id'");
} elseif($type == "paylog") {
	$notes = $oiopub_db->GetOne("SELECT payment_log FROM " . $oiopub_set->dbtable_purchases . " WHERE item_id='$id'");
}

//template vars
$templates = array();
$templates['page'] = "purchase_notes";
$templates['title'] = __oio("Purchase Notes");
$templates['notes'] = str_replace("\n", "<br />", trim($notes));

//load template
include_once($oiopub_set->folder_dir . "/templates/core/main.tpl");

?>