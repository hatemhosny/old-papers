<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

//set global vars
global $oiopub_set, $oiopub_db;

//drop tables
$oiopub_db->query("DROP TABLE IF EXISTS " . $oiopub_set->dbtable_tracker_clicks);
$oiopub_db->query("DROP TABLE IF EXISTS " . $oiopub_set->dbtable_tracker_visits);
$oiopub_db->query("DROP TABLE IF EXISTS " . $oiopub_set->dbtable_tracker_archive);

?>