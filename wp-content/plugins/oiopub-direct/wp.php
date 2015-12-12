<?php

/*
Plugin Name: OIO Ad Manager
Plugin URI: http://download.oiopublisher.com
Description: Control your Ad Space with OIOpublisher.
Version: 2.60
Author: Simon Emery
Author URI: http://www.simonemery.co.uk
*/


//set vars
$oio_curfile = str_replace('\\', '/', __FILE__);
$oio_needle = '/plugins/' . basename($oio_curfile);
$oio_include = dirname($oio_curfile) . '/index.php';

//check install directory
if(strpos($oio_curfile, $oio_needle) !== false) {
	echo 'It looks like you have uploaded OIO directly to the Wordpress "plugins" folder.' . "\n";
	echo '<br />' . "\n";
	echo 'OIO must be uploaded inside its own directory (eg. oiopub-direct) within the "plugins" folder to function correctly.' . "\n";
	die();
}

//define vars
if(!defined('OIOPUB_PLATFORM')) {
	define('OIOPUB_PLATFORM', 'wordpress');
}

//include plugin?
if(strpos($_SERVER['REQUEST_URI'], '/admin-ajax.php') === false || isset($_POST['widget-id'])) {
	include_once($oio_include);
}