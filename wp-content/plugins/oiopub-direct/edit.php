<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

//define vars
define('OIOPUB_EDIT', 1);
define('OIOPUB_ADMIN', 1);

//init
include_once(str_replace("\\", "/", dirname(__FILE__)) . "/index.php");

//don't show errors
@ini_set('display_errors', 0);

//is plugin enabled?
if($oiopub_set->enabled != 1) {
	die("Plugin Currently Offline");
}

//check access
if(!oiopub_auth_check()) {
	die("Access Denied");
}

//get vars
$message = '';
$nofollow = true;
$item = new oiopub_std;
$item_id = intval($_GET['id']);
$item_type = oiopub_clean($_GET['type']);

//copy
if($_GET['do'] == "copy") {
	oiopub_copy_ad($item_id, $_SERVER['HTTP_REFERER']);
}

//disable upload?
if(isset($_GET['upload']) && $_GET['upload']) {
	$oiopub_set->general_set['upload'] = $_GET['upload'] == 'false' ? 0 : 1;
}

//edit?
if($item_id > 0) {
	$item = $oiopub_db->GetRow("SELECT * FROM $oiopub_set->dbtable_purchases WHERE item_id='$item_id'");
	if(empty($item)) {
		die("This Purchase ID does not exist in the database!");
	} elseif($item->submit_api > 0) {
		die("You cannot edit purchases made via OIOpublisher.com");
	} else {
		$pid = $item->post_id;
	}
}

//already uploaded?
if(strpos($item->item_url, "uploads/") !== false) {
	$is_uploaded = true;
	$org_item_url = $item->item_url;
} else {
	$is_uploaded = false;
	$org_item_url = $item->item_url;
}

//get type
if($item_type == "post") {
	$prefix = "p";
	$nofollow = false;
	$item->item_channel = 1;
	$adtype_array = array( 1 => "Blogger", "Advertiser" );
	$title = __oio("Paid Review Purchase");
} elseif($item_type == "link") {
	$prefix = "l";
	$item->item_channel = 2;
	for($z=1; $z <= $oiopub_set->links_zones; $z++) {
		$lz = "links_" . $z;
		if(!empty($oiopub_set->{$lz}['title'])) {
			$adtype_array[$z] = $oiopub_set->{$lz}['title'] . " (zone $z)";
		}
		if(isset($item->item_type) && $item->item_type == $z) {
			$nofollow = $oiopub_set->{$lz}['nofollow'] == 2 ? true : false;
		}
	}
	$title = __oio("Text Ad Purchase");
} elseif($item_type == "inline") {
	$prefix = "v";
	$item->item_channel = 3;
	if($oiopub_set->inline_ads['selection'] == 1) $inline_select = "Video Ad";
	if($oiopub_set->inline_ads['selection'] == 2) $inline_select = "Banner Ad";
	if($oiopub_set->inline_ads['selection'] == 3) $inline_select = "RSS Feed Ad";
	$adtype_array = array( $oiopub_set->inline_ads['selection'] => $inline_select, 4 => "Intext Link" );
	$title = __oio("Inline Ad Purchase");
	//$nofollow = $oiopub_set->inline_ads['nofollow'] == 2 ? true : false;
} elseif($item_type == "custom") {
	$prefix = "s";
	$nofollow = false;
	$item->item_channel = 4;
	for($z=1; $z <= $oiopub_set->custom_num; $z++) {
		$cn = "custom_" . $z;
		if(!empty($oiopub_set->{$cn}['title'])) {
			$adtype_array[$z] = $oiopub_set->{$cn}['title'] . " (item $z)";
		}
	}
	$title = __oio("Custom Purchase");
} elseif($item_type == "banner") {
	$prefix = "b";
	$item->item_channel = 5;
	for($z=1; $z <= $oiopub_set->banners_zones; $z++) {
		$bz = "banners_" . $z;
		if(!empty($oiopub_set->{$bz}['title'])) {
			$adtype_array[$z] = $oiopub_set->{$bz}['title'] . " (zone $z)";
		}
		if(isset($item->item_type) && $item->item_type == $z) {
			$nofollow = $oiopub_set->{$bz}['nofollow'] == 2 ? true : false;
		}
	}
	$title = __oio("Banner Ad Purchase");
}

