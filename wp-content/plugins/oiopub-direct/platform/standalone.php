<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();


/* INCLUDES */

include_once($oiopub_set->platform_dir . "/functions.php");

/* DEFINITIONS */

define('oiopub_posts', false);
define('oiopub_links', true);
define('oiopub_banners', true);
define('oiopub_inline', false);
define('oiopub_custom', true);

/* EXTRA VARS */

$oiopub_set->site_url = $oiopub_set->host_name;
$oiopub_set->admin_url = $oiopub_set->plugin_url;

/* HOOKS */

if(oiopub_is_admin()) {
	$oiopub_hook->add('init', 'oiopub_log_admin_ip');
	$oiopub_hook->add('init', 'oiopub_upgrade_wrapper');
	$oiopub_hook->add('init', 'oiopub_uninstall_wrapper');
} else {
	$oiopub_hook->add('oiopub_header', 'oiopub_header_output', 99);
}

/* INIT */

//load admin class
if(oiopub_is_admin()) {
	include_once($oiopub_set->folder_dir . "/include/admin.php");
	include_once($oiopub_set->platform_dir . "/admin.php");
	$oiopub_admin = new oiopub_admin_standalone();
}

?>