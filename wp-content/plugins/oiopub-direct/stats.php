<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

//define vars
define('OIOPUB_STATS', 1);

//init
include_once(str_replace("\\", "/", dirname(__FILE__)) . "/index.php");

//don't show errors
@ini_set('display_errors', 0);

//is plugin enabled?
if($oiopub_set->enabled != 1) {
	die("Plugin Currently Offline");
}

//start session
oiopub_session_start();

//set vars
$email = '';
$output = '';
$action = false;
$pdata = array();
$purchase = array();
$allow_access = false;
$purchase_list = array();
$rand_id = oiopub_clean($_GET['rand']);

//$_POST form?
if(isset($_REQUEST['email']) && isset($_REQUEST['rand'])) {
	//set vars
	$rand_id = oiopub_clean($_REQUEST['rand']);
	$email = strtolower(oiopub_clean($_REQUEST['email']));
	//valid data?
	if($email && $rand_id) {
		//db check passed?
		if($check = $oiopub_db->GetRow("SELECT * FROM " . $oiopub_set->dbtable_purchases . " WHERE rand_id='$rand_id' AND LOWER(adv_email)='$email'")) {
			//set session
			$_SESSION['oio_stats_email'] = md5($email);
			//redirect?
			if(isset($_GET['email']) && $_GET['email']) {
				header("Location: stats.php?rand=" . $rand_id);
				exit();
			}
		}
	}
}

//get purchase data?
if(!empty($rand_id)) {
	$purchase = $oiopub_db->GetRow("SELECT * FROM " . $oiopub_set->dbtable_purchases . " WHERE rand_id='$rand_id'");
	$purchase->adv_email = strtolower($purchase->adv_email);
	$pdata = oiopub_adtype_info($purchase);
}

//give admin access?
if($purchase && (oiopub_auth_check() || $oiopub_set->demo)) {
	$_SESSION['oio_stats_email'] = md5($purchase->adv_email);
}

//valid session match?
if($purchase && isset($_SESSION['oio_stats_email']) && $_SESSION['oio_stats_email'] === md5($purchase->adv_email)) {
	$allow_access = true;
}

//find all data?
if($allow_access) {
	//get data
	$all_purchases = $oiopub_db->GetAll("SELECT item_channel,item_type,submit_time,rand_id FROM " . $oiopub_set->dbtable_purchases . " WHERE LOWER(adv_email)='$purchase->adv_email' AND adv_email!='' ORDER BY submit_time DESC");
	//loop through array
	foreach($all_purchases as $p) {
		$adtype = oiopub_adtype_info($p);
		$purchase_list[$p->rand_id] = $adtype['type'] . " - " . date("jS M Y", $p->submit_time) . " &nbsp; (" . $p->rand_id . ")";
	}
}

//template vars
$templates = array();
$templates['page'] = "purchase_stats";
$templates['title'] = __oio("Advertising dashboard") . '<form method="get" action="' . $oiopub_set->request_uri . '" name="purchase" style="margin:8px 0 0 0; font-size:12px;"><b>' . __oio("My ads") . ':</b> &nbsp; ' . oiopub_dropmenu_kv($purchase_list, 'rand', $purchase->rand_id, 340, 'document.purchase.submit()') . '</form>';
$templates['title_head'] = __oio("Advertising dashboard");

//load template
include_once($oiopub_set->folder_dir . "/templates/core/main.tpl");

?>