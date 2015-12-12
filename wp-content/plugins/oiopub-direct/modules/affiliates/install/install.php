<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

//set global vars
global $oiopub_set, $oiopub_db, $oiopub_tables;

//add options
oiopub_add_config('affiliates', array("install"=>0, "enabled"=>0, "fixed"=>0, "level"=>20, "maturity"=>14, "renew"=>0, "attach"=>0, "terms"=>"", "help"=>"", "aff_url"=>$oiopub_set->site_url . "/"));

//add affiliates table
$sql1 = "CREATE TABLE IF NOT EXISTS " . $oiopub_set->dbtable_affiliates . " (
`id` int(10) NOT NULL auto_increment,
`level` int(10) NOT NULL default '0',
`coupon` int(10) NOT NULL default '0',
`name` varchar(128) NOT NULL default '',
`email` varchar(128) NOT NULL default '',
`password` varchar(32) NOT NULL default '',
`salt` varchar(8) NOT NULL default '',
`paypal` varchar(64) NOT NULL default '',
`status` int(10) NOT NULL default '1',
PRIMARY KEY (`id`),
KEY `email` (`email`),
KEY `paypal` (`paypal`)
) ENGINE=MyISAM" . $oiopub_db->default_charset() . ";";

//add affiliates_hits table
$sql2 = "CREATE TABLE IF NOT EXISTS " . $oiopub_set->dbtable_affiliates_hits . " (
`id` int(10) NOT NULL auto_increment,
`ref_id` int(10) NOT NULL default '0',
`time` bigint(20) NOT NULL default '0',
`date` date NOT NULL,
`ip` bigint(20) NOT NULL default '0',
`page` varchar(255) NOT NULL default '',
PRIMARY KEY (`id`),
KEY `ref_id` (`ref_id`,`date`)
) ENGINE=MyISAM" . $oiopub_db->default_charset() . ";";

//add affiliates_sales table
$sql3 = "CREATE TABLE IF NOT EXISTS " . $oiopub_set->dbtable_affiliates_sales . " (
`id` int(10) NOT NULL auto_increment,
`affiliate_id` int(10) NOT NULL default '0',
`affiliate_paid` int(10) NOT NULL default '0',
`affiliate_amount` decimal(10,2) NOT NULL default '0.00',
`affiliate_currency` varchar(8) NOT NULL default '',
`purchase_id` int(10) NOT NULL default '0',
`purchase_type` int(10) NOT NULL default '0',
`purchase_time` bigint(20) NOT NULL default '0',
`purchase_payment` int(10) NOT NULL default '0',
PRIMARY KEY (`id`),
KEY `affiliate_id` (`affiliate_id`,`affiliate_paid`),
KEY `purchase_id` (`purchase_id`)
) ENGINE=MyISAM" . $oiopub_db->default_charset() . ";";

//add tables
$oiopub_db->query($sql1);
$oiopub_db->query($sql2);
$oiopub_db->query($sql3);

//add to tables array
$oiopub_tables[] = $sql1;
$oiopub_tables[] = $sql2;
$oiopub_tables[] = $sql3;

?>