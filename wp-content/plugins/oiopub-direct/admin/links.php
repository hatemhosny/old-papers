<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

//links purchase manager
function oiopub_admin_links_purchase() {
	global $oiopub_set;
	$option_type = oiopub_var('opt', 'get');
	$oiopub_page = oiopub_var('page', 'get');
	$oiopub_type = intval(oiopub_var('type', 'get'));
	echo "<script language=\"javascript\">function alert1(url){var msg=confirm('Confirm Link Approval?');if(msg){window.location=url;}}</script>\n";
 	echo "<script language=\"javascript\">function alert2(url){var msg=confirm('Confirm Link Rejection?');if(msg){window.location=url;}}</script>\n";
 	echo "<script language=\"javascript\">function alert3(url){var msg=confirm('Confirm Payment Reminder?');if(msg){window.location=url;}}</script>\n";
 	echo "<script language=\"javascript\">function alert4(url){var msg=confirm('Confirm Payment Validation?');if(msg){window.location=url;}}</script>\n";
	echo "<script language=\"javascript\">function alert5(url){var msg=confirm('Confirm Void Transaction?');if(msg){window.location=url;}}</script>\n";
	echo "<script language=\"javascript\">function alert6(url){var msg=confirm('Confirm Link Expiry?');if(msg){window.location=url;}}</script>\n";
	echo "<script language=\"javascript\">function alert7(url){var msg=confirm('Confirm Link Renewal?');if(msg){window.location=url;}}</script>\n";
	echo "<script language=\"javascript\">function alert8(url){var msg=confirm('Confirm Permanent Deletion?');if(msg){window.location=url;}}</script>\n";
	echo "<script language=\"javascript\">function alert9(url){var msg=confirm('Confirm Purchase Promotion?');if(msg){window.location=url;}}</script>\n";
	echo "<br />\n";
	echo "<table width=\"100%\">\n";
	echo "<tr>\n";
	echo "<td><h3>Text ads</h3></td>\n";
	echo "<td align=\"right\" style=\"padding-right:15px;\">\n";
	echo "<script type=\"text/javascript\">\n";
	echo "function switch_status(type, id) {\n";
	echo "window.location = 'admin.php?page=oiopub-manager.php&opt='+type+'&type='+id;\n";
	echo "}\n";
	echo "</script>\n";
	echo "<form method=\"get\" action=\"" . oiopub_clean($_SERVER['REQUEST_URI']) . "\">\n";
	echo "<input type=\"hidden\" name=\"page\" value=\"$oiopub_page\" />\n";
	echo "<input type=\"hidden\" name=\"opt\" value=\"$option_type\" />\n";
	echo "View ads: " . oiopub_dropmenu_kv($oiopub_set->arr_status, "type", $oiopub_type, 100, "switch_status('link', this.value);") . "\n";
	echo "</form>\n";
	echo "</td>\n";
	echo "</tr>\n";
	echo "</table>\n";
	if(empty($oiopub_type) || $oiopub_type == 1) {
		//active
		oiopub_admin_links_edit(0, -2, 1);
	} elseif($oiopub_type == 2) {
		//pending
		oiopub_admin_links_edit(0, -2);
	} elseif($oiopub_type == 3) {
		//queued
		oiopub_admin_links_edit(-1);
	} elseif($oiopub_type == 4) {
		//rejected
		oiopub_admin_links_edit(2);
	} elseif($oiopub_type == 5) {
		//expired
		oiopub_admin_links_edit(3);
	} elseif($oiopub_type == 6) {
		//all
		oiopub_admin_links_edit();
	}
}

