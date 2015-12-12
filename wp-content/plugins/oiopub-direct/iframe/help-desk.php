<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

//define vars
define('OIOPUB_HELP', 1);
define('OIOPUB_ADMIN', 1);

//init
include_once("../index.php");

//show load screen
echo "<div class='loading' style='text-align:center; padding-top:30px;'>Loading... <img src='" . $oiopub_set->plugin_url_org . "/images/loading.gif' style='border:0px;' alt='' /></div>\n";
flush();

//perform checks
if(oiopub_install_check()) {
	//is plugin enabled?
	if($oiopub_set->enabled != 1) {
		exit();
	}
	//check access
	if(!oiopub_auth_check()) {
		exit();
	}
}

//load helper class
include_once($oiopub_set->folder_dir . "/include/help.php");
$oiopub_help = new oiopub_help;

//hide load screen
echo "<style type='text/css'>.loading{display:none;}</style>\n";
flush();

//$_GET show?
$oiopub_help->show();

//header
$oiopub_help->header();

//core text
echo "If you need help with OIO, please check out the following resources:\n";
echo "<br /><br /><br />\n";
echo "<div style='line-height:32px; font-weight:bold;'>\n";
echo "* <a href='http://docs.oiopublisher.com' target='_blank'>Online tutorials &amp; FAQs</a>\n";
echo "<br />\n";
echo "* <a href='" . $oiopub_set->admin_url . "/admin.php?page=" . $oiopub_help->page . "&help=1&show=output' target='_parent'>Displaying ads - quick checklist</a>\n";
echo "<br />\n";
echo "* <a href='http://forum.oiopublisher.com' target='_blank'>Support form - ask a question</a>\n";
echo "</div>\n";

//footer
$oiopub_help->footer();

?>