<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

//template vars
$templates = array();
$templates['page'] = "purchase_start";
$templates['title'] = __oio("Make Your Purchase");

/* TEMPLATE FUNCTIONS */

//item menu
function oiopub_zone_select() {
	global $oiopub_set;
	if($oiopub_set->grand_total <= 0) {
		return __oio("Advertising options on this website are currently unavailable");
	}
	$array = array( 0 => "- " . __oio("filter by ad type") . " -" );
	if($oiopub_set->links_total > 0) {
		$array[1] = __oio("Text Ads");
	}
	if($oiopub_set->banners_total > 0) {
		$array[4] = __oio("Banner Ads");
	}
	if($oiopub_set->inline_total > 0) {
		$array[3] = __oio("Inline Ads");
	}
	if($oiopub_set->posts_total > 0) {
		$array[2] = __oio("Paid Reviews");
	}
	if($oiopub_set->custom_total > 0) {
		$array[5] = __oio("Custom Items");
	}
	return oiopub_dropmenu_kv($array, "purchase-type", "", 150, "document.type.submit()");
}

?>