//links view / edit
function oiopub_admin_links_edit() {
	global $oiopub_db, $oiopub_set;
	$itype = intval(oiopub_var('type', 'get'));
	echo "<table width='100%' border='0' cellspacing='0' cellpadding='6' class='widefat'>\n";
	echo "<thead>\n";
	echo "<tr>\n";
	echo "<th scope=\"col\">ID</th>\n";
	echo "<th scope=\"col\">Client</th>\n";
	echo "<th scope=\"col\">Type</th>\n";
	echo "<th scope=\"col\">Link</th>\n";
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
	$links = $oiopub_db->GetAll("SELECT * FROM " . $oiopub_set->dbtable_purchases . " WHERE item_channel=2" . ($statuses ? " AND item_status IN(" . implode(',', $statuses) . ")" : "") . " ORDER BY $order");
	foreach($links as $link) {
		$lz = "links_" . $link->item_type;
		$actions = ''; $remind = '';
		$outstanding = false; $active = false;
		if($mystatus == 3) $outstanding = true;
		if($link->item_status == 1 && $link->payment_status == 1) $active = true;
		if($outstanding == false || ($outstanding == true && $active == false)) {
			$id = $link->item_id;
			$cost = ($link->payment_amount == "0.00" && !empty($link->link_exchange)) ? "Link Exchange" : $link->payment_amount;
			$url  = $link->item_page;
			$url .= "<br />";
			$url .= "<a href=\"".$link->item_url."\" target=\"_blank\">" . $link->item_url . "</a>";
			$type = "<a href='admin.php?page=oiopub-adzones.php&opt=link&zone=" . $link->item_type . "' target='_blank'>" . $oiopub_set->{$lz}['title'] . "</a>";
			if($link->item_duration > 0) {
				$type .= "<br /><i>" . number_format($link->item_duration, 0) . " " . $link->item_model . "</i>";
			}
			//if($link->submit_api == 0) $type .= '<br />Direct Sale';
			//if($link->submit_api == 1) $type .= '<br /><a href="http://www.oiopublisher.com/market.php" target="_blank">OIOpublisher Marketplace</a>';
			//if($link->submit_api == 2) $type .= '<br /><a href="http://jobs.oiopublisher.com" target="_blank">OIOpublisher Jobs</a>';
			$currency = $link->payment_currency;
			$name = $link->adv_name;
			$email = $link->adv_email;
			$start_time = $link->payment_time;
			if(empty($start_time)) {
				$pay_time = 'No payment Yet';
			} elseif($link->item_model === 'days') {
				if($link->item_duration > 0) {
					$end_time = $start_time + ($link->item_duration * 86400);
					if($link->item_status == -1 || $link->item_status == -2) {
						$pay_time = "N/A";
					} else {
						$pay_time = "Start: <i>".date('D j F Y', $start_time)."</i><br />End: <i>".date('D j F Y', $end_time)."</i>";
					}
				} else {
					$pay_time = "Permanent Ad";
				}
			} else {
				$pay_time = $link->item_duration_left . " " . $link->item_model . " left";
			}
			if($link->item_status == -1) $status = 'Queued';
			if($link->item_status == -2) $status = '<font color="red"><b>Pending</b></font>';
			if($link->item_status == 0) $status = '<font color="red"><b>Pending</b></font>'; 
			if($link->item_status == 1) $status = 'Approved'; 
			if($link->item_status == 2) $status = 'Rejected';
			if($link->item_status == 3) $status = 'Expired';
			if($link->payment_status == 0) $paid = 'Not Paid Yet'; 
			if($link->payment_status == 1 && $link->item_subscription == 0) $paid = 'Payment Made';
			if($link->payment_status == 1 && $link->item_subscription == 1) $paid = 'Paid - Subscribed';
			if($link->payment_status == 2) {
				$paid = "<a href=\"".$oiopub_set->plugin_url_org."/notes.php?type=paylog&id=".$id."\" onclick=\"window.open('".$oiopub_set->plugin_url_org."/notes.php?type=paylog&id=".$id."','oiopublisher','location=0,status=0,scrollbars=0,width=400,height=400'); return false;\"><font color=\"red\">Invalid Payment</font></a>";
			}
			$my_url1 = $oiopub_set->plugin_url_org."/approvals.php?opt=link&type=" . $itype . "&status=approve&id=" . $id;
			$my_url2 = $oiopub_set->plugin_url_org."/approvals.php?opt=link&type=" . $itype . "&status=reject&id=" . $id;
			$my_url3 = $oiopub_set->plugin_url_org."/approvals.php?opt=link&type=" . $itype . "&status=remind&id=" . $id;
			$my_url4 = $oiopub_set->plugin_url_org."/approvals.php?opt=link&type=" . $itype . "&status=validate&id=" . $id;
			$my_url5 = $oiopub_set->plugin_url_org."/approvals.php?opt=link&type=" . $itype . "&status=void&id=" . $id;
			$my_url6 = $oiopub_set->plugin_url_org."/approvals.php?opt=link&type=" . $itype . "&status=expire&id=" . $id;
			$my_url7 = $oiopub_set->plugin_url_org."/approvals.php?opt=link&type=" . $itype . "&status=renew&id=" . $id;
			$my_url8 = $oiopub_set->plugin_url_org."/approvals.php?opt=link&type=" . $itype . "&status=delete&id=" . $id;
			$my_url9 = $oiopub_set->plugin_url_org."/approvals.php?opt=link&type=" . $itype . "&status=promote&id=" . $id;
			if($link->item_status == 0 || $link->item_status == -2) {
				$actions .= "<a href=\"$my_url1\" onclick=\"alert1('".$my_url1."'); return false;\" title=\"Approve Purchase\">Approve</a> ";
				$actions .= "<a href=\"$my_url2\" onclick=\"alert2('".$my_url2."'); return false;\" title=\"Reject Purchase\">Reject</a> ";
			}
			if($link->item_status == -1 && $mystatus == 3) {
				$actions .= "<a href=\"$my_url9\" onclick=\"alert9('".$my_url9."'); return false;\" title=\"Promote Purchase\">Set as Active</a> ";
			}
			if($link->item_status == 2) {
				$actions .= "<a href=\"$my_url1\" onclick=\"alert1('".$my_url1."'); return false;\" title=\"Approve Purchase\">Approve</a> ";
				$actions .= "<a href=\"$my_url8\" onclick=\"alert8('".$my_url8."'); return false;\" title=\"Delete Purchase from Database\">Delete</a> ";
			}
			if(($link->item_status == 1 || $link->item_status == -1) && $link->payment_status == 0) {
				$remind = "<br /><a href=\"$my_url3\" onclick=\"alert3('".$my_url3."'); return false;\" title=\"Send payment reminder to advertiser\">Send Reminder?</a>";
				$actions .= "<a href=\"$my_url4\" onclick=\"alert4('".$my_url4."'); return false;\" title=\"Mark purchase as paid manually\">Mark Paid</a> ";
				$actions .= "<a href=\"$my_url5\" onclick=\"alert5('".$my_url5."'); return false;\" title=\"Mark purchase as invalid manually\">Mark Void</a> ";
			}
			if($link->item_status == 1 && $link->payment_status == 1) {
				$actions .= "<a href=\"$my_url6\" onclick=\"alert6('".$my_url6."'); return false;\" title=\"Mark purchase as expired manually\">Expire</a> ";
				$actions .= "<a href=\"$my_url5\" onclick=\"alert5('".$my_url5."'); return false;\" title=\"Mark purchase as invalid manually\">Mark Void</a> ";
			}
			if($link->item_status == 3) {
				$actions .= "<a href=\"$my_url7\" onclick=\"alert7('".$my_url7."'); return false;\">Renew</a> ";
			}
			if($link->payment_status == 2) {
				$actions .= "<a href=\"$my_url3\" onclick=\"alert3('".$my_url3."'); return false;\" title=\"Request purchaser make payment again\">Request Payment</a> ";
				$actions .= "<a href=\"$my_url4\" onclick=\"alert4('".$my_url4."'); return false;\" title=\"Mark purchase as paid manually\">Mark Valid</a> ";
				$actions .= "<a href=\"$my_url8\" onclick=\"alert8('".$my_url8."'); return false;\" title=\"Delete Purchase from Database\">Delete</a> ";
			}
			if(empty($actions)) {
				$actions = "N/A";
			} else {
				$actions = str_replace("> <", "><br /><", trim($actions));
			}		
			echo "<tr id='link-".$id."' $class>\n";
			echo "<td>".$id." (<a href='".$oiopub_set->plugin_url_org."/stats.php?rand=" . $link->rand_id . "' target='_blank'>stats</a>)<br /><a href='".$oiopub_set->plugin_url_org."/edit.php?type=link&id=$id' target='_blank'>Edit</a> / <a href='".$oiopub_set->plugin_url_org."/edit.php?do=copy&type=link&id=$id'>Copy</a></td>\n";
			echo "<td>".$name."<br /><a href=\"mailto:".$email."\">".$email."</a></td>\n";
			echo "<td>".$type."</td>\n";
			echo "<td>".$url."</td>\n";
			echo "<td>".$pay_time."</td>\n";
			if($cost == "Link Exchange") {
				echo "<td>Link Exchange<br /><i><a href='" . $link->link_exchange . "' target='_blank'>Exchange Page</a></i>".$remind."</td>\n";
			} else {
				echo "<td>".$cost." ".$currency."<br /><i>".$paid."</i>".$remind. ($link->coupon ? "<br />Coupon: " . $link->coupon : "") . "</td>\n";
			}
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

//links settings
function oiopub_admin_links_settings($page='settings') {
	global $oiopub_set, $oiopub_db, $oiopub_module;
	$zone = (empty($_GET['zone']) ? 1 : $_GET['zone']);
	$lz = 'links_' . $zone;
	$lzd = $lz . '_defaults';
	//zone num update
	if(isset($_POST['zones_num'])) {
		$zones = (empty($_POST['zones_num']) ? 1 : intval($_POST['zones_num']));
		include_once($oiopub_set->folder_dir . "/include/install.php");
		$oiopub_install = new oiopub_install;
		if($zones > $oiopub_set->links_zones) {
			for($num = ($oiopub_set->links_zones+1); $num <= $zones; $num++) {
				$oiopub_install->install_zone('link', $num);
			}
		} elseif($oiopub_set->links_zones > $zones) {
			for($num = ($zones+1); $num <= $oiopub_set->links_zones; $num++) {
				$oiopub_install->delete_zone('link', $num);
			}
			$oiopub_db->query("OPTIMIZE TABLE " . $oiopub_set->dbtable_config);
		}
		oiopub_update_config('links_zones', $zones);
		echo "<meta http-equiv='refresh' content='0;URL=admin.php?page=oiopub-adzones.php&opt=link&zone=$zones' />\n";
	}
	//zone data update
	if(isset($_POST['oiopub_link_price'])) {
		//pricing options
		$dupe_zero = false;
		$price_array = array();
		$duration_array = array();
		$price = explode("\n", $_POST['oiopub_link_price']);
		$duration = explode("\n", $_POST['oiopub_link_duration']);
		for($z=0; $z < count($price); $z++) {
			$the_price = number_format(floatval($price[$z]), 2, '.', '');
			if(!$dupe_zero || $the_price > 0) {
				$price_array[] = $the_price;
				$duration_array[] = intval($duration[$z]);
				if($the_price == 0) {
					$dupe_zero = true;
				}
			}
		}
		if(empty($price_array)) {
			$price_array = array(0);
			$duration_array = array(0);
		} elseif(!empty($_POST['oiopub_link_link_exchange']) && !in_array(0, $price_array)) {
			$price_array[] = 0;
			$duration_array[] = 0;
		}
		$array = array();
		$array['enabled'] = intval($_POST['oiopub_link_enabled']);
		$array['title'] = (!empty($_POST['oiopub_link_title']) ? oiopub_clean($_POST['oiopub_link_title']) : "Zone $zone");
		$array['list'] = intval($_POST['oiopub_link_list']);
		$array['model'] = oiopub_clean($_POST['oiopub_link_model']);
		$array['price'] = $price_array;
		$array['duration'] = $duration_array;
		$array['cols'] = (empty($_POST['oiopub_link_cols']) || $array['list'] == 1) ? 1 : intval($_POST['oiopub_link_cols']);
		$array['rows'] = empty($_POST['oiopub_link_rows']) ? 1 : intval($_POST['oiopub_link_rows']);
		$array['width'] = intval($_POST['oiopub_link_width']);
		$array['height'] = intval($_POST['oiopub_link_height']);
		$array['desc_length'] = intval($_POST['oiopub_link_description']);
		$array['rotator'] = empty($_POST['oiopub_link_rotator']) ? 1 : intval($_POST['oiopub_link_rotator']);
		$array['queue'] = intval($_POST['oiopub_link_queue']);
		$array['nofollow'] = intval($_POST['oiopub_link_nofollow']);
		$array['nfboost'] = intval($_POST['oiopub_link_nfboost']);
		$array['def_text'] = oiopub_clean($_POST['oiopub_link_default']);
		$array['advertise_here'] = intval($_POST['oiopub_link_advhere']);
		$array['def_num'] = intval($_POST['oiopub_link_defnum']);
		$array['def_method'] = intval($_POST['oiopub_link_default_method']);
		$array['link_exchange'] = oiopub_clean($_POST['oiopub_link_link_exchange']);
		$array['cats'] = intval($_POST['oiopub_link_cats']);
		//link zone settings
		oiopub_update_config($lz, $array, 1);
		//link default ads
		$array = array();
		if($oiopub_set->{$lz}['def_num'] > 0) {
			for($z=1; $z <= $oiopub_set->{$lz}['def_num']; $z++) {
				$array['type'][$z] = intval($_POST['oiopub_link_default'.$z.'_type']);
				$array['cats'][$z] = intval($_POST['oiopub_link_default'.$z.'_cats']);
				$array['url'][$z] = oiopub_clean($_POST['oiopub_link_default'.$z.'_url']);
				$array['anchor'][$z] = oiopub_clean($_POST['oiopub_link_default'.$z.'_anchor']);
				$array['desc'][$z] = oiopub_clean($_POST['oiopub_link_default'.$z.'_desc']);
				$array['geo1'][$z] = oiopub_clean($_POST['oiopub_link_default'.$z.'_geo1']);
				$array['geo2'][$z] = $_POST['oiopub_link_default'.$z.'_geo2'];
			}
		}
		oiopub_update_config($lzd, $array);
		unset($array);
	}
	echo "<script type=\"text/javascript\">\n";
	echo "function switch_zone(type, id) {\n";
	echo "window.location = 'admin.php?page=oiopub-adzones.php&opt='+type+'&zone='+id;\n";
	echo "}\n";
	echo "</script>\n";
	echo "<form action=\"" . oiopub_clean($_SERVER["REQUEST_URI"]) . "\" method=\"post\">\n";
	echo "<input type='hidden' name='csrf' value='" . oiopub_csrf_token() . "' />\n";
	echo "<table width='100%' style='background:#EAEAAE; border:1px solid #DBDB70; padding:10px; margin-top:10px; margin-bottom:10px;'>\n";
	echo "<tr><td width='100' valign='middle'><b>Select Zone:</b></td>\n";
	echo "<td valign='middle'>\n";
	$zone_menu = array();
	if($oiopub_set->links_zones > 0) {
		for($z=1; $z <= $oiopub_set->links_zones; $z++) {
			$zz = "links_" . $z;
			$zone_menu[$z] = $oiopub_set->{$zz}['title'] . " (zone $z)";
		}
		echo oiopub_dropmenu_kv($zone_menu, "zone_id", $zone, 200, 'switch_zone("link", this.value);', 1);
	} else {
		echo "<i>no zones currently defined, please use the input form to your right</i>\n";
	}
	echo "</td><td width='280' valign='middle' align='right'>\n";
	echo "<b>How Many Zones?</b> &nbsp;<input type='text' name='zones_num' size='5' value='" . $oiopub_set->links_zones . "' style='background:#FFF;' /> <input type='submit' value='Go' />\n";
	echo "</td></tr>\n";
	echo "</table>\n";
	echo "</form>\n";
	echo "<div width='100%' style='background:#CCFFCC; border:1px solid #9AFF9A; padding:10px; line-height:22px;'>\n";
	echo "<b>PHP Output Code:</b>\n";
	echo "<br />\n";
	echo  htmlspecialchars("<?php if(function_exists('oiopub_link_zone')) oiopub_link_zone(" . $zone . ", 'center'); ?>") . " &nbsp; [<a href='http://forum.oiopublisher.com/discussion/520/customising-the-ad-manager/#Item_5' target='_blank' style='color:red;'>customise output</a>]\n";
	echo "<br /><br />\n";
	echo "<b>Javascript Output Code:</b>\n";
	echo "<br />\n";
	echo htmlspecialchars('<script type="text/javascript" src="' . $oiopub_set->plugin_url . '/js.php#type=link&align=center&zone=' . $zone . '"></script>') . "\n";
	echo "</div>\n";
	echo "<br />\n";
	echo "<form action=\"" . oiopub_clean($_SERVER["REQUEST_URI"]) . "#update\" method=\"post\" name=\"type\" id=\"update\">\n";
	echo "<input type='hidden' name='csrf' value='" . oiopub_csrf_token() . "' />\n";
	echo "<input type=\"hidden\" name=\"notify\" value=\"1\" />\n";
	echo "<h2>Text Ad Settings: Zone " . $zone . "</h2>\n";
	echo "<br />\n";
	echo "<b>Zone Enabled?</b>&nbsp;&nbsp;<input type=\"checkbox\" name=\"oiopub_link_enabled\" value=\"1\"" . ($oiopub_set->{$lz}['enabled'] == 1 ? " checked" : "")  . " />";
	if($oiopub_set->{$lz}['enabled'] == 1) {
		echo "&nbsp;&nbsp;&nbsp;[<font color=\"green\"><b>ad zone enabled</b></font>]\n";
	} else {
		echo "&nbsp;&nbsp;&nbsp;[<font color=\"red\"><b>ad zone disabled</b></font>]\n";
	}
	echo "<br /><br /><br />\n";
	echo "<b>Zone Title</b><br /><input type=\"text\" name=\"oiopub_link_title\" size=\"40\" value=\"".$oiopub_set->{$lz}['title']."\" />";
	echo "&nbsp;&nbsp; <i>defines the name of the zone for advertisers</i>\n";
	echo "<br /><br />\n";
	echo "<b>Show ads in a list?</b>\n";
	echo "<br />\n";
	echo oiopub_dropmenu_kv($oiopub_set->arr_yesno, "oiopub_link_list", $oiopub_set->{$lz}['list'], 100, "document.type.submit()") . "\n";
	echo "&nbsp;&nbsp; <i>selecting 'yes' will show the links in a bullet-point list, otherwise they will be displayed as a zone</i>\n";
	echo "<br /><br />\n";
	echo "<b>Charging Model</b>\n";
	echo "<br />\n";
	$array = array( 'days' => "Cost per day", 'clicks' => "Cost per click", 'impressions' => "Cost per impression" );
	echo oiopub_dropmenu_kv($array, "oiopub_link_model", $oiopub_set->{$lz}['model'], 200, "document.type.submit()");
	if($oiopub_set->{$lz}['model'] == 'clicks') {
		echo "&nbsp;&nbsp; <i><font color='red'><b>Note:</b></font> OIO does not support spreading out clicks over a set timeframe</i>";
	} elseif($oiopub_set->{$lz}['model'] == 'impressions') {
		echo "&nbsp;&nbsp; <i><font color='red'><b>Note:</b></font> OIO does not support spreading out impressions over a set timeframe</i>";
	} else {
		echo "&nbsp;&nbsp; <i>choose between 'cost per day' (default), 'cost per click' or 'cost per impression'</i>";
	}
	echo "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" style=\"margin-top:20px;\">\n";
	echo "<tr><td width=\"140\">\n";
	echo "<b>Ad Price</b>\n";
	echo "<br />\n";
	$price_count = count($oiopub_set->{$lz}['price']) + 2;
	echo "<textarea name=\"oiopub_link_price\" style=\"width:70px; height:" . ($price_count * 26) . "px;\">" . @implode("\n", $oiopub_set->{$lz}['price']) . "</textarea>\n";
	echo "</td><td width=\"140\">\n";
	echo "<b># of " . (isset($oiopub_set->{$lz}['model']) ? $oiopub_set->{$lz}['model'] : "days") . "</b>\n";
	echo "<br />\n";
	echo "<textarea name=\"oiopub_link_duration\" style=\"width:70px; height:" . ($price_count * 26) . "px;\">" . @implode("\n", $oiopub_set->{$lz}['duration']) . "</textarea>\n";
	echo "</td><td>\n";
	echo "&nbsp;&nbsp; <i>you can set an unlimited number of pricing combinations, each on a new line</i>";
	echo "</td></tr>\n";
	echo "</table>\n";
	echo "<br />\n";
	if($oiopub_set->{$lz}['list'] == 1) {
		echo "<b>Number of Links</b>\n";
		echo "<br />\n";
		echo "<input type=\"text\" name=\"oiopub_link_rows\" size=\"5\" value=\"".$oiopub_set->{$lz}['rows']."\" />\n";
		echo "&nbsp;&nbsp; <i>the number of links to display in the list</i>\n";
	} else {
		echo "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\"\n";
		echo "<tr><td width=\"140\">\n";
		echo "<b>Zone Columns</b>\n";
		echo "<br />\n";
		echo "<input type=\"text\" name=\"oiopub_link_cols\" size=\"5\" value=\"".$oiopub_set->{$lz}['cols']."\" />\n";
		echo "</td><td width=\"140\">\n";
		echo "<b>Zone Rows</b>\n";
		echo "<br />\n";
		echo "<input type=\"text\" name=\"oiopub_link_rows\" size=\"5\" value=\"".$oiopub_set->{$lz}['rows']."\" />\n";
		echo "</td><td>\n";
		echo "&nbsp;&nbsp; <i>these values dictate how many link slots will be available, and in what shape (cols x rows)</i>";
		echo "</td></tr>\n";
		echo "</table>\n";
		echo "<br />\n";
		echo "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\">\n";
		echo "<tr><td width=\"140\">\n";
		echo "<b>Zone Width</b>\n";
		echo "<br />\n";
		echo "<input type=\"text\" name=\"oiopub_link_width\" size=\"5\" value=\"".$oiopub_set->{$lz}['width']."\" /> px\n";
		echo "</td><td width=\"140\">\n";
		echo "<b>Zone Height</b>\n";
		echo "<br />\n";
		echo "<input type=\"text\" name=\"oiopub_link_height\" size=\"5\" value=\"".$oiopub_set->{$lz}['height']."\" /> px\n";
		echo "</td><td>\n";
		echo "&nbsp;&nbsp; <i>please define a zone width and height (eg. 468x60)</i>";
		echo "</td></tr>\n";
		echo "</table>\n";    
	}
	echo "<div class=\"submit\"><input type=\"submit\" value=\"Update Settings\" /></div>\n";
	echo "<br />\n";
	echo "<h2>Advanced Settings</h2>\n";
	echo "<br />\n";
	echo "<b>Ad Description Length</b>\n";
	echo "<br /><br />\n";
	echo "<input type=\"text\" name=\"oiopub_link_description\" size=\"5\" value=\"".$oiopub_set->{$lz}['desc_length']."\" />\n";
	echo "&nbsp;&nbsp; <i>the length of the descriptive text below an ad, set to zero to disable ad descriptions</i>\n";
	echo "<br /><br />\n";
	echo "<b>Rotation Factor</b>\n";
	echo "<br /><br />\n";
	echo "<input type=\"text\" name=\"oiopub_link_rotator\" size=\"5\" value=\"".$oiopub_set->{$lz}['rotator']."\" />\n";
	echo "&nbsp;&nbsp; <i>choose how many ads to rotate per slot - a value of 2 will therefore allow 2 purchases per slot</i>\n";
	echo "<br /><br />\n";
	echo "<b>Ad Queue Length</b>\n";
	echo "<br /><br />\n";
	echo "<input type=\"text\" name=\"oiopub_link_queue\" size=\"5\" value=\"".$oiopub_set->{$lz}['queue']."\" />\n";
	echo "&nbsp;&nbsp; <i>determines how many people can reserve future purchases, set to zero to disable</i>\n";
	echo "<br /><br />\n";
	echo "<b>Default Ad Slot Text</b>\n";
	echo "<br /><br />\n";
	echo "<input type=\"text\" name=\"oiopub_link_default\" size=\"30\" value=\"".$oiopub_set->{$lz}['def_text']."\" />\n";
	echo "&nbsp;&nbsp; <i>for link spots that havent been purchased, you can display custom text if wanted</i>\n";
	echo "<br /><br />\n";
	if($oiopub_set->{$lz}['list'] == 1) {
		echo "<b>Show 'Advertise Here' link?</b>\n";
		echo "<br /><br />\n";
		echo oiopub_dropmenu_kv($oiopub_set->arr_yesno, "oiopub_link_advhere", $oiopub_set->{$lz}['advertise_here']);
		echo "&nbsp;&nbsp; <i>setting this value to 'yes' will display a purchase link underneath the current ads displayed</i>\n";
		echo "<br /><br />\n";
	}
	echo "<b>Use nofollow attribute?</b>\n";
	echo "<br /><br />\n";
	echo oiopub_dropmenu_kv($oiopub_set->arr_nofollow, "oiopub_link_nofollow", $oiopub_set->{$lz}['nofollow']);
	echo "&nbsp;&nbsp; <i>specify whether you want to use rel='nofollow' on links</i>\n";
	echo "<br /><br />\n";
	echo "<b>Nofollow Price Boost</b>\n";
	echo "<br /><br />\n";
	echo "<input type=\"text\" name=\"oiopub_link_nfboost\" size=\"5\" value=\"".$oiopub_set->{$lz}['nfboost']."\" /> %\n";
	echo "&nbsp;&nbsp; <i>if 'user choice' is selected above, you can add a percentage to the price for removal of the nofollow attribute</i>\n";
	if(function_exists('oiopub_category_list')) {
		echo "<br /><br />\n";
		echo "<b>Use Wordpress Categories?</b>\n";
		echo "<br /><br />\n";
		echo oiopub_dropmenu_kv($oiopub_set->arr_yesno, "oiopub_link_cats", $oiopub_set->{$lz}['cats']);
		echo "&nbsp;&nbsp; <i>setting this option to 'yes' will let you select a category for each ad<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;when viewing a post or category page in Wordpress, only the ads assigned to that category will display</i>\n";
	}
	echo "<br />\n";
	echo "<div class=\"submit\"><input type=\"submit\" value=\"Update Settings\" /></div>\n";
	echo "<br />\n";
	echo "<h2>Link Exchange</h2>\n";
	echo "&raquo; Entering a link exchange url here will allow other users to get an ad slot for free, providing they link to the url on their site.\n";
	echo "<br /><br />\n";
	echo "<b>Link Exchange URL:</b>\n";
	echo "<br /><br />\n";
	echo "<input type=\"text\" name=\"oiopub_link_link_exchange\" size=\"30\" value=\"".$oiopub_set->{$lz}['link_exchange']."\" />\n";
	echo "&nbsp;&nbsp; <i>entering a url here will enable the link exchange option, which must point to this url</i>\n";
	echo "<br />\n";
	echo "<div class=\"submit\"><input type=\"submit\" value=\"Update Settings\" /></div>\n";
	echo "<br />\n";
	echo "<span id='defaults'></span>\n";
	echo "<h2>Default Ads</h2>\n";
	echo "&raquo; Below you can define an unlimited number of default ads that will be placed in un-purchased slots.\n";
	echo "<br /><br />\n";
	echo "<b>Number of Default Ads</b>\n";
	echo "<br /><br />\n";
	echo "<input type=\"text\" name=\"oiopub_link_defnum\" size=\"5\" value=\"".$oiopub_set->{$lz}['def_num']."\" />\n";
	echo "&nbsp;&nbsp; <i>defines how many default ads you want to put in rotation for this zone</i>\n";
	echo "<br /><br />\n";
	echo "<b>Default Ads Display Method</b>\n";
	echo "<br /><br />\n";
	$array = array( 0 => "Rotate equally with purchases", 2 => "Weighted ad rotation", 1 => "Fill in empty ad spaces only" );
	echo oiopub_dropmenu_kv($array, "oiopub_link_default_method", $oiopub_set->{$lz}['def_method'], 210);
	echo "&nbsp;&nbsp; <i><font color='red'><b>Note:</b></font> 'weighted ad rotation' shows fewer default ads as more purchases are made</i>\n";
	if($oiopub_set->{$lz}['def_num'] > 0) {
		echo "<script type=\"text/javascript\">\n";
		echo "<!--\n";
		echo "function showdiv(id, action) {\n";
		echo "	if(document.getElementById) {\n";
		echo "		if(document.getElementById(id)) {\n";
		echo "			document.getElementById(id).style.display=action;\n";
		echo "		}\n";
		echo "	} else if(document.layers) {\n";
		echo "		document.id.display=action;\n";
		echo "	} else {\n";
		echo "		document.all.id.style.display=action;\n";
		echo "	}\n";
		echo "}\n";
		echo "//-->\n";
		echo "</script>\n";
		$array = array( 0 => "Text" );
		echo "<table border='0' cellpadding='4' cellspacing='4'>\n";
		for($z=1; $z <= $oiopub_set->{$lz}['def_num']; $z++) {
			echo "<tr><td colspan='2' height='20'></td></tr>\n";
			echo "<tr><td width='145'><b>Default Ad [$z]:</b></td><td>" . oiopub_dropmenu_kv($array, 'oiopub_link_default'.$z.'_type', $oiopub_set->{$lzd}['type'][$z], 180) . "</td></tr>\n";
			if(function_exists('oiopub_category_list') && $oiopub_set->{$lz}['cats'] == 1) {
				echo "<tr><td><b>Ad Category:</b></td><td>" . oiopub_dropmenu_kv(oiopub_category_list(), 'oiopub_link_default'.$z.'_cats', $oiopub_set->{$lzd}['cats'][$z], 180) . "</td></tr>\n";
			}				
			echo "<tr><td><b>Target Link:</b></td><td><input type=\"text\" name=\"oiopub_link_default".$z."_url\" size=\"43\" value=\"" . $oiopub_set->{$lzd}['url'][$z] . "\" /> &nbsp;<i>the url to the site that the anchor text will be linked to</i></td></tr>\n";
			echo "<tr><td><b>Anchor Text:</b></td><td><input type=\"text\" name=\"oiopub_link_default".$z."_anchor\" size=\"43\" value=\"" . $oiopub_set->{$lzd}['anchor'][$z] . "\" /> &nbsp;<i>the clickable anchor text you want to display</i></td></tr>\n";
			if($oiopub_set->{$lz}['desc_length'] > 0) {
				echo "<tr><td><b>Ad Description:</b></td><td><input type=\"text\" name=\"oiopub_link_default".$z."_desc\" size=\"43\" value=\"" . $oiopub_set->{$lzd}['desc'][$z] . "\" /> &nbsp;<i>description text to display beneath the ad (optional)</i></td></tr>\n";
			}
			if($oiopub_set->demographics['enabled'] == 1) {
				$geo = array();
				$geo['array1'] = array( 1 => "from", 2 => "not from" );
				$geo['array2'] = array_merge(array( "GLOB" => "-- global --", "LAST" => "-- last resort --" ), oiopub_geo_countries());
				$geo['name1'] = "oiopub_link_default".$z."_geo1";
				$geo['name2'] = "oiopub_link_default".$z."_geo2";
				$geo['current1'] = $oiopub_set->{$lzd}['geo1'][$z];
				$geo['current2'] = is_array($oiopub_set->{$lzd}['geo2'][$z]) ? $oiopub_set->{$lzd}['geo2'][$z] : array( $oiopub_set->{$lzd}['geo2'][$z] );			
				echo "<tr><td><b>GeoTarget:</b></td><td>" . oiopub_dropmenu_kv($geo['array1'], $geo['name1'], $geo['current1'], 80) . " " . oiopub_dropmenu_kv($geo['array2'], $geo['name2'], $geo['current2'], 200, '', 1) . " &nbsp;<i>hold down the Ctrl key to select multiple locations</i></td></tr>\n";
			} else {
				echo "<tr><td><b>GeoTarget:</b></td><td><i>you'll need to switch on <a href='admin.php?page=oiopub-opts.php&opt=geolocation'>geolocation</a>, to use this feature.</i></td></tr>\n";
			}
		}
		echo "</table>\n";
	} else {
		echo "<br /><br />\n"; 
		echo "<p><b><i>No default ads currently defined</i></b></p>\n";
	}
	echo "<div class=\"submit\"><input type=\"submit\" value=\"Update Settings\" /></div>\n";
	echo "</form>\n";
}

?>