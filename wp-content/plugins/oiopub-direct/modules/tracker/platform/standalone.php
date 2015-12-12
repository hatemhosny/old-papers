<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

//init module
function oiopub_tracker_init($this) {
	global $oiopub_hook;
	$oiopub_hook->add('oiopub_footer', array(&$this, 'tracking_code'), 1);
}

//cache check
function oiopub_tracker_cache() {
	return false;
}

?>