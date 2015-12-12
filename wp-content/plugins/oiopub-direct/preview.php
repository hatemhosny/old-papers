<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

//define vars
define('OIOPUB_PREVIEW', 1);

//init
include_once(str_replace("\\", "/", dirname(__FILE__)) . "/index.php");

//is plugin enabled?
if($oiopub_set->enabled != 1) {
	die("Plugin Currently Offline");
}

//get rand ID
$rand = oiopub_clean($_GET['id']);

//get purchase ID
$purchase = $oiopub_db->GetRow("SELECT * FROM " . $oiopub_set->dbtable_purchases . " WHERE rand_id='$rand'");

//valid preview?
if(!$purchase->post_id) die('Invalid Preview Link');

//show preview
echo oiopub_preview_data($purchase->post_id, $purchase->published_status);

?>