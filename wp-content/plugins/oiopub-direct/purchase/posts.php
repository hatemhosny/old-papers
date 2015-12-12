<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

//new purchase?
if(!isset($item->item_channel)) {
	$item->item_channel = 1;
	$item->post_author = (int) oiopub_var('author', 'get');
}

//posts active?
if($oiopub_set->posts_total <= 0) {
	header("Location: purchase.php");
}

//$_POST request
if(isset($_POST['process']) && $_POST['process'] == "yes") {
	//start process
	$oiopub_purchase->init($_POST, $item->item_channel, $item->post_author);
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

//get categories
if(function_exists('oiopub_category_list')) {
	$cat_array = oiopub_category_list();
} else {
	$cat_array = array();
}

//security question
$item->captcha = oiopub_captcha();
$_SESSION['next'] = $item->captcha['answer'];
session_write_close();

//template vars
$templates = array();
$templates['page'] = "purchase_posts";

//set page title
if($item->payment_status == 1) {
	$templates['title'] = __oio("Edit Paid Review");
} elseif($item->item_status == 3) {
	$templates['title'] = __oio("Renew Paid Review");
} else {
	$templates['title'] = __oio("Paid Review Purchase");
}

/* TEMPLATE FUNCTIONS */

//zone menu
function oiopub_zone_select($name, $type, $width, $order) {
	global $oiopub_set;
	$array = array( 0 => "-- " . __oio("select") . " --" );
	if($oiopub_set->posts['price_blogger'] > 0) {
		$array[1] = __oio("Website Owner");
	} if($oiopub_set->posts['price_adv'] > 0 || $oiopub_set->posts['price_free'] == 1) {
		$array[2] = __oio("You");
	}
	return oiopub_dropmenu_kv($array, $name, $type, $width, "document.type.submit()", 0, $order);
}

//zone available
function oiopub_zone_available($type) {
	global $oiopub_set, $oiopub_db;
	$channel = 1;
	$type = intval($type);
	$allowed_time = time() - (86400 * $oiopub_set->posts['max_posts_days']);
	$check_rows = $oiopub_db->GetOne("SELECT COUNT(*) FROM " . $oiopub_set->dbtable_purchases . " WHERE item_channel='$channel' AND item_status < '2' AND submit_time > $allowed_time");
	if($check_rows >= $oiopub_set->posts['max_posts_num'] && $oiopub_set->posts['max_posts_num'] > 0) {
		$latest_time = $oiopub_db->GetOne("SELECT submit_time FROM " . $oiopub_set->dbtable_purchases . " WHERE item_channel='$channel' AND item_status < '2' ORDER BY submit_time DESC");
		$new_time = ceil(($latest_time + (86400 * $oiopub_set->posts['max_posts_days']) - time()) / 3600);
		//hours or days?
		$due = $new_time > 48 ? ceil($new_time / 24) . " " . __oio("days") : $new_time . " " . __oio("hours");
		return __oio("A new post cannot be submitted for another %s", array( $due ));
	}
	return __oio("available now");
}

//price select
function oiopub_price_select() {
	global $oiopub_set, $item;
	if($item->post_author == 1) {
		$price = $oiopub_set->posts['price_blogger'];
		$currency = $oiopub_set->general_set['currency'];
	} else {
		$price = $oiopub_set->posts['price_adv'];
		$currency = $oiopub_set->general_set['currency'];
	}
	if(isset($item->payment_processor) && isset($oiopub_set->{$item->payment_processor})) {
		$price = isset($oiopub_set->{$item->payment_processor}['credits_ratio']) ? $oiopub_set->{$item->payment_processor}['credits_ratio'] * $price : $price;
		$currency = " " . (isset($oiopub_set->{$item->payment_processor}['currency']) ? $oiopub_set->{$item->payment_processor}['currency'] : $oiopub_set->general_set['currency']);
	}
	if($price == 0) {
		return $res = __oio("Free Submission");
	} else {
		$res = oiopub_amount($price, $currency);
	}
	return $res;
}

?>