<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

//init module
function oiopub_tracker_init(&$class) {
	global $oiopub_hook;
	$oiopub_hook->add('oiopub_footer', array(&$class, 'tracking_code'), 1);
	add_action('wp_footer', array(&$class, 'tracking_code'), 1);
}

//cache check
function oiopub_tracker_cache() {
	//get active plugins
	$plugins = (array) get_option('active_plugins');
	//loop through plugins
	foreach($plugins as $key=>$val) {
		if(strpos($val, 'wp-cache') !== false || strpos($val, 'w3-total-cache') !== false) {
			return true;
		}
	}
	return false;
}

?>