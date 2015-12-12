<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

/* ACTIVATION FUNCTIONS */

//deactivate script
function oiopub_script_deactivate() {
	//get vars
	$time = time() - 42000;
	$session_name = session_name();
	//unset session data
	$_SESSION = array();
	//unset session cookie
	if(isset($_COOKIE[$session_name])) {
		setcookie($session_name, '', $time, '/');
	}
	//destroy session
	session_destroy();
	//redirect user
	header("Location: install.php");
	exit();
}

/* AUTH FUNCTIONS */

//auth check
function oiopub_auth_check() {
	global $oiopub_set;
	//start session
	oiopub_session_start();
	//check for session vars
	if(isset($oiopub_set->hash) && isset($_SESSION['oiopub']['id']) && $_SESSION['oiopub']['id'] > 0) {
		if($_SESSION['oiopub']['rand'] === md5(md5($oiopub_set->hash) . session_id())) {
			return true;
		}
	}
	//not found
	return false;
}

//is admin
function oiopub_is_admin() {
	if(defined('OIOPUB_ADMIN')) {
		return true;
	}
	return false;
}

/* MISC FUNCTIONS */

//admin home url
function oiopub_admin_home_url() {
	global $oiopub_set;
	return $oiopub_set->admin_url . "/admin.php";
}

?>