<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();


/*
PLUGIN URL
allows you to set the url the script is located at
*/

$oiopub_set->plugin_url = ''; //for example: http://www.mysite.com/path/to/oiopub-direct (no trailing slash!)


/*
DATABASE CONFIG
you must fill in all database connection info if using standalone mode
*/

$oiopub_set->db_args['db_host'] = ''; //database host (usually localhost)
$oiopub_set->db_args['db_user'] = ''; //database username
$oiopub_set->db_args['db_pass'] = ''; //database password
$oiopub_set->db_args['db_name'] = ''; //database name


/*
DATABASE TABLE PREFIX
allows you to add a custom prefix to database tables
*/

$oiopub_set->prefix = ''; //for example: abc1_


/*
CACHE CONFIG
allows you to select a custom caching method
*/

$oiopub_set->cache_args['cache_type'] = 'file'; //choose between file, memcache, xcache, eaccelerator
$oiopub_set->cache_args['cache_dir'] = $oiopub_set->cache_dir; //allows you to set a custom file caching directory
$oiopub_set->cache_args['memcache_host'] = ''; //memcache host IP (if using memcache)
$oiopub_set->cache_args['memcache_port'] = ''; //memcache host port (if using memcache)


/*
PAYMENT SANDBOX
allows you to test payment processes, such as paypal sandbox
*/

$oiopub_set->sandbox = 0; //0=off, 1=on


/*
HARDCODE PLATFORM
allows you to define the platform without relying on auto-check
*/

if(!defined('OIOPUB_PLATFORM')) {
	define('OIOPUB_PLATFORM', ''); //choose between wordpress and standalone
}


/*
WORDPRESS SPECIFIC VARS
allows you to hardcode certain Wordpress-only variables
*/

$oiopub_set->wp_config = ''; //custom path to the Wordpress wp-load.php or wp-config.php file (eg. /home/www/wordpress/wp-config.php)

$oiopub_set->blocked_cats = array(); //any WordPress category IDs placed here will not be displayed to advertisers (separate the IDs using commas)