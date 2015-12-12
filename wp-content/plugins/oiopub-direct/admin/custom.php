<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

//custom purchase manager
function oiopub_admin_custom_purchase() {
	global $oiopub_set;
	$option_type = oiopub_var('opt', 'get');
	$oiopub_page = oiopub_var('page', 'get');
	$oiopub_type = intval(oiopub_var('type', 'get'));
	$array = $oiopub_set->arr_status;
	unset($array[6]);
	echo "<script language=\"javascript\">function alert1(url){var msg=confirm('Confirm Service Approval?');if(msg){window.location=url;}}</script>\n";
 	echo "<script language=\"javascript\">function alert2(url){var msg=confirm('Confirm Service Rejection?');if(msg){window.location=url;}}</script>\n";
 	echo "<script language=\"javascript\">function alert3(url){var msg=confirm('Confirm Payment Reminder?');if(msg){window.location=url;}}</script>\n";
 	echo "<script language=\"javascript\">function alert4(url){var msg=confirm('Confirm Payment Validation?');if(msg){window.location=url;}}</script>\n";
	echo "<script language=\"javascript\">function alert5(url){var msg=confirm('Confirm Void Transaction?');if(msg){window.location=url;}}</script>\n";
	echo "<script language=\"javascript\">function alert6(url){var msg=confirm('Confirm Service Expiry?');if(msg){window.location=url;}}</script>\n";
	echo "<script language=\"javascript\">function alert7(url){var msg=confirm('Confirm Service Renewal?');if(msg){window.location=url;}}</script>\n";
	echo "<script language=\"javascript\">function alert8(url){var msg=confirm('Confirm Permanent Deletion?');if(msg){window.location=url;}}</script>\n";
	echo "<script language=\"javascript\">function alert9(url){var msg=confirm('Confirm Purchase Promotion?');if(msg){window.location=url;}}</script>\n";
	echo "<br />\n";
	echo "<table width=\"100%\">\n";
	echo "<tr>\n";
	echo "<td><h3>Custom Purchases</h3></td>\n";
	echo "<td align=\"right\" style=\"padding-right:15px;\">\n";
	echo "<script type=\"text/javascript\">\n";
	echo "function switch_status(type, id) {\n";
	echo "window.location = 'admin.php?page=oiopub-manager.php&opt='+type+'&type='+id;\n";
	echo "}\n";
	echo "</script>\n";
	echo "<form method=\"get\" action=\"" . oiopub_clean($_SERVER['REQUEST_URI']) . "\">\n";
	echo "<input type=\"hidden\" name=\"page\" value=\"$oiopub_page\" />\n";
	echo "<input type=\"hidden\" name=\"opt\" value=\"$option_type\" />\n";
	echo "View ads: " . oiopub_dropmenu_kv($oiopub_set->arr_status, "type", $oiopub_type, 100, "switch_status('custom', this.value);") . "\n";
	echo "</form>\n";
	echo "</td>\n";
	echo "</tr>\n";
	echo "</table>\n";
	if(empty($oiopub_type) || $oiopub_type == 1) {
		//active
		oiopub_admin_custom_edit(0, -2, 1);
	} elseif($oiopub_type == 2) {
		//pending
		oiopub_admin_custom_edit(0, -2);
	} elseif($oiopub_type == 3) {
		//queued
		oiopub_admin_custom_edit(-1);
	} elseif($oiopub_type == 4) {
		//rejected
		oiopub_admin_custom_edit(2);
	} elseif($oiopub_type == 5) {
		//expired
		oiopub_admin_custom_edit(3);
	} elseif($oiopub_type == 6) {
		//all
		oiopub_admin_custom_edit();
	}
}

