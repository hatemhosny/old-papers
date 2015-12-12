<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

//new purchase?
if(!isset($item->item_channel)) {
	$item->item_channel = 4;
	$item->item_type = (int) oiopub_var('item', 'get');
}

//get zone
$cn = "custom_" . $item->item_type;

//channel active?
if($oiopub_set->custom_total <= 0 || ($item->item_type > 0 && $oiopub_set->{$cn}['price'] <= 0)) {
	header("Location: purchase.php");
}

//$_POST request
if(isset($_POST['process']) && $_POST['process'] == "yes") {
	//start process
	$oiopub_purchase->init($_POST, $item->item_channel, $item->item_type);
	//update or insert?
	if(!empty($rand_id)) {
		$item = $oiopub_purchase->update($rand_id);
	} else {
		$item = $oiopub_purchase->insert();
	}
} elseif(count($oiopub_set->arr_payment) == 2) {
	//single processor
	$keys = array_keys($oiopub_set->arr_payment);
	$item->payment_processor = $keys[1];
}

//security question
$item->captcha = oiopub_captcha();
$_SESSION['next'] = $item->captcha['answer'];
session_write_close();

//template vars
$templates = array();
$templates['page'] = "purchase_custom";

//set page title
if($item->payment_status == 1) {
	$templates['title'] = __oio("Edit Custom Purchase");
} elseif($item->item_status == 3) {
	$templates['title'] = __oio("Renew Custom Purchase");
} else {
	$templates['title'] = __oio("Custom Purchase");
}

/* TEMPLATE FUNCTIONS */

//zone menu
function oiopub_zone_select($name, $type, $width, $order) {
	global $oiopub_set;
	$array = array( 0 => "-- " . __oio("select") . " --" );
	for($z=1; $z <= $oiopub_set->custom_num; $z++) {
		$cn = "custom_" . $z;
		if($oiopub_set->{$cn}['price'] > 0) {
			$array[$z] = $oiopub_set->{$cn}['title'];
		}
	}
	return oiopub_dropmenu_kv($array, $name, $type, $width, "document.type.submit()", 1, $order);
}

//zone available
function oiopub_zone_available($type) {
	global $oiopub_set;
	$channel = 4;
	$type = intval($type);
	$cn = "custom_" . $type;
	if($oiopub_set->{$cn}['max'] == 0) {
		return __oio("unlimited number available");
	}
	return oiopub_next_available($channel, $type);
}

//pricing menu
function oiopub_price_select() {
	global $oiopub_set, $item;
	$cn = "custom_" . $item->item_type;
	if(isset($item->payment_processor) && isset($oiopub_set->{$item->payment_processor})) {
		$price = isset($oiopub_set->{$item->payment_processor}['credits_ratio']) ? $oiopub_set->{$item->payment_processor}['credits_ratio'] * $oiopub_set->{$cn}['price'] : $oiopub_set->{$cn}['price'];
		$currency = " " . (isset($oiopub_set->{$item->payment_processor}['currency']) ? $oiopub_set->{$item->payment_processor}['currency'] : $oiopub_set->general_set['currency']);
		$duration = ($oiopub_set->{$cn}['duration'][$z] == 0 ? " " . __oio("permanent") : number_format($oiopub_set->{$cn}['duration'], 0) . " " . __oio("days"));
	} else {
		$price = $oiopub_set->{$cn}['price'];
		$currency = " " . $oiopub_set->general_set['currency'];
		$duration = ($oiopub_set->{$cn}['duration'] == 0 ? " " . __oio("permanent") : number_format($oiopub_set->{$cn}['duration'], 0) . " " . __oio("days"));
	}
	if($price == 0) {
		$res = __oio("Free Submission") . " (" . trim($duration) . ")";
	} else {
		$res = oiopub_amount($price, $currency) . " - " . $duration;
	}
	return $res;
}

?>