//post vars
if(isset($_POST['process']) && $_POST['process'] == "yes") {
	//set vars
	$item->adv_name = oiopub_clean($_POST['name']);
	$item->adv_email = strtolower(oiopub_clean($_POST['email']));
	$item->item_status = intval($_POST['adstatus']);
	$item->item_nofollow = intval($_POST['adnofollow']);
	$item->direct_link = intval($_POST['adtracking']);
	$item->payment_processor = oiopub_clean($_POST['paymethod']);
	$item->payment_status = intval($_POST['paystatus']);
	$item->item_subscription = intval($_POST['subscription']);
	$item->payment_txid = oiopub_clean($_POST['txn_id']);
	$item->payment_amount = floatval($_POST['adprice']);
	$item->item_model = oiopub_clean($_POST['admodel']);
	$item->item_duration = intval($_POST['adduration']);
	$item->item_duration_left = intval($_POST['adduration_left']);
	$item->item_url = oiopub_clean($_POST['adurl']);
	$item->item_page = oiopub_clean($_POST['adpage']);
	$item->item_tooltip = oiopub_clean($_POST['adtooltip']);
	$item->item_notes = oiopub_clean($_POST['adnotes'], 0, 1, 0);
	$item->item_subid = oiopub_clean($_POST['subid']);
	$item->category_id = intval($_POST['cats']);
	//set currency?
	if(isset($_POST['adcurrency'])) {
		$item->payment_currency = oiopub_clean($_POST['adcurrency']);
	}
	//post data?
	if($item_type == "post") {
		$item->post_author = intval($_POST['adtype']);
		$item->item_type = 0;
	} else {
		$item->post_author = 0;
		$item->item_type = intval($_POST['adtype']);
	}
	//payment start
	if(isset($_POST['adstart']) && !empty($_POST['adstart'])) {
		$item->payment_time = strtotime($_POST['adstart']);
	} elseif($item->payment_time == 0 && $item->payment_status == 1) {
		$item->payment_time = time();
	} else {
		$item->payment_time = 0;
	}
	//payment next
	if($item->payment_time > 0 && $item->item_duration > 0 && $item->item_subscription == 1 && $item->item_model == 'days') {
		$item->payment_next = $item->payment_time + ($item->item_duration * 86400);
	} else {
		$item->payment_next = 0;
	}
	//post items
	if(isset($_POST['postid'])) {
		$item->post_id = intval($_POST['postid']);
	}
	if(isset($_POST['postphrase'])) {
		$item->post_phrase = oiopub_clean($_POST['postphrase']);
	}
	//inline hack
	if($item->item_channel == 3 && $item->item_type == 4) {
		$item->item_url = oiopub_clean($_POST['adurl2']);
		$item->item_tooltip = oiopub_clean($_POST['adtooltip2']);
	}
	//image upload
	if($oiopub_set->general_set['upload'] == 1 && isset($_FILES['adurl']['name'])) {
		if(!empty($_FILES['adurl']['name'])) {
			include_once($oiopub_set->folder_dir . "/include/upload.php");
			$rand = oiopub_rand(6) . "_";
			$upload = new oiopub_upload();
			$upload->name = $rand . $_FILES['adurl']['name'];
			$upload->size = $_FILES['adurl']['size'];
			$upload->temp_name = $_FILES['adurl']['tmp_name'];
			$upload->upload_dir = $oiopub_set->folder_dir . "/uploads/";
			$upload->is_image = true;
			if($upload->upload()) {
				$item->item_url = $oiopub_set->plugin_url . "/uploads/" . $rand . $_FILES['adurl']['name'];
			} else {
				$message = "<font color='red'><b>File Upload Attempt Failed!</b></font>\n";
			}
		} elseif($is_uploaded) {
			$item->item_url = $org_item_url;
		}
	}
	if(empty($message)) {
		if($item_id > 0) {
			//set data
			$set = array();
			foreach($item as $k => $v) {
				$set[] = "$k='$v'";
			}
			//update
			$oiopub_db->query("UPDATE $oiopub_set->dbtable_purchases SET " . implode(',', $set) . " WHERE item_id='$item_id'");
			$message = "<font color='green'><b>Purchase Data Updated</b></font>\n";
			oiopub_flush_cache();
		} else {
			//post data
			if($item_type == "post") {
				$post = array();
				$post['category'] = intval($_POST['postcat']);
				$post['title'] = oiopub_clean($_POST['posttitle']);
				$post['content'] = oiopub_clean($_POST['postcontent'], 0, 1);
				if(function_exists('oiopub_insert_post')) {
					$item->post_id = oiopub_insert_post($post);
				} else {
					die("The platform you are using OIOpublisher on cannot handle post data!");
				}
			}
			//insert
			$item->submit_time = time();
			$item->rand_id = $prefix . "-" . oiopub_rand(10);
			$item->post_id = intval($item->post_id);
			$oiopub_db->query("INSERT INTO " . $oiopub_set->dbtable_purchases . " (" . implode(',', array_keys((array) $item)) . ") VALUES ('" . implode("','", array_values((array) $item)) . "')");
			$item_id = intval($oiopub_db->insert_id);
			$oiopub_set->request_uri .= '&id=' . $item_id;
			if($item_id > 0) {
				$message = "<font color='green'><b>New Purchase Inserted</b></font>\n";
				oiopub_flush_cache();
			} else {
				$db_error = $oiopub_db->LastError();
				$message = "<font color='red'><b>An error has occurred: " . ($db_error ? $db_error : 'cause unknown') . "</b></font>\n";
			}
		}
	}
	//reset item notes (hack)
	$item->item_notes = stripslashes($item->item_notes);
} elseif(isset($_POST['delete']) && $_POST['delete'] == "yes") {
	//delete
	$item = "";
	$item_id = intval($_GET['id']);
	$oiopub_db->query("DELETE FROM " . $oiopub_set->dbtable_purchases . " WHERE item_id='$item_id'");
	$message = "<font color='green'><b>Item Deleted</b></font>\n";
	oiopub_flush_cache();
}

//other arrays
$adstatus_array = array( 0 => "Pending", 1 => "Approved", 2 => "Rejected", 3 => "Expired", -1 => "Queued", -2 => "Queued (pending)" );
$paystatus_array = array( 0 => "Not Paid", 1 => "Paid", 2 => "Invalid" );

//get categories
if(function_exists('oiopub_category_list')) {
	$cats_array = oiopub_category_list();
}

//template vars
$templates = array();
$templates['page'] = "purchase_edit";
$templates['title'] = $title;

//load template
include_once($oiopub_set->folder_dir . "/templates/core/main.tpl");

?>