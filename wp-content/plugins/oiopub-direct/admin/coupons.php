<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

//coupon codes
function oiopub_admin_coupons() {
	global $oiopub_set;
	//activate coupons
	oiopub_admin_coupons_activate();
	//list of coupons
	oiopub_admin_coupons_list();
	//add / edit coupon?
	if(isset($_GET['cid'])) {
		oiopub_admin_coupons_update();
	}
}

//activate coupons
function oiopub_admin_coupons_activate() {
	global $oiopub_set;
	//update settings?
	if(isset($_POST['process']) && $_POST['process'] == "activate_coupons") {
		$oiopub_set->coupons['enabled'] = $_POST['status'] == 1 ? 1 : 0;
		oiopub_update_config('coupons', $oiopub_set->coupons);
	}
	//build form
	echo '<h2 style="margin:0 0 15px 0;">Activate Coupons?</h2>' . "\n";
	echo '<form action="' . $oiopub_set->request_uri . '" method="post">' . "\n";
	echo '<input type="hidden" name="csrf" value="' . oiopub_csrf_token() . '" />' . "\n";
	echo '<input type="hidden" name="process" value="activate_coupons" />' . "\n";
	echo '<p>' . oiopub_dropmenu_kv(array( 0 => "Disabled", 1 => "Enabled" ), 'status', $oiopub_set->coupons['enabled'], 200) . ' &nbsp; <input type="submit" value="Update" /> &nbsp; ' . ($oiopub_set->coupons['enabled'] == 0 ? '<span style="color:red;"><i>coupon codes will not work until you enable them</i></span>' : '<span style="color:green;"><i>coupon codes are currently activated</i></span>') . '</p>' . "\n";
	echo '</form>' . "\n";
}

//list of coupons
function oiopub_admin_coupons_list() {
	global $oiopub_set, $oiopub_db;
	//set vars
	$page = oiopub_var('page', 'get');
	$opt = oiopub_var('opt', 'get');
	$type = oiopub_var('type', 'get');
	//get coupons
	$status = $type == 1 ? "0" : ($type == 2 ? "0,1" : "1");
	$coupons = $oiopub_db->GetAll("SELECT * FROM " . $oiopub_set->dbtable_coupons . " WHERE status IN(" . $status . ") ORDER BY status DESC, id ASC");
	//build table
	echo '<h2 style="margin:25px 0 15px 0;">My Coupon Codes</h2>' . "\n";
	echo '<table width="100%" style="margin-bottom:5px;">' . "\n";
	echo '<tr>' . "\n";
	echo '<td>&raquo; <a href="admin.php?page=oiopub-opts.php&amp;opt=coupons&amp;cid=0#update"><b>Add new coupon code</b></a></td>' . "\n";
	echo '<td align="right">' . "\n";
	echo '<form method="get" action="' . $oiopub_set->request_uri . '">' . "\n";
	echo '<input type="hidden" name="page" value="' . $page . '" />' . "\n";
	echo '<input type="hidden" name="opt" value="' . $opt . '" />' . "\n";
	echo 'View: ' . oiopub_dropmenu_kv(array( 0 => "Enabled", 1 => "Disabled", 2 => "All" ), "type", $type) . ' <input type="submit" value="Show" />' . "\n";
	echo '</form>' . "\n";
	echo '</td>' . "\n";
	echo '</tr>' . "\n";
	echo '</table>' . "\n";
	echo '<table width="100%" border="0" cellspacing="0" cellpadding="6" class="widefat">' . "\n";
	echo '<thead>' . "\n";
	echo '<tr><th scope="col">Coupon</th><th scope="col">Discount</th><th scope="col">Applies To</th><th scope="col">Expiry Date</th><th scope="col">Max Usage</th><th scope="col">Actions</th></tr>' . "\n";
	echo '</thead>' . "\n";
	echo '<tbody>' . "\n";
	foreach($coupons as $c) {
		$style = ' style="background:' . ($c->status == 1 ? "#BDFCC9" : "#FFC1C1") . ';"';
		$types = array( 0 => 'everything', 2 => 'links', 5 => 'banners', 3 => 'inline', 1 => 'posts', 4 => 'custom ');
		echo '<tr' . $style . '><td>' . $c->code . '</td><td>' . $c->discount . ($c->percentage == 1 ? "%" : " " . $oiopub_set->general_set['currency']) . '</td><td>' . $types[$c->type] . ($c->type_sub ? " (" . $c->type_sub . ")" : "") . '</td><td>' . ($c->expiry_date ? date("jS M, Y", $c->expiry_date) : "not set") . '</td><td>' . ($c->max_usage > 0 ? $c->max_usage : "unlimited") . ' (' . $c->times_used . ')</td><td><a href="admin.php?page=oiopub-opts.php&amp;opt=coupons&amp;cid=' . $c->id . '#update"><b>Edit</b></a></td></tr>' . "\n";
	}
	echo '<tbody>' . "\n";
	echo '</table>' . "\n";
}

