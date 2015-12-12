<?php

//set error level
error_reporting(E_ERROR);

//debug mode?
define('OIOPUB_DEBUG', false);

//load debug?
if(OIOPUB_DEBUG) {
	include_once("include/debug.php");
	$oiopub_debug = new oiopub_debug();
	$oiopub_debug->init();
}

//set environment
if(function_exists('ini_set')) {
	$mem_limit = (int) @ini_get('memory_limit');
	if($mem_limit > 0 && $mem_limit < 64) {
		@ini_set('memory_limit', '64M');
	}
	@ini_set('magic_quotes_sybase', 0);
}

//define oiopub
if(!defined('oiopub')) {
	define('oiopub', 1);
}

//WP 2.5+ activation
global $oiopub_cache, $oiopub_db, $oiopub_set, $oiopub_hook, $oiopub_cron, $oiopub_alerts, $oiopub_version, $wpdb;

//empty class
class oiopub_std {}

//oiopub version
$oiopub_version = "2.60";

//clear settings
$oiopub_set = new oiopub_std;

//csrf protection?
$oiopub_set->csrf = false;

//demo mode?
$oiopub_set->demo = ($_SERVER['HTTP_HOST'] == "demo.oiopublisher.com");

//test site?
$oiopub_set->test_site = ($_SERVER['HTTP_HOST'] == "test.oiopublisher.com");

//normalise $_SERVER variables
//adapted from Wordpress 2.6
if(empty($_SERVER['REQUEST_URI'])) {
	if(isset($_SERVER['HTTP_X_ORIGINAL_URL'])) {
		$_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_ORIGINAL_URL'];
	} elseif(isset($_SERVER['HTTP_X_REWRITE_URL'])) {
		$_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_REWRITE_URL'];
	} else {
		if(!isset($_SERVER['PATH_INFO']) && isset($_SERVER['ORIG_PATH_INFO'])) {
			$_SERVER['PATH_INFO'] = $_SERVER['ORIG_PATH_INFO'];
		}
		if(isset($_SERVER['PATH_INFO'])) {
			if($_SERVER['PATH_INFO'] == $_SERVER['SCRIPT_NAME']) {
				$_SERVER['REQUEST_URI'] = $_SERVER['PATH_INFO'];
			} else {
				$_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'] . $_SERVER['PATH_INFO'];
			}
		}
		if(isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
			$_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
		}
	}
}

//script folder
$cwd = trim(str_replace('\\', '/', dirname(__FILE__)));
$exp = explode('/', $cwd);

//set directories
$oiopub_set->folder_dir = $cwd;
$oiopub_set->folder_name = $exp[count($exp)-1];
$oiopub_set->parent_dir = dirname($cwd);
$oiopub_set->parent_name = $exp[count($exp)-2];
$oiopub_set->cache_dir = $oiopub_set->folder_dir . "/cache";

//load core libs
include_once($oiopub_set->folder_dir . "/include/functions.php");
include_once($oiopub_set->folder_dir . "/include/hooks.php");
include_once($oiopub_set->folder_dir . "/include/config.php");
include_once($oiopub_set->folder_dir . "/include/cache.php");
include_once($oiopub_set->folder_dir . "/include/settings.php");
include_once($oiopub_set->folder_dir . "/include/cron.php");
include_once($oiopub_set->folder_dir . "/include/api.php");
include_once($oiopub_set->folder_dir . "/include/modules.php");

//load hooks class
$oiopub_hook = new oiopub_hooks();

//init config class
$oiopub_config = new oiopub_config;

//load wordpress?
if(isset($oiopub_set->wp_load) && $oiopub_set->wp_load == 1) {
	if(!defined('WPINC')) {
		$oio_cwd = getcwd();
		chdir(dirname($oiopub_set->wp_config));
		include_once($oiopub_set->wp_config);
		error_reporting(OIOPUB_DEBUG ? E_ALL : E_ERROR);
		chdir($oio_cwd);
		
	}
}

//set extra vars
$oiopub_config->set_extras();

//display errors
$oiopub_config->display_errors();

//set db driver
if($oiopub_set->dbh) {
	$db_driver = is_resource($oiopub_set->dbh) ? 'mysql' : 'mysqli';
} else {
	$db_driver = function_exists('mysqli_connect') ? 'mysqli' : 'mysql';
}

//load database
if(OIOPUB_DEBUG) {
	include_once($oiopub_set->folder_dir . "/include/" . $db_driver . "/database_debug.php");
} else {
	include_once($oiopub_set->folder_dir . "/include/" . $db_driver . "/database.php");
}

//unset vars
unset($oiopub_config, $db_driver);

//load cache class
$oiopub_cache = new oiopub_cache($oiopub_set->cache_args);

//load db class
$oiopub_db = new oiopub_db($oiopub_set->db_args, $oiopub_set->dbh, $oiopub_cache);

//load settings class
$oiopub_set = new oiopub_settings();

//load api class
$oiopub_api = new oiopub_api();

//load cron class?
if(!defined('OIOPUB_LOAD_LITE') || defined('OIOPUB_JS')) {
	$oiopub_cron = new oiopub_cron();
}

//include platform
include_once($oiopub_set->platform_file);

//load modules class
$oiopub_module = new oiopub_modules();

//output functions
oiopub_output_files();

//init hook
$oiopub_hook->fire('init');

//check install?
if(!defined('OIOPUB_LOAD_LITE')) {
	oiopub_install_redirect();
}

//read only?
if($oiopub_set->demo && oiopub_is_admin()) {
	unset($_POST, $_REQUEST);
}