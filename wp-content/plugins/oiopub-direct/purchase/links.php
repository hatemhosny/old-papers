<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

//new purchase?
if(!isset($item->item_channel)) {
	$item->item_channel = 2;
	$item->item_type = (int) oiopub_var('zone', 'get');
	$item->item_nofollow = 1;
}
//get zone
$lz = "links_" . $item->item_type;

//channel active?
if($oiopub_set->links_total <= 0 || ($item->item_type > 0 && ($oiopub_set->{$lz}['enabled'] != 1 || ($oiopub_set->{$lz}['price'][0] <= 0 && empty($oiopub_set->{$lz}['link_exchange']))))) {
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

//get categories
if(function_exists('oiopub_post_categories') && $oiopub_set->{$lz}['cats'] == 1) {
	$cat_array = oiopub_post_categories();
}

//security question
$item->captcha = oiopub_captcha();
$_SESSION['next'] = $item->captcha['answer'];
session_write_close();

//default values
$item->item_url = empty($item->item_url) ? 'http://' : $item->item_url;

//template vars
$templates = array();
$templates['page'] = "purchase_links";

//set page title
if($item->payment_status == 1) {
	$templates['title'] = __oio("Edit Text Ad");
} elseif($item->item_status == 3) {
	$templates['title'] = __oio("Renew Text Ad");
} else {
	$templates['title'] = __oio("Text Ad Purchase");
}

/* TEMPLATE FUNCTIONS */

//item menu
function oiopub_zone_select($name, $type, $width, $order) {
	global $oiopub_set;
	$array = array( 0 => "-- " . __oio("select") . " --" );
	for($z=1; $z <= $oiopub_set->links_zones; $z++) {
		$lz = "links_" . $z;
		if($oiopub_set->{$lz}['enabled'] == 1) {
			if($oiopub_set->{$lz}['price'][0] > 0 || !empty($oiopub_set->{$lz}['link_exchange'])) {
				$array[$z] = $oiopub_set->{$lz}['title'];
			}
		}
	}
	return oiopub_dropmenu_kv($array, $name, $type, $width, "document.type.submit()", 1, $order);
}

//zone available
function oiopub_zone_available($type) {
	global $oiopub_set;
	$channel = 2;
	$type = intval($type);
	$lz = "links_" . $type;
	return oiopub_next_available($channel, $type);
}

//pricing menu
function oiopub_price_select($name, $type, $width, $order) {
	global $oiopub_set, $item;
	$lz = "links_" . $type;
	$cost = intval($_POST['oio_pricing']);
	$array = array( 0 => "-- " . __oio("select") . " --" );
	$count = count($oiopub_set->{$lz}['price']);
	for($z=0; $z < $count; $z++) {
		if($oiopub_set->{$lz}['price'][$z] > 0 || !empty($oiopub_set->{$lz}['link_exchange'])) {
			$y = $z + 1;
			if(isset($item->payment_processor) && isset($oiopub_set->{$item->payment_processor})) {
				$price = isset($oiopub_set->{$item->payment_processor}['credits_ratio']) ? $oiopub_set->{$item->payment_processor}['credits_ratio'] * $oiopub_set->{$lz}['price'][$z] : $oiopub_set->{$lz}['price'][$z];
				$currency = " " . (isset($oiopub_set->{$item->payment_processor}['currency']) ? $oiopub_set->{$item->payment_processor}['currency'] : $oiopub_set->general_set['currency']);
				$model = isset($oiopub_set->{$lz}['model']) ? $oiopub_set->{$lz}['model'] : "days";
				$duration = ($oiopub_set->{$lz}['duration'][$z] == 0 ? " " . __oio("permanent") :number_format($oiopub_set->{$lz}['duration'][$z], 0) . " " . __oio($model));
			} else {
				$price = $oiopub_set->{$lz}['price'][$z];
				$currency = " " . $oiopub_set->general_set['currency'];
				$model = isset($oiopub_set->{$lz}['model']) ? $oiopub_set->{$lz}['model'] : "days";
				$duration = ($oiopub_set->{$lz}['duration'][$z] == 0 ? " " . __oio("permanent") : number_format($oiopub_set->{$lz}['duration'][$z], 0) . " " . __oio($model));
			}
			if($price == 0) {
				$array[$y] = __oio("Link Exchange") . " (" . trim($duration) . ")";
			} else {
				$array[$y] = oiopub_amount($price, $currency) . " - " . $duration;
			}
		}
	}
	if($count == 1) {
		$cost = 1;
		if($price == 0) {
			$item->payment_amount = '0.00';
		}
	}
	if(!empty($oiopub_set->{$lz}['link_exchange'])) {
		$onselect = "add_field(\"process\", \"hidden\", \"suppress_errors\", \"suppress\", \"1\"); document.process.submit();";
	}
	return oiopub_dropmenu_kv($array, $name, $cost, $width, $onselect, 0, $order);
}

?>