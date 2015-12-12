<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

//define vars
define('OIOPUB_API', 1);

//init
include_once(str_replace("\\", "/", dirname(__FILE__)) . "/index.php");

//misc values
$time = time();
$ip = ip2long($_SERVER['REMOTE_ADDR']);
	
//post values
$api_key = oiopub_decode($_POST['api_key']);
$api_data = unserialize(stripslashes(urldecode($_POST['api_data'])));

//api settings (xml)
if($_GET['do'] == "settings") {
	//query data
	$settings = $oiopub_db->GetAll("SELECT * FROM " . $oiopub_set->dbtable_config . " WHERE api_load='1' ORDER BY name");
	$forbidden = array( "global_pass", "hash", "cron_jobs", "cron_running", "api_key", "api_valid" );
	//get output
	echo $oiopub_api->get_settings($settings, $forbidden);
	exit();
}

//postback check
if($_POST['action'] == "postback_check") {
	$api_rand_key = oiopub_decode($_POST['api_rand_key']);
	$api_rand_val = oiopub_decode($_POST['api_rand_val']);
	echo $oiopub_api->postback_check($api_rand_key, $api_rand_val);
	exit();
}

//is plugin enabled?
if($oiopub_set->enabled != 1) {
	die("Plugin Currently Offline");
}

//check api key
$oiopub_api->check_key($api_key, 1);

//postback check
$postback = $oiopub_api->postback_call();

//postback check passed?
if($postback != "SUCCESS") {
	echo "FAILED";
	exit();
}

//insert marketplace purchase
if($api_data['action'] == "insert_purchase") {
	if(!empty($api_data['oio_rand_id']) && $api_data['oio_channel'] > 0 && $api_data['oio_type'] > 0) {
		//duplicate submission?
		$duplicate_id = $oiopub_db->GetOne("SELECT item_id FROM " . $oiopub_set->dbtable_purchases . " WHERE rand_id='$api_data[oio_rand_id]' AND item_channel='$api_data[oio_channel]'");
		if($duplicate_id > 0) {
			echo "SUCCESS";
			exit();
		}
		//process results
		include_once($oiopub_set->folder_dir . "/include/purchase.php");
		$purchase = new oiopub_purchase($api_data, $api_data['oio_channel'], $api_data['oio_type']);
		//attempt insert
		$result = $purchase->insert(0, $api_data['oio_submit_api']);
		//display results
		if($result == true) {
			echo "SUCCESS";
			exit();
		} elseif(!empty($result->misc['api'])) {
			echo $result->misc['api'];
			exit();
		} else {
			echo "FAILED";
			exit();
		}
	}
}

//api hook
$oiopub_hook->fire('api_display', array($api_key, $api_data));

//left over
echo "FAILED";
exit();

?>