<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

//define vars
define('OIOPUB_GLOBAL', 1);

//init
include_once(str_replace("\\", "/", dirname(__FILE__)) . "/index.php");

//api vars
$api_key = oiopub_clean(urldecode($_POST['api_key']));
$api_data = unserialize(stripslashes(urldecode($_POST['api_data'])));

//derive vars
$api_pass = oiopub_clean($api_data['global_pass']);

//is plugin enabled?
if($oiopub_set->enabled != 1) {
	echo "PLUGIN-DISABLED";
	exit();
}

//check api key
$oiopub_api->check_key($api_key, 1);

//check api key
$oiopub_api->check_pass($api_pass, 1);

//get settings data
if($api_data['action'] == 'get-settings') {
	//query data
	$settings = $oiopub_db->CacheGetAll("SELECT * FROM " . $oiopub_set->dbtable_config);
	$forbidden = array( "global_pass", "hash", "cron_jobs", "cron_running", "api_key", "api_valid" );
	//get output
	echo $oiopub_api->get_settings($settings, $forbidden);
	exit();
}

//get puchases data
if($api_data['action'] == 'get-purchases') {
	//purchase vars
	$item_channel = intval($api_data['ic']);
	$item_status = intval($api_data['is']);
	$item_published = intval($api_data['ip']);
	$payment_status = intval($api_data['ps']);
	//sql where
	$sql_where = 'WHERE 1=1';
	if(isset($api_data['ic'])) $sql_where .= " AND item_channel='$item_channel'";
	if(isset($api_data['is'])) $sql_where .= " AND item_status='$item_status'";
	if(isset($api_data['ps'])) $sql_where .= " AND payment_status='$payment_status'";
	if(isset($api_data['ip'])) $sql_where .= " AND item_published='$item_published'";
	//query data
	$purchases = $oiopub_db->GetAll("SELECT * FROM " . $oiopub_set->dbtable_purchases . $sql_where);
	//get ourput
	echo $oiopub_api->get_purchases($purchases);
	exit();
}

//update settings
if($api_data['action'] == 'update-settings') {
	$data = array();
	foreach($req_action as $key => $val) {
		if(oiopub_get_config($key, 0)) {
			$data[$key] = oiopub_clean(urldecode($val));
		}
	}
	//get output
	echo $oiopub_api->update_settings($data);
	exit();
}

//update purchases
if($api_data['action'] == 'update-purchases') {
	//purchase vars
	$id = intval($api_data['id']);
	$status = intval($api_data['status']);
	//get output
	echo $oiopub_api->update_purchases($id, $status);
	exit();
}

?>