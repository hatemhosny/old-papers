<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

//new purchase?
if(!isset($item->item_channel)) {
	$item->item_channel = 3;
	$item->item_type = (int) oiopub_var('type', 'get');
	$item->post_id = (int) oiopub_var('p', 'get');
	$item->item_nofollow = 1;
}

//get zone
if($item->item_type == 4) {
	$iz = "inline_links";
	$ad_name = __oio("Intext Link"); 
} else {
	$iz = "inline_ads";
	if($oiopub_set->inline_ads['selection'] == 1) {
		$ad_name = __oio("Video");
	} elseif($oiopub_set->inline_ads['selection'] == 2) {
		$ad_name = __oio("Banner");
	} elseif($oiopub_set->inline_ads['selection'] == 2) {
		$ad_name = __oio("RSS Feed");
	} else {
		$ad_name = '';
	}
}

//channel active?
if($oiopub_set->inline_total <= 0 || ($item->item_type > 0 && ($oiopub_set->{$iz}['enabled'] != 1 || $oiopub_set->{$iz}['price'][0] <= 0))) {
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

//default values
$item->item_url = empty($item->item_url) ? 'http://' : $item->item_url;
$item->item_page = empty($item->item_page) ? 'http://' : $item->item_page;

//template vars
$templates = array();
$templates['page'] = "purchase_inline";

//set page title
if($item->payment_status == 1) {
	$templates['title'] = __oio("Edit Inline Ad");
} elseif($item->item_status == 3) {
	$templates['title'] = __oio("Renew Inline Ad");
} else {
	$templates['title'] = __oio("Inline Ad Purchase");
}

/* TEMPLATE FUNCTIONS */

//zone menu
function oiopub_zone_select($name, $type, $width, $order) {
	global $oiopub_set;
	$array = array( 0 => "-- " . __oio("select") . " --" );
	if($oiopub_set->inline_ads['enabled'] == 1) {
		if($oiopub_set->inline_ads['price'][0] > 0) {
			if($oiopub_set->inline_ads['selection'] == 1) $ad_name = __oio("Inline Video Ad");
			if($oiopub_set->inline_ads['selection'] == 2) $ad_name = __oio("Inline Banner Ad");
			if($oiopub_set->inline_ads['selection'] == 3) $ad_name = __oio("Inline RSS Feed Ad");
			$array[$oiopub_set->inline_ads['selection']] = $ad_name;
		}
	}
	if($oiopub_set->inline_links['enabled'] == 1) {
		if($oiopub_set->inline_links['price'][0] > 0) {
			$array[4] = __oio("Intext Link");
		}
	}
	return oiopub_dropmenu_kv($array, $name, $type, $width, "document.type.submit()", 0, $order);
}

//zone available
function oiopub_zone_available($type) {
	global $oiopub_set;
	$channel = 3;
	$type = intval($type);
	$iz = "inline_" . (($type == 4) ? "links" : "ads");
	if($type == 4) {
		return __oio("%s links per post allowed", array( $oiopub_set->inline_links['max'] ));
	}
	return oiopub_next_available($channel, $type);
}

//pricing menu
function oiopub_price_select($name, $type, $width, $order) {
	global $oiopub_set;
	if($type == 4) {
		$iz = "inline_links";
	} else {
		$iz = "inline_ads";
	}
	$cost = intval($_POST['oio_pricing']);
	$array = array( 0 => "-- " . __oio("select") . " --" );
	$count = count($oiopub_set->{$iz}['price']);
	for($z=0; $z < $count; $z++) {
		if($oiopub_set->{$iz}['price'][$z] > 0) {
			$y = $z + 1;
			if(isset($item->payment_processor) && isset($oiopub_set->{$item->payment_processor})) {
				$price = isset($oiopub_set->{$item->payment_processor}['credits_ratio']) ? $oiopub_set->{$item->payment_processor}['credits_ratio'] * $oiopub_set->{$iz}['price'][$z] : $oiopub_set->{$iz}['price'][$z];
				$currency = " " . (isset($oiopub_set->{$item->payment_processor}['currency']) ? $oiopub_set->{$item->payment_processor}['currency'] : $oiopub_set->general_set['currency']);
				$model = isset($oiopub_set->{$iz}['model']) ? $oiopub_set->{$iz}['model'] : "days";
				$duration = ($oiopub_set->{$iz}['duration'][$z] == 0 ? " " . __oio("permanent") : ", " . number_format($oiopub_set->{$iz}['duration'][$z], 0) . " " . __oio($model));
			} else {
				$price = $oiopub_set->{$iz}['price'][$z];
				$currency = " " . $oiopub_set->general_set['currency'];
				$model = isset($oiopub_set->{$iz}['model']) ? $oiopub_set->{$iz}['model'] : "days";
				$duration = ($oiopub_set->{$iz}['duration'][$z] == 0 ? " " . __oio("permanent") : number_format($oiopub_set->{$iz}['duration'][$z], 0) . " " . __oio($model));
			}
			if($price == 0) {
				$array[$y] = __oio("Free Submission") . " (" . trim($duration) . ")";
			} else {
				$array[$y] = oiopub_amount($price, $currency) . " - " . $duration;
			}
		}
	}
	if($count == 1) {
		$cost = 1;
	}
	return oiopub_dropmenu_kv($array, $name, $cost, $width, "", 0, $order);
}

?>