//add / edit coupon
function oiopub_admin_coupons_update() {
	global $oiopub_set, $oiopub_db;
	//set vars
	$errors = array();
	$coupon = array();
	$id = (int) oiopub_var('cid', 'get');
	//get data?
	if($id > 0) {
		$coupon = (array) $oiopub_db->GetRow("SELECT * FROM " . $oiopub_set->dbtable_coupons . " WHERE id='" . $id . "' LIMIT 1");
	}
	//update coupon?
	if(isset($_POST['process']) && $_POST['process'] == "update_coupon") {
		//sanitize data
		$_POST = array_map('oiopub_clean', $_POST);
		$_POST['discount'] = number_format(preg_replace('/[^0-9.]/', '', $_POST['discount']), 2, ".", "");
		//set expiry time
		$expiry_time = $_POST['expiry_date'] ? (strtotime($_POST['expiry_date']) + 86300) : 0;
		//set sub types?
		if($_POST['type'] <= 0) {
			$_POST['type_sub'] = "";
		} else {
			$_POST['type_sub'] = explode(",", $_POST['type_sub']);
			$_POST['type_sub'] = array_map('trim', $_POST['type_sub']);
			sort($_POST['type_sub']);
			$_POST['type_sub'] = implode(",", $_POST['type_sub']);
		}
		//valid coupon?
		if(empty($_POST['code']) || $_POST['discount'] <= "0.00") {
			$errors[] = "Please enter a valid coupon code and discount amount";
		}
		//active coupon?
		if($_POST['status'] == 1) {
			//valid expiry time?
			if($expiry_time > 0 && $expiry_time <= time()) {
				$errors[] = "Please set an expiry date in the future";
			}
			//valid usage?
			if($id > 0 && $_POST['max_usage'] > 0 && $coupon['times_used'] >= $_POST['max_usage']) {
				$errors[] = "This coupon code has already been used " . $coupon['times_used'] . " times, please increase the max usage";
			}
		}
		//already used?
		if($id <= 0 && $oiopub_db->GetOne("SELECT id FROM " . $oiopub_set->dbtable_coupons . " WHERE code='" . $_POST['code'] . "' LIMIT 1")) {
			$errors[] = "You have already created a code called " . $_POST['code'];
		}
		//continue?
		if(empty($errors)) {
			//update db
			if($id > 0) {
				$oiopub_db->query("UPDATE " . $oiopub_set->dbtable_coupons . " SET code='" . $_POST['code'] . "', discount='" . $_POST['discount'] . "', percentage='" . ($_POST['percentage'] ? 1 : 0) . "', expiry_date='" . $expiry_time . "', max_usage='" . (int) $_POST['max_usage'] . "', type='" . $_POST['type'] . "', type_sub='" . $_POST['type_sub'] . "', status='" . ($_POST['status'] ? 1 : 0) . "' WHERE id='" . $id . "' LIMIT 1");
			} else {
				$oiopub_db->query("INSERT INTO " . $oiopub_set->dbtable_coupons . " (code,discount,percentage,expiry_date,max_usage,type,type_sub,status) VALUES ('" . $_POST['code'] . "','" . $_POST['discount'] . "','" . ($_POST['percentage'] ? 1 : 0) . "','" . $expiry_time . "','" . (int) $_POST['max_usage'] . "','" . $_POST['type'] . "','" . $_POST['type_sub'] . "','" . ($_POST['status'] ? 1 : 0) . "')");
				$id = $oiopub_db->insert_id;
			}
			//enable coupons?
			if($_POST['status'] == 1) {
				$oiopub_set->coupons['enabled'] = 1;
				oiopub_update_config('coupons', $oiopub_set->coupons);
			}
			//redirect user
			echo '<meta http-equiv="refresh" content="0;url=admin.php?page=oiopub-opts.php&amp;opt=coupons&amp;cid=' . $id . '" />' . "\n";
			exit();
		}
	}
	//display title
	echo '<h2 id="update" style="margin:30px 0 15px 0;">' . ($id > 0 ? "Update Coupon Code: " . $coupon['code'] : "Add Coupon Code") . '</h2>' . "\n";
	//show errors?
	foreach($errors as $e) {
		$coupon = $_POST;
		echo '<span style="color:red;">&raquo; ' . $e . '</span><br />' . "\n";
	}
	//build form
	echo '<form action="' . $oiopub_set->request_uri . '" method="post">' . "\n";
	echo '<input type="hidden" name="csrf" value="' . oiopub_csrf_token() . '" />' . "\n";
	echo '<input type="hidden" name="process" value="update_coupon" />' . "\n";
	echo '<table width="100%" border="0" cellspacing="6" cellpadding="6">' . "\n";
	echo '<tr><td width="150">Coupon Type:</td><td>' . oiopub_dropmenu_kv(array( 0 => 'everything', 2 => 'links', 5 => 'banners', 3 => 'inline', 1 => 'posts', 4 => 'custom '), 'type', ($id > 0 ? $coupon['type'] : "everything"), 150) . ' &nbsp; <i>select whether the coupon applies to a specific type of ad</i></td></tr>' . "\n";
	echo '<tr><td>Coupon Zones?</td><td><input type="text" name="type_sub" size="20" value="' . ($id > 0 ? $coupon['type_sub'] : "") . '" /> &nbsp; <i>optional - to limit this coupon to specific ad zones, enter the zone IDs here (separated by commas)</i></td></tr>' . "\n";
	echo '<tr><td colspan="2" height="10"></td></tr>' . "\n";
	echo '<tr><td>Coupon Code:</td><td><input type="text" name="code" size="20" value="' . ($id > 0 ? $coupon['code'] : "") . '" /> &nbsp; <i>the code to be given to advertisers</i></td></tr>' . "\n";
	echo '<tr><td>Discount Type:</td><td>' . oiopub_dropmenu_kv(array( 0 => "fixed fee (" . $oiopub_set->general_set['currency'] . ")", 1 => "percentage" ), 'percentage', ($id > 0 ? $coupon['percentage'] : ""), 150) . ' &nbsp; <i>select whether to offer a percentage or fixed fee discount</i></td></tr>' . "\n";
	echo '<tr><td>Discount Value:</td><td><input type="text" name="discount" size="20" value="' . ($id > 0 ? $coupon['discount'] : "") . '" /> &nbsp; <i>the value of the discount to offer to advertisers</i></td></tr>' . "\n";
	echo '<tr><td>Expiry Date:</td><td><input type="text" name="expiry_date" size="20" value="' . ($id > 0 && $coupon['expiry_date'] ? date("Y-m-d", $coupon['expiry_date']) : "") . '" /> &nbsp; <i>optional - format yyyy-mm-dd</i></td></tr>' . "\n";
	echo '<tr><td>Max Usage:</td><td><input type="text" name="max_usage" size="20" value="' . ($id > 0 && $coupon['max_usage'] > 0 ? $coupon['max_usage'] : "") . '" /> &nbsp; <i>optional - leave blank for no max usage</i></td></tr>' . "\n";
	echo '<tr><td>Status:</td><td>' . oiopub_dropmenu_kv(array( 0 => "Disabled", 1 => "Enabled" ), 'status', ($id > 0 ? $coupon['status'] : 1), 150) . ' &nbsp; <i>enable or disable the coupon code</i></td></tr>' . "\n";
	echo '<tr><td></td><td><input type="submit" value="Save Changes" /></td></tr>' . "\n";
	echo '</table>' . "\n";
	echo '</form>' . "\n";
}

?>