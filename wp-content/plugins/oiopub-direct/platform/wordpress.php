<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();


/* INCLUDES */

include_once($oiopub_set->platform_dir . "/functions.php");
include_once($oiopub_set->platform_dir . "/widgets.php");
include_once($oiopub_set->platform_dir . "/inline.php");
include_once($oiopub_set->platform_dir . "/posts.php");

/* DEFINITIONS */

define('oiopub_posts', true);
define('oiopub_links', true);
define('oiopub_banners', true);
define('oiopub_inline', true);
define('oiopub_custom', true);

/* EXTRA VARS */

$oiopub_set->site_url = get_option('siteurl');
$oiopub_set->admin_url = get_option('siteurl') . "/wp-admin";

//site name check
if(empty($oiopub_set->site_name)) {
	oiopub_update_config('site_name', get_option('blogname'));
}

//admin mail check
if(empty($oiopub_set->admin_mail)) {
	oiopub_update_config('admin_mail', get_option('admin_email'));
}

//version fallback
if(empty($oiopub_set->version)) {
	if(get_option('oiopub_version') > 0) {
		$oiopub_set->version = get_option('oiopub_version');
	}
}

//modify template paths?
if($oiopub_set->template == "wordpress" && defined('TEMPLATEPATH')) {
	$oiopub_set->template_header = TEMPLATEPATH . "/header.php";
	$oiopub_set->template_footer = TEMPLATEPATH . "/footer.php";
}

/* HOOKS */

if(oiopub_is_admin()) {
	//redirect user?
	if(isset($oiopub_set->admin_redirect) && $oiopub_set->admin_redirect) {
		oiopub_delete_config('admin_redirect');
		header("Location: $oiopub_set->admin_redirect");
		exit();
	}
	//set actions
	add_action('init', 'oiopub_log_admin_ip');
	add_action('init', 'oiopub_upgrade_wrapper');
	add_action('init', 'oiopub_uninstall_wrapper');
	add_action('init', 'oiopub_deactivate_wrapper');
	//registration hook
	register_activation_hook($oiopub_set->folder_dir.'/wp.php', 'oiopub_script_activate');
} else {
	add_action('wp_head', 'oiopub_header_output', 99);
	$oiopub_hook->add('oiopub_header', 'oiopub_header_output', 99);
	if($oiopub_set->template == "wordpress") {
		$oiopub_hook->add('content_end', 'oiopub_powered_by');
	}
}

/* INIT */

//load admin
if(oiopub_is_admin()) {
	include_once($oiopub_set->folder_dir . "/include/admin.php");
	include_once($oiopub_set->platform_dir . "/admin.php");
	$oiopub_admin = new oiopub_admin_wp();
}

//load posts
if(class_exists('oiopub_posts')) {
	new oiopub_posts();
}

//load widgets
if(class_exists('oiopub_widgets')) {
	new oiopub_widgets();
}

?>