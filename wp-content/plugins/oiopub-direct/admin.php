<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

//define vars
define('OIOPUB_ADMIN', 1);
define('OIOPUB_PLATFORM', 'standalone');

//noindex header
header("X-Robots-Tag: noindex, nofollow", true);

//init
include_once(str_replace('\\', '/', dirname(__FILE__)) . "/index.php");

//not installed?
if(!oiopub_install_check()) {
	header("Location: install.php");
	exit();
}

//3rd party installation?
if(!isset($oiopub_set->admin_user)) {
	echo "OIOpublisher has been installed using a 3rd party platform, and therefore the admin area cannot be accessed via this file!";
	die();
}

//admin display
$oiopub_admin->display();

?>