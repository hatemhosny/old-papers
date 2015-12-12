<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

//set global vars
global $oiopub_set, $oiopub_db;

//drop db tables
$oiopub_db->query("DROP TABLE IF EXISTS " . $oiopub_set->dbtable_affiliates);
$oiopub_db->query("DROP TABLE IF EXISTS " . $oiopub_set->dbtable_affiliates_hits);
$oiopub_db->query("DROP TABLE IF EXISTS " . $oiopub_set->dbtable_affiliates_sales);

?>