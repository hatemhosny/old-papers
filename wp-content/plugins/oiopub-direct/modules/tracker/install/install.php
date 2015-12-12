<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

//set global vars
global $oiopub_set, $oiopub_db, $oiopub_tables;

//add options
oiopub_add_config('tracker', array('install'=>0, 'enabled'=>1, 'reports'=>1, 'share'=>1, 'ip_filter'=>'blacklist', 'agent_filter'=>'blacklist', 'referer_filter'=>'blacklist'));
oiopub_add_config('tracker_cron', 0);
oiopub_add_config('ip_filter_data', array());
oiopub_add_config('agent_filter_data', array());
oiopub_add_config('referer_filter_data', array());

//v1 compatibility
$oiopub_db->query("DROP TABLE IF EXISTS " . $oiopub_set->dbtable_tracker_clicks);
$oiopub_db->query("DROP TABLE IF EXISTS " . $oiopub_set->dbtable_tracker_visits);

//add clicks table
$sql1 = "CREATE TABLE IF NOT EXISTS " . $oiopub_set->dbtable_tracker_clicks . " (
`id` int(10) NOT NULL auto_increment,
`pid` int(10) NOT NULL default '0',
`time` bigint(20) NOT NULL default '0',
`date` date NOT NULL,
`ip` bigint(20) NOT NULL default '0',
`agent` text NOT NULL,
`referer` text NOT NULL,
`status` int(10) NOT NULL default '0',
PRIMARY KEY (`id`)
) ENGINE=MyISAM" . $oiopub_db->default_charset() . ";";

//add visits table
$sql2 = "CREATE TABLE IF NOT EXISTS " . $oiopub_set->dbtable_tracker_visits . " (
`id` int(10) NOT NULL auto_increment,
`pids` text NOT NULL,
`time` bigint(20) NOT NULL default '0',
`date` date NOT NULL,
`ip` bigint(20) NOT NULL default '0',
`agent` text NOT NULL,
`referer` text NOT NULL,
`status` int(10) NOT NULL default '0',
PRIMARY KEY (`id`)
) ENGINE=MyISAM" . $oiopub_db->default_charset() . ";";

//add archive table
$sql3 = "CREATE TABLE IF NOT EXISTS " . $oiopub_set->dbtable_tracker_archive . " (
`id` int(10) NOT NULL auto_increment,
`pid` int(10) NOT NULL default '0',
`time` bigint(20) NOT NULL default '0',
`date` date NOT NULL,
`unique_clicks` int(10) NOT NULL default '0',
`total_clicks` int(10) NOT NULL default '0',
`unique_visits` int(10) NOT NULL default '0',
`total_visits` int(10) NOT NULL default '0',
PRIMARY KEY (`id`),
KEY `pid` (`pid`),
KEY `date` (`date`)
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