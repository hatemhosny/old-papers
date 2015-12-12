<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

//define vars
define('OIOPUB_DOWNLOAD', 1);

//noindex header
header("X-Robots-Tag: noindex, nofollow", true);

//init
include_once(str_replace("\\", "/", dirname(__FILE__)) . "/index.php");

//is plugin enabled?
if($oiopub_set->enabled != 1) {
	die("Plugin Currently Offline");
}

//get vars
$auth = false;
$id = intval($_GET['id']);
$rand = oiopub_clean($_GET['rand']);

//thanks!
if($_GET['do'] == "thanks") {
	echo "<b>" . __oio("Download Complete") . "!</b>\n";
	exit();
}

//get data
$check = $oiopub_db->GetRow("SELECT item_channel,item_type,item_status,payment_status,published_status FROM " . $oiopub_set->dbtable_purchases . " WHERE item_id='$id' AND rand_id='$rand'");

//authenticate
if($check->item_channel == 4) {
	if($check->item_status == 1 && $check->payment_status == 1) {
		if($check->published_status < 2) {
			//gzip off
			if(@ini_get('zlib.output_compression')) {
				@ini_set('zlib.output_compression', 'Off');
			}
			//download
			$cn = "custom_" . $check->item_type;
			oiopub_download_file($oiopub_set->{$cn}['download'], "application/zip", $id, $rand);
			$auth = true;
		} else {
			echo "<b>" . __oio("You have already accessed this download link once, and cannot do so again.") . "</b>\n";
			echo "<br /><br />\n";
			echo __oio("Please contact the seller if you need to download the file again.") . "\n";
			exit();
		}
	}
}

//invalid message
if($auth == false) {
	echo "<b>" . __oio("You don't have permission to download this file. You must have paid for your purchase to do so!") . "</b>\n";
	exit();
}

?>