<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

//set global vars
global $oiopub_set, $oiopub_db;

//v2.01 (affiliates)
if($oiopub_set->affiliates['install'] < "2.01") {
	$oiopub_db->query("ALTER TABLE ". $oiopub_set->dbtable_affiliates_sales . " ADD affiliate_currency varchar(8) NOT NULL");
	$oiopub_db->query("UPDATE " . $oiopub_set->dbtable_affiliates_sales . " SET affiliate_currency='" . $oiopub_set->general_set['currency'] . "'");
}

//v2.05 (affiliates)
if($oiopub_set->affiliates['install'] < "2.05") {
	$oiopub_set->affiliates['help'] = "";
	oiopub_update_config('affiliates', $oiopub_set->affiliates);
}

?>