//custom view / edit
function oiopub_admin_custom_edit() {
	global $oiopub_db, $oiopub_set;
	$itype = oiopub_var('type', 'get');
	echo "<table width='100%' border='0' cellspacing='0' cellpadding='6' class='widefat'>\n";
	echo "<thead>\n";
	echo "<tr>\n";
	echo "<th scope=\"col\">ID</th>\n";
	echo "<th scope=\"col\">Client</th>\n";
	echo "<th scope=\"col\">Type</th>\n";
	echo "<th scope=\"col\">Notes</th>\n";
	echo "<th scope=\"col\">Duration</th>\n";
	echo "<th scope=\"col\">Cost</th>\n";
	echo "<th scope=\"col\">Status</th>\n";
	echo "<th scope=\"col\">Action</th>\n";
	echo "</tr>\n";
	echo "</thead>\n";
	echo "<tbody id=\"the-list\">\n";
	//set vars
	$class = "";
	$bgcolor = "";
	$statuses = func_get_args();
	$_GET['order'] = isset($_GET['order']) ? $_GET['order'] : '';
	//order hack
	if($_GET['order'] == 'id') $order = 'item_id DESC';
	if($_GET['order'] == 'client') $order = 'adv_name';
	if($_GET['order'] == 'email') $order = 'adv_email';
	if($_GET['order'] == 'type') $order = 'item_type';
	if($_GET['order'] == 'duration') $order = 'item_duration';
	if($_GET['order'] == 'cost') $order = 'payment_amount';
	if($_GET['order'] == 'status') $order = 'item_status';
	if(!isset($order)) $order = 'item_id DESC';
	//run query
	$custom_query = $oiopub_db->GetAll("SELECT * FROM " . $oiopub_set->dbtable_purchases . " WHERE item_channel=4" . ($statuses ? " AND item_status IN(" . implode(',', $statuses) . ")" : "") . " ORDER BY $order");
	foreach($custom_query as $custom) {
		$actions = ''; $remind = '';
		$outstanding = false; $active = false;
		if($mystatus == 3) $outstanding = true;
		if($custom->item_status == 1 && $custom->payment_status == 1) $active = true;
		if($outstanding == false || ($outstanding == true && $active == false)) {
			$id = $custom->item_id;
			$cost = $custom->payment_amount;
			$notes = $custom->item_notes;
			$currency = $custom->payment_currency;
			$name = $custom->adv_name;
			$email = $custom->adv_email;
			$type = $custom->item_type;
			if($custom->submit_api == 0) $type .= '<br />Direct Sale';
			if($custom->submit_api == 1) $type .= '<br /><a href="http://www.oiopublisher.com/market.php" target="_blank">OIOpublisher Marketplace</a>';
			if($custom->submit_api == 2) $type .= '<br /><a href="http://jobs.oiopublisher.com" target="_blank">OIOpublisher Jobs</a>';
			$start_time = $custom->payment_time;
			$cn = 'custom_' . $type;
			if(empty($start_time)) {
				$pay_time = 'No payment Yet';
			} else {
				if($custom->item_duration > 0) {
					$end_time = $start_time + ($custom->item_duration * 86400);
					$pay_time = "Start: <i>".date('D j F Y', $start_time)."</i><br />End: <i>".date('D j F Y', $end_time)."</i>";
				} else {
					$pay_time = "Permanent";
				}
			}
			if($custom->item_status == 0) $status = '<font color="red"><b>Pending</b></font>'; 
			if($custom->item_status == 1) $status = 'Approved'; 
			if($custom->item_status == 2) $status = 'Rejected';
			if($custom->item_status == 3) $status = 'Expired';
			if($custom->payment_status == 0) $paid = 'Not Paid Yet';
			if($custom->payment_status == 1 && $custom->item_subscription == 0) $paid = 'Payment Made';
			if($custom->payment_status == 1 && $custom->item_subscription == 1) $paid = 'Paid - Subscribed';
			if($custom->payment_status == 2) {
				$paid = "<a href=\"".$oiopub_set->plugin_url_org."/notes.php?type=paylog&id=".$id."\" onclick=\"window.open('".$oiopub_set->plugin_url_org."/notes.php?type=paylog&id=".$id."','oiopublisher','location=0,status=0,scrollbars=0,width=400,height=400'); return false;\"><font color=\"red\">Invalid Payment</font></a>";
			}
			$type1 = $oiopub_set->{$cn}['title'];
			$my_url1 = $oiopub_set->plugin_url_org."/approvals.php?opt=custom&type=" . $itype . "&status=approve&id=" . $id;
			$my_url2 = $oiopub_set->plugin_url_org."/approvals.php?opt=custom&type=" . $itype . "&status=reject&id=" . $id;
			$my_url3 = $oiopub_set->plugin_url_org."/approvals.php?opt=custom&type=" . $itype . "&status=remind&id=" . $id;
			$my_url4 = $oiopub_set->plugin_url_org."/approvals.php?opt=custom&type=" . $itype . "&status=validate&id=" . $id;
			$my_url5 = $oiopub_set->plugin_url_org."/approvals.php?opt=custom&type=" . $itype . "&status=void&id=" . $id;
			$my_url6 = $oiopub_set->plugin_url_org."/approvals.php?opt=custom&type=" . $itype . "&status=expire&id=" . $id;
			$my_url7 = $oiopub_set->plugin_url_org."/approvals.php?opt=custom&type=" . $itype . "&status=renew&id=" . $id;
			$my_url8 = $oiopub_set->plugin_url_org."/approvals.php?opt=custom&type=" . $itype . "&status=delete&id=" . $id;
			$my_url9 = $oiopub_set->plugin_url_org."/approvals.php?opt=custom&type=" . $itype . "&status=promote&id=" . $id;
			if($custom->item_status == 0) {
				$actions .= "<a href=\"$my_url1\" onclick=\"alert1('".$my_url1."'); return false;\" title=\"Approve Purchase\">Approve</a> ";
				$actions .= "<a href=\"$my_url2\" onclick=\"alert2('".$my_url2."'); return false;\" title=\"Reject Purchase\">Reject</a> ";
			}
			if($custom->item_status == 2) {
				$actions .= "<a href=\"$my_url1\" onclick=\"alert1('".$my_url1."'); return false;\" title=\"Approve Purchase\">Approve</a> ";
				$actions .= "<a href=\"$my_url8\" onclick=\"alert8('".$my_url8."'); return false;\" title=\"Delete Purchase from Database\">Delete</a> ";
			}
			if($custom->item_status == 1 && $custom->payment_status == 0) {
				$remind = "<br /><a href=\"$my_url3\" onclick=\"alert3('".$my_url3."'); return false;\" title=\"Send payment reminder to advertiser\">Send Reminder?</a>";
				$actions .= "<a href=\"$my_url4\" onclick=\"alert4('".$my_url4."'); return false;\" title=\"Mark purchase as paid manually\">Mark Paid</a> ";
				$actions .= "<a href=\"$my_url5\" onclick=\"alert5('".$my_url5."'); return false;\" title=\"Mark purchase as invalid manually\">Mark Void</a> ";
			}
			if($custom->item_status == 1 && $custom->payment_status == 1) {
				$actions .= "<a href=\"$my_url6\" onclick=\"alert6('".$my_url6."'); return false;\" title=\"Mark purchase as expired manually\">Expire</a> ";
				$actions .= "<a href=\"$my_url5\" onclick=\"alert5('".$my_url5."'); return false;\" title=\"Mark purchase as invalid manually\">Mark Void</a> ";
			}
			if($custom->item_status == 3) {
				$actions .= "<a href=\"$my_url7\" onclick=\"alert7('".$my_url7."'); return false;\">Renew</a> ";
			}
			if($custom->payment_status == 2) {
				$actions .= "<a href=\"$my_url3\" onclick=\"alert3('".$my_url3."'); return false;\" title=\"Request purchaser make payment again\">Request Payment</a> ";	
				$actions .= "<a href=\"$my_url4\" onclick=\"alert4('".$my_url4."'); return false;\" title=\"Mark purchase as paid manually\">Mark Valid</a> ";
				$actions .= "<a href=\"$my_url8\" onclick=\"alert8('".$my_url8."'); return false;\" title=\"Delete Purchase from Database\">Delete</a> ";
			}
			if(empty($actions)) {
				$actions = "N/A";
			} else {
				$actions = str_replace("> <", "><br /><", trim($actions));
			}
			echo "<tr id='custom-".$id."' $class>\n";
			echo "<td>".$id."<br /><a href='".$oiopub_set->plugin_url_org."/edit.php?type=custom&id=$id' target='_blank'>Edit</a> / <a href='".$oiopub_set->plugin_url_org."/edit.php?do=copy&type=custom&id=$id'>Copy</a></td>\n";
			echo "<td>".$name."<br /><a href=\"mailto:".$email."\">".$email."</a></td>\n";
			echo "<td>".$type1."</td>\n";
			echo "<td><a href=\"".$oiopub_set->plugin_url_org."/notes.php?type=custom&id=$id\" onclick=\"window.open('".$oiopub_set->plugin_url_org."/notes.php?type=custom&id=$id','oiopublisher','location=0,status=0,scrollbars=0,width=400,height=400'); return false;\">View Notes</a></td>\n";
			echo "<td>".$pay_time."</td>\n";
			echo "<td>".$cost." ".$currency."<br /><i>".$paid."</i>".$remind. ($custom->coupon ? "<br />Coupon: " . $custom->coupon : "") . "</td>\n";
			echo "<td>".$status."</td>\n";
			echo "<td>".$actions."</td>\n";
			echo "</tr>\n";
			if($bgcolor == "") {
				$bgcolor = "class='alternate'";
			} else {
				$bgcolor = "";
			}
		}
	}
	echo "</tbody>\n";
	echo "</table>\n";
}

