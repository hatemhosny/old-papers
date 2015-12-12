<?php

/*
Copyright (C) 2008  Simon Emery

iThis file is part of OIOpublisher Direct.
*/

//define vars
define('OIOPUB_PURCHASE', 1);

//init
include_once(str_replace("\\", "/", dirname(__FILE__)) . "/index.php");

//don't show errors
@ini_set('display_errors', 0);

//is plugin enabled?
if($oiopub_set->enabled != 1) {
	die("Plugin Currently Offline");
}

//redirect to custom page?
if(!empty($oiopub_set->general_set['buypage']) && count($_GET) == 0 && !defined('NO_HEADER')) {
	//header("Location: " . $oiopub_set->general_set['buypage']);
	//exit();
}

//sort methods
ksort($oiopub_set->arr_payment);

//add default method
$oiopub_set->arr_payment = array_merge(array( 0 => "-- " . __oio("select") . " --" ), $oiopub_set->arr_payment);

//start session
oiopub_session_start();

//set vars
$item = new oiopub_std;
$rand_id = oiopub_var('expired', 'get') ? oiopub_var('expired', 'get') : oiopub_var('rand', 'get');

//get ad type?
if(!empty($rand_id)) {
	//get item data
	$item = $oiopub_db->GetRow("SELECT * FROM " . $oiopub_set->dbtable_purchases . " WHERE rand_id='$rand_id'");
	//valid item?
	if($item->item_status != 3 && $oiopub_set->general_set['edit_ads'] != 1) {
		$item = "";
	}
	//security check?
	if($item && $item->item_status != 3) {
		if(!isset($_SESSION['oio_stats_email']) || $_SESSION['oio_stats_email'] != md5($item->adv_email)) {
			header("Location: stats.php?rand=" . $rand_id);
			exit();
		}
	}
}

//where next?
if(empty($_GET['do'])) {
	if(isset($_POST['process']) && $_POST['process'] == 'yes') {
		if($_POST['purchase-type'] == 1) {
			header('Location: purchase.php?do=link');
			exit();
		} elseif($_POST['purchase-type'] == 2) {
			header('Location: purchase.php?do=post');
			exit();
		} elseif($_POST['purchase-type'] == 3) {
			header('Location: purchase.php?do=inline');
			exit();
		} elseif($_POST['purchase-type'] == 4) {
			header('Location: purchase.php?do=banner');
			exit();
		} elseif($_POST['purchase-type'] == 5) {
			header('Location: purchase.php?do=custom');
			exit();
		}
	}
}

//include purchase class
if(!isset($oiopub_purchase)) {
	include_once($oiopub_set->folder_dir . "/include/purchase.php");
	$oiopub_purchase = new oiopub_purchase();
}

//include files
if(!isset($_GET['do']) && !$item) {
	include_once($oiopub_set->folder_dir . '/purchase/start.php');
} elseif($_GET['do'] == 'post' || $item->item_channel == 1) {
	include_once($oiopub_set->folder_dir . '/purchase/posts.php');
} elseif($_GET['do'] == 'link' || $item->item_channel == 2) {
	include_once($oiopub_set->folder_dir . '/purchase/links.php');
} elseif($_GET['do'] == 'inline' || $item->item_channel == 3) {
	include_once($oiopub_set->folder_dir . '/purchase/inline.php');
} elseif($_GET['do'] == 'custom' || $item->item_channel == 4) {
	include_once($oiopub_set->folder_dir . '/purchase/custom.php');
} elseif($_GET['do'] == 'banner' || $item->item_channel == 5) {
	include_once($oiopub_set->folder_dir . '/purchase/banners.php');
} elseif($_GET['do'] == '12345') {
	echo "OIOpub Direct";
	exit();
} else {
	include_once($oiopub_set->folder_dir . '/purchase/start.php');
}

//load template
include_once($oiopub_set->folder_dir . "/templates/core/main.tpl");

?>