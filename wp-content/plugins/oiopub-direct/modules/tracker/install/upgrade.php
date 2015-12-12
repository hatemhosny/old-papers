<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

//set global vars
global $oiopub_set, $oiopub_db;

//v2.02 (tracker)
if($oiopub_set->tracker['install'] < "2.02") {
	//setup filters
	$oiopub_set->tracker['ip_filter'] = "blacklist";
	$oiopub_set->tracker['agent_filter'] = "blacklist";
	$oiopub_set->tracker['referer_filter'] = "blacklist";
	oiopub_update_config('tracker', $oiopub_set->tracker);
	oiopub_add_config('ip_filter_data', array());
	oiopub_add_config('agent_filter_data', array());
	oiopub_add_config('referer_filter_data', array());
}

//v2.04 (tracker)
if($oiopub_set->tracker['install'] < "2.05") {
	//update tracker tables
	$this->tracking_update();
	$oiopub_db->query("DROP TABLE IF EXISTS " . $oiopub_set->dbtable_tracker_clicks);
	$oiopub_db->query("DROP TABLE IF EXISTS " . $oiopub_set->dbtable_tracker_visits);
	@include($oiopub_set->modules_dir . '/' . $oiopub_set->tracker_folder . '/install/install.php');
}

?>