//custom settings
function oiopub_admin_custom_settings() {
	global $oiopub_set, $oiopub_db;
	//basic vars
	$item = (empty($_GET['item']) ? 1 : $_GET['item']);
	$cn = 'custom_' . $item;
	//update custom num
	if(isset($_POST['custom_num'])) {
		$custom_num = (empty($_POST['custom_num']) ? 1 : intval($_POST['custom_num']));
		include_once($oiopub_set->folder_dir . "/include/install.php");
		$oiopub_install = new oiopub_install;
		if($custom_num > $oiopub_set->custom_num) {
			for($num = ($oiopub_set->custom_num+1); $num <= $custom_num; $num++) {
				$oiopub_install->install_zone('custom', $num);
			}
		} elseif($oiopub_set->custom_num > $custom_num) {
			for($num = ($custom_num+1); $num <= $oiopub_set->custom_num; $num++) {
				$oiopub_install->delete_zone('custom', $num);
			}
			$oiopub_db->query("OPTIMIZE TABLE " . $oiopub_set->dbtable_config);
		}
		oiopub_update_config('custom_num', $custom_num);
		echo "<meta http-equiv='refresh' content='0;URL=admin.php?page=oiopub-adzones.php&opt=custom&item=$custom_num' />\n";
	}
	//update custom data
	if(isset($_POST['oiopub_custom_price'])) {
		$array = array();
		$array['title'] = empty($_POST['oiopub_custom_title']) ? "Item $item" : oiopub_clean($_POST['oiopub_custom_title']);
		$array['price'] = number_format(floatval($_POST['oiopub_custom_price']), 2, '.', '');
		$array['duration'] = intval($_POST['oiopub_custom_duration']);
		$array['max'] = intval($_POST['oiopub_custom_max']);
		$array['info'] = $_POST['oiopub_custom_info'];
		$array['download'] = oiopub_clean($_POST['oiopub_custom_download']);
		oiopub_update_config($cn, $array, 1);
		unset($array);
	}
	echo "<script type=\"text/javascript\">\n";
	echo "function switch_zone(type, id) {\n";
	echo "window.location = 'admin.php?page=oiopub-adzones.php&opt='+type+'&item='+id;\n";
	echo "}\n";
	echo "</script>\n";
	echo "<form action=\"" . oiopub_clean($_SERVER["REQUEST_URI"]) . "\" method=\"post\">\n";
	echo "<input type='hidden' name='csrf' value='" . oiopub_csrf_token() . "' />\n";
	echo "<table width='100%' style='background:#EAEAAE; border:1px solid #DBDB70; padding:10px; margin-top:10px; margin-bottom:10px;'>\n";
	echo "<tr><td width='100' valign='middle'><b>Select Item:</b></td>\n";
	echo "<td valign='middle'>\n";
	$zone_menu = array();
	if($oiopub_set->custom_num > 0) {
		for($z=1; $z <= $oiopub_set->custom_num; $z++) {
			$zz = "custom_" . $z;
			$zone_menu[$z] = $oiopub_set->{$zz}['title'] . " (item $z)";
		}
		echo oiopub_dropmenu_kv($zone_menu, "item_id", $item, 200, 'switch_zone("custom", this.value);', 1);
	} else {
		echo "<i>no items currently defined, please use the input form to your right</i>\n";
	}
	echo "</td><td width='280' valign='middle' align='right'>\n";
	echo "<b>How Many Items?</b> <input type='text' name='custom_num' size='6' value='" . $oiopub_set->custom_num . "' style='background:#FFF;' /> <input type='submit' value='Go' />\n";
	echo "</td></tr>\n";
	echo "</table>\n";
	echo "</form>\n";
	echo "<form action=\"" . oiopub_clean($_SERVER["REQUEST_URI"]) . "#update\" method=\"post\" id=\"update\">\n";
	echo "<input type='hidden' name='csrf' value='" . oiopub_csrf_token() . "' />\n";
	echo "<input type=\"hidden\" name=\"notify\" value=\"1\" />\n";
	echo "<h2>Custom Purchases: Item " . $item . "</h2>\n";
	echo "You can define an unlimited number of custom purchases below. Setting the price of any service to zero will disable it.\n";
	echo "<br /><br />\n";
	echo "<table width=\"100%\">\n";
	echo "<tr><td width=\"130\">\n";
	echo "<b>Price:</b>\n";
	echo "<br />\n";
	echo "<input type=\"text\" name=\"oiopub_custom_price\" size=\"7\" value=\"".$oiopub_set->{$cn}['price']."\" />\n";
	echo "</td><td width=\"130\">\n";
	echo "<b>Duration:</b>\n";
	echo "<br />\n";
	echo "<input type=\"text\" name=\"oiopub_custom_duration\" size=\"7\" value=\"".$oiopub_set->{$cn}['duration']."\" />\n";
	echo "</td><td>\n";
	echo "<b>Max Available:</b>\n";
	echo "<br />\n";
	echo "<input type=\"text\" name=\"oiopub_custom_max\" size=\"7\" value=\"".$oiopub_set->{$cn}['max']."\" />\n";
	echo "</td></tr>\n";
	echo "<tr><td colspan=\"3\" style=\"padding-top:20px;\">\n";
	echo "<b>Title:</b>\n";
	echo "<br />\n";
	echo "<input type=\"text\" name=\"oiopub_custom_title\" size=\"40\" value=\"".$oiopub_set->{$cn}['title']."\" />\n";
	echo "&nbsp;&nbsp;<i>add the name of the product you wish to sell here</i>\n";
	echo "</td></tr>\n";
	echo "<tr><td colspan=\"3\" style=\"padding-top:20px;\">\n";
	echo "<b>Download File?</b> *\n";
	echo "<br />\n";
	echo "<input type=\"text\" name=\"oiopub_custom_download\" size=\"40\" value=\"".$oiopub_set->{$cn}['download']."\" />\n";
	echo "&nbsp;&nbsp;<i>allow sale of download, must be a zip file (upload to 'downloads' plugin directory)</i>\n";
	echo "</td></tr>";
	echo "<tr><td colspan=\"3\" style=\"padding-top:20px;\">\n";
	echo "<b>Description:</b>\n";
	echo "<br />\n";
	echo "<textarea name=\"oiopub_custom_info\" rows=\"4\" cols=\"70\">".stripslashes($oiopub_set->{$cn}['info'])."</textarea>\n";
	echo "</td></tr>\n";
	echo "</table>\n";
	echo "<br />\n";
	echo "<i>* you should just enter the name of the zip file you want to sell, such as <b>myzip.zip</b>. Please then upload the file to the 'downloads' folder in the OIOpublisher Direct plugin. If you don't do this, clients won't be able to download your product!</i>\n";
	echo "<br />\n";
	echo "<div class=\"submit\"><input type=\"submit\" value=\"Update Settings\" /></div>\n";
	echo "</form>\n";
}

?>