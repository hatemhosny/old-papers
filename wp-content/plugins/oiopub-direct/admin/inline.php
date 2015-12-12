<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

//inline purchase manager
function oiopub_admin_inline_purchase() {
	global $oiopub_set;
	$option_type = oiopub_var('opt', 'get');
	$oiopub_page = oiopub_var('page', 'get');
	$oiopub_type = intval(oiopub_var('type', 'get'));
	echo "<script language=\"javascript\">function alert1(url){var msg=confirm('Confirm Inline Ad Approval?');if(msg){window.location=url;}}</script>\n";
 	echo "<script language=\"javascript\">function alert2(url){var msg=confirm('Confirm Inline Ad Rejection?');if(msg){window.location=url;}}</script>\n";
 	echo "<script language=\"javascript\">function alert3(url){var msg=confirm('Confirm Payment Reminder?');if(msg){window.location=url;}}</script>\n";
 	echo "<script language=\"javascript\">function alert4(url){var msg=confirm('Confirm Payment Validation?');if(msg){window.location=url;}}</script>\n";
	echo "<script language=\"javascript\">function alert5(url){var msg=confirm('Confirm Void Transaction?');if(msg){window.location=url;}}</script>\n";
	echo "<script language=\"javascript\">function alert6(url){var msg=confirm('Confirm Inline Ad Expiry?');if(msg){window.location=url;}}</script>\n";
	echo "<script language=\"javascript\">function alert7(url){var msg=confirm('Confirm Inline Ad Renewal?');if(msg){window.location=url;}}</script>\n";
	echo "<script language=\"javascript\">function alert8(url){var msg=confirm('Confirm Permanent Deletion?');if(msg){window.location=url;}}</script>\n";
	echo "<script language=\"javascript\">function alert9(url){var msg=confirm('Confirm Purchase Promotion?');if(msg){window.location=url;}}</script>\n";
	echo "<br />\n";
	echo "<table width=\"100%\">\n";
	echo "<tr>\n";
	echo "<td><h3>Inline Ads</h3></td>\n";
	echo "<td align=\"right\" style=\"padding-right:15px;\">\n";
	echo "<script type=\"text/javascript\">\n";
	echo "function switch_status(type, id) {\n";
	echo "window.location = 'admin.php?page=oiopub-manager.php&opt='+type+'&type='+id;\n";
	echo "}\n";
	echo "</script>\n";
	echo "<form method=\"get\" action=\"" . oiopub_clean($_SERVER['REQUEST_URI']) . "\">\n";
	echo "<input type=\"hidden\" name=\"page\" value=\"$oiopub_page\" />\n";
	echo "<input type=\"hidden\" name=\"opt\" value=\"$option_type\" />\n";
	echo "View ads: " . oiopub_dropmenu_kv($oiopub_set->arr_status, "type", $oiopub_type, 100, "switch_status('inline', this.value);") . "\n";
	echo "</form>\n";
	echo "</td>\n";
	echo "</tr>\n";
	echo "</table>\n";
	if(empty($oiopub_type) || $oiopub_type == 1) {
		//active
		oiopub_admin_inline_edit(0, -2, 1);
	} elseif($oiopub_type == 2) {
		//pending
		oiopub_admin_inline_edit(0, -2);
	} elseif($oiopub_type == 3) {
		//queued
		oiopub_admin_inline_edit(-1);
	} elseif($oiopub_type == 4) {
		//rejected
		oiopub_admin_inline_edit(2);
	} elseif($oiopub_type == 5) {
		//expired
		oiopub_admin_inline_edit(3);
	} elseif($oiopub_type == 6) {
		//all
		oiopub_admin_inline_edit();
	}
}

//inline manager view
function oiopub_admin_inline_edit() {
	global $oiopub_db, $oiopub_set;
	$itype = oiopub_var('type', 'get');
	echo "<table width='100%' border='0' cellspacing='0' cellpadding='6' class='widefat'>\n";
	echo "<thead>\n";
	echo "<tr>\n";
	echo "<th scope=\"col\">ID</th>\n";
	echo "<th scope=\"col\">Client</th>\n";
	echo "<th scope=\"col\">Inline Ad</th>\n";
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
	$inline_query = $oiopub_db->GetAll("SELECT * FROM " . $oiopub_set->dbtable_purchases . " WHERE item_channel=3" . ($statuses ? " AND item_status IN(" . implode(',', $statuses) . ")" : "") . " ORDER BY $order");
	foreach($inline_query as $inline) {
		$actions = ''; $remind = '';
		$outstanding = false; $active = false;
		if($mystatus == 3) $outstanding = true;
		if($inline->item_status == 1 && $inline->payment_status == 1) $active = true;
		if($outstanding == false || ($outstanding == true && $active == false)) {
			$id = $inline->item_id;
			$cost = $inline->payment_amount;
			if($inline->item_type == 1) {
				$url1 = explode("|", $inline->item_url);
				if($url1[0] == 'youtube') {
					$url = "<a href=\"http://www.youtube.com/watch?v=".$url1[1]."\" target=\"_blank\">View Video</a>";
				}
			}
			if($inline->item_type == 2) {
				$url  = "<a href=\"".$inline->item_url."\" target=\"_blank\">Banner Ad</a>";
				$url .= "<br />";
				$url .= "<a href=\"".$inline->item_page."\" target=\"_blank\">Target Link</a>";
			}
			if($inline->item_type == 3) {
				$url = "<a href=\"".$inline->item_url."\" target=\"_blank\">View RSS Ad</a>";
			}
			if($inline->item_type == 4) {
				$url  = "Post ID #" . $inline->post_id;
				$url .= "<br />";
				$url .= "<a href=\"".$inline->item_url."\" target=\"_blank\">View Intext Link</a>";
			}
			$currency = $inline->payment_currency;
			$name = $inline->adv_name;
			$email = $inline->adv_email;
			$type = $inline->item_type;
			$start_time = $inline->payment_time;
			if(empty($start_time)) {
				$pay_time = 'No payment Yet';
			} elseif($inline->item_model === 'days') {
				if($inline->item_duration > 0) {
					$end_time = $start_time + ($inline->item_duration * 86400);
					if($inline->item_status == -1 || $inline->item_status == -2) {
						$pay_time = "N/A";
					} else {
						$pay_time = "Start: <i>".date('D j F Y', $start_time)."</i><br />End: <i>".date('D j F Y', $end_time)."</i>";
					}
				} else {
					$pay_time = "Permanent Ad";
				}
			} else {
				$pay_time = $inline->item_duration_left . " " . $inline->item_model . " left";
			}
			if($inline->item_status == -1) $status = 'Queued';
			if($inline->item_status == -2) $status = '<font color="red"><b>Pending</b></font>';
			if($inline->item_status == 0) $status = '<font color="red"><b>Pending</b></font>'; 
			if($inline->item_status == 1) $status = 'Approved';
			if($inline->item_status == 2) $status = 'Rejected';
			if($inline->item_status == 3) $status = 'Expired';
			if($inline->payment_status == 0) $paid = 'Not Paid Yet'; 
			if($inline->payment_status == 1 && $inline->item_subscription == 0) $paid = 'Payment Made';
			if($inline->payment_status == 1 && $inline->item_subscription == 1) $paid = 'Paid - Subscribed';
			if($inline->payment_status == 2) {
				$paid = "<a href=\"".$oiopub_set->plugin_url_org."/notes.php?type=paylog&id=".$id."\" onclick=\"window.open('".$oiopub_set->plugin_url_org."/notes.php?type=paylog&id=".$id."','oiopublisher','location=0,status=0,scrollbars=0,width=400,height=400'); return false;\"><font color=\"red\">Invalid Payment</font></a>";
			}
			if($type == 1) $type1 = 'Home Page'; 
			if($type == 2) {
				if($inline->post_id > 0) $type1 = 'Single Post<br /><i>Post ID: ' . $inline->post_id . '</i>';
				if($inline->post_id == 0) $type1 = 'All Posts';
			}
			$my_url1 = $oiopub_set->plugin_url_org."/approvals.php?opt=inline&type=" . $itype . "&status=approve&id=" . $id;
			$my_url2 = $oiopub_set->plugin_url_org."/approvals.php?opt=inline&type=" . $itype . "&status=reject&id=" . $id;
			$my_url3 = $oiopub_set->plugin_url_org."/approvals.php?opt=inline&type=" . $itype . "&status=remind&id=" . $id;
			$my_url4 = $oiopub_set->plugin_url_org."/approvals.php?opt=inline&type=" . $itype . "&status=validate&id=" . $id;
			$my_url5 = $oiopub_set->plugin_url_org."/approvals.php?opt=inline&type=" . $itype . "&status=void&id=" . $id;
			$my_url6 = $oiopub_set->plugin_url_org."/approvals.php?opt=inline&type=" . $itype . "&status=expire&id=" . $id;
			$my_url7 = $oiopub_set->plugin_url_org."/approvals.php?opt=inline&type=" . $itype . "&status=renew&id=" . $id;
			$my_url8 = $oiopub_set->plugin_url_org."/approvals.php?opt=inline&type=" . $itype . "&status=delete&id=" . $id;
			$my_url9 = $oiopub_set->plugin_url_org."/approvals.php?opt=inline&type=" . $itype . "&status=promote&id=" . $id;
			if($inline->item_status == 0 || $inline->item_status == -2) {
				$actions .= "<a href=\"$my_url1\" onclick=\"alert1('".$my_url1."'); return false;\" title=\"Approve Purchase\">Approve</a> ";
				$actions .= "<a href=\"$my_url2\" onclick=\"alert2('".$my_url2."'); return false;\" title=\"Reject Purchase\">Reject</a> ";
			}
			if($inline->item_status == -1 && $mystatus != 3) {
				$actions .= "<a href=\"$my_url9\" onclick=\"alert9('".$my_url9."'); return false;\" title=\"Promote Purchase\">Set as Active</a> ";
			}
			if($inline->item_status == 2) {
				$actions .= "<a href=\"$my_url1\" onclick=\"alert1('".$my_url1."'); return false;\" title=\"Approve Purchase\">Approve</a> ";
				$actions .= "<a href=\"$my_url8\" onclick=\"alert8('".$my_url8."'); return false;\" title=\"Delete Purchase from Database\">Delete</a> ";
			}
			if(($inline->item_status == 1 || $inline->item_status == -1) && $inline->payment_status == 0) {
				$remind = "<br /><a href=\"$my_url3\" onclick=\"alert3('".$my_url3."'); return false;\" title=\"Send payment reminder to advertiser\">Send Reminder?</a>";
				$actions .= "<a href=\"$my_url4\" onclick=\"alert4('".$my_url4."'); return false;\" title=\"Mark purchase as paid manually\">Mark Paid</a> ";
				$actions .= "<a href=\"$my_url5\" onclick=\"alert5('".$my_url5."'); return false;\" title=\"Mark purchase as invalid manually\">Mark Void</a> ";
			}
			if($inline->item_status == 1 && $inline->payment_status == 1) {
				$actions .= "<a href=\"$my_url6\" onclick=\"alert6('".$my_url6."'); return false;\" title=\"Mark purchase as expired manually\">Expire</a> ";
				$actions .= "<a href=\"$my_url5\" onclick=\"alert5('".$my_url5."'); return false;\" title=\"Mark purchase as invalid manually\">Mark Void</a> ";
			}
			if($inline->item_status == 3) {
				$actions .= "<a href=\"$my_url7\" onclick=\"alert7('".$my_url7."'); return false;\">Renew</a> ";
			}
			if($inline->payment_status == 2) {
				$actions .= "<a href=\"$my_url3\" onclick=\"alert3('".$my_url3."'); return false;\" title=\"Request purchaser make payment again\">Request Payment</a> ";
				$actions .= "<a href=\"$my_url4\" onclick=\"alert4('".$my_url4."'); return false;\" title=\"Mark purchase as paid manually\">Mark Valid</a> ";
				$actions .= "<a href=\"$my_url8\" onclick=\"alert8('".$my_url8."'); return false;\" title=\"Delete Purchase from Database\">Delete</a> ";
			}
			if(empty($actions)) {
				$actions = "N/A";
			} else {
				$actions = str_replace("> <", "><br /><", trim($actions));
			}
			echo "<tr id='inline-".$id."' $class>\n";
			echo "<td>".$id." (<a href='".$oiopub_set->plugin_url_org."/stats.php?rand=" . $inline->rand_id . "' target='_blank'>stats</a>)<br /><a href='".$oiopub_set->plugin_url_org."/edit.php?type=inline&id=$id' target='_blank'>Edit</a> / <a href='".$oiopub_set->plugin_url_org."/edit.php?do=copy&type=inline&id=$id'>Copy</a></td>\n";
			echo "<td>".$name."<br /><a href=\"mailto:".$email."\">".$email."</a></td>\n";
			echo "<td>".$url."</td>\n";
			echo "<td>".$pay_time."</td>\n";
			echo "<td>".$cost." ".$currency."<br /><i>".$paid."</i>".$remind. ($inline->coupon ? "<br />Coupon: " . $inline->coupon : "") . "</td>\n";
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

//inline settings
function oiopub_admin_inline_settings() {
	global $oiopub_db, $oiopub_set, $oiopub_module;
	$opt_type = oiopub_var('type', 'get');
	//update inline data
	if(isset($_POST['oiopub_inline_price'])) {
		//pricing options
		$price_array = array();
		$duration_array = array();
		$price = explode("\n", $_POST['oiopub_inline_price']);
		$duration = explode("\n", $_POST['oiopub_inline_duration']);
		for($z=0; $z < count($price); $z++) {
			$price[$z] = number_format(floatval($price[$z]), 2, '.', '');
			if($price[$z] > 0) {
				$price_array[] = $price[$z];
				$duration_array[] = intval($duration[$z]);
			}
		}
		if(empty($price_array)) {
			$price_array = array(0);
			$duration_array = array(0);
		}
		//update inline data
		if(empty($opt_type)) {
			$array = array();
			$array['enabled'] = intval($_POST['oiopub_inline_enabled']);
			$array['selection'] = empty($_POST['oiopub_inline_selection']) ? 2 : intval($_POST['oiopub_inline_selection']);
			$array['model'] = oiopub_clean($_POST['oiopub_inline_model']);
			$array['price'] = $price_array;
			$array['duration'] = $duration_array;
			$array['width'] = intval($_POST['oiopub_inline_width']);
			$array['height'] = intval($_POST['oiopub_inline_height']);
			$array['rotator'] = empty($_POST['oiopub_inline_rotator']) ? 1 : intval($_POST['oiopub_inline_rotator']);
			$array['showposts'] = intval($_POST['oiopub_inline_showposts']);
			$array['queue'] = intval($_POST['oiopub_inline_queue']);
			$array['showfeed'] = intval($_POST['oiopub_inline_showfeed']);
			$array['reuse'] = intval($_POST['oiopub_inline_reuse']);
			$array['position'] = oiopub_clean($_POST['oiopub_inline_position']);
			$array['nofollow'] = intval($_POST['oiopub_inline_nofollow']);
			$array['nfboost'] = intval($_POST['oiopub_inline_nfboost']);
			$array['template'] = oiopub_clean($_POST['oiopub_inline_template']);
			$array['defnum'] = intval($_POST['oiopub_inline_defnum']);
			//inline settings
			oiopub_update_config('inline_ads', $array);
			//inline default ads
			$array = array();
			if($oiopub_set->inline_ads['defnum'] > 0) {
				for($z=1; $z <= $oiopub_set->inline_ads['defnum']; $z++) {
					$array['type'][$z] = intval($_POST['oiopub_inline_default'.$z.'_type']);
					$array['image'][$z] = oiopub_clean($_POST['oiopub_inline_default'.$z.'_image']);
					$array['site'][$z] = oiopub_clean($_POST['oiopub_inline_default'.$z.'_site']);
					$array['html'][$z] = $array['type'][$z] == 1 ? $_POST['oiopub_inline_default'.$z.'_html'] : "";
					$array['geo1'][$z] = oiopub_clean($_POST['oiopub_inline_default'.$z.'_geo1']);
					$array['geo2'][$z] = $_POST['oiopub_inline_default'.$z.'_geo2'];
				}
			}
			//save to db
			oiopub_update_config('inline_defaults', $array);
			//stripslashes now?
			if($oiopub_set->inline_ads['defnum'] > 0 && oiopub_has_magic_quotes()) {
				for($z=1; $z <= $oiopub_set->inline_ads['defnum']; $z++) {
					$oiopub_set->inline_defaults['html'][$z] = stripslashes($array['html'][$z]);
				}
			}
			//done
			unset($array);
		}
		//update intext data
		if($opt_type == "intext") {
			$array = array();
			$array['enabled'] = intval($_POST['oiopub_inline_enabled']);
			$array['model'] = oiopub_clean($_POST['oiopub_inline_model']);
			$array['price'] = $price_array;
			$array['duration'] = $duration_array;
			$array['max'] = intval($_POST['oiopub_inline_max']);
			$array['nofollow'] = intval($_POST['oiopub_inline_nofollow']);
			$array['nfboost'] = intval($_POST['oiopub_inline_nfboost']);
			oiopub_update_config('inline_links', $array);
			unset($array);
		}
	}
	//get itype
	if($oiopub_set->inline_ads['selection'] == 1) { $itype = "Video"; }
	if($oiopub_set->inline_ads['selection'] == 2) { $itype = "Image"; }
	if($oiopub_set->inline_ads['selection'] == 3) { $itype = "RSS Feed"; }
	echo "<table width='100%' style='background:#CCFFCC; border:1px solid #9AFF9A; padding:10px; margin-top:10px; margin-bottom:10px;'>\n";
	echo "<tr><td>\n";	
	echo "<b>Type:</b>&nbsp; ";
	if(empty($opt_type)) { echo "<a href='admin.php?page=oiopub-adzones.php&opt=inline'><font color='red'><b>Inline Ads</b></font></a> | "; } else { echo "<a href='admin.php?page=oiopub-adzones.php&opt=inline'><b>Inline Ads</b></a> | "; }
	if($opt_type == 'intext') { echo "<a href='admin.php?page=oiopub-adzones.php&opt=inline&type=intext'><font color='red'><b>Intext Links</b></font></a>\n"; } else { echo "<a href='admin.php?page=oiopub-adzones.php&opt=inline&type=intext'><b>Intext Links</b></a>\n"; }
	echo "</td></tr>\n";
	echo "</table>\n";
	echo "<form action=\"" . oiopub_clean($_SERVER["REQUEST_URI"]) . "#update\" method=\"post\" name=\"type\" id=\"update\">\n";
	echo "<input type='hidden' name='csrf' value='" . oiopub_csrf_token() . "' />\n";
	echo "<input type=\"hidden\" name=\"notify\" value=\"1\" />\n";
	if(empty($opt_type)) {	
		echo "<h2>Inline Ad Settings</h2>\n";
		echo "You do <b>not</b> need to setup any zones to use inline ads, as they will be automatically inserted into your post content when enabled.\n";
		echo "<br /><br /><br />\n";
		echo "<b>Inline Ads Enabled?</b>&nbsp;&nbsp;<input type=\"checkbox\" name=\"oiopub_inline_enabled\" value=\"1\"" . ($oiopub_set->inline_ads['enabled'] == 1 ? " checked" : "")  . " />";
		if($oiopub_set->inline_ads['enabled'] == 1) {
			echo "&nbsp;&nbsp;&nbsp;[<font color=\"green\"><b>inline ads enabled</b></font>]\n";
		} else {
			echo "&nbsp;&nbsp;&nbsp;[<font color=\"red\"><b>inline ads disabled</b></font>]\n";
		}
		echo "<br /><br /><br />\n";
		echo "<b>Select Ad Type</b>\n";
		echo "<br />\n";
		echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
		echo "<tr><td width=\"240\">\n";
		$array = array( 0 => "-- select --", 1 => "Video Ad", 2 => "Image (banner ad)", 3 => "RSS Feed Ad" );
		echo oiopub_dropmenu_kv($array, "oiopub_inline_selection", $oiopub_set->inline_ads['selection'], 200, "document.type.submit()");
		echo "</td><td>\n";
		echo "<i>select whether to let advertisers buy a video, image, or RSS feed based advertisement within posts</i>\n";
		echo "</td></tr>\n";
		echo "</table>\n";
		echo "<br />\n";
		echo "<b>Charging Model</b>\n";
		echo "<br />\n";
		$array = array( 'days' => "Cost per day", 'clicks' => "Cost per click", 'impressions' => "Cost per impression" );
		echo oiopub_dropmenu_kv($array, "oiopub_inline_model", $oiopub_set->inline_ads['model'], 200, "document.type.submit()");
		echo "&nbsp;&nbsp; <i>choose between 'cost per day' (default), 'cost per click' or 'cost per impression'</i>";
		echo "<br /><br />\n";
		echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
		echo "<tr><td width=\"140\">\n";
		echo "<b>Ad Price</b>\n";
		echo "<br />\n";
		$price_count = count($oiopub_set->inline_ads['price']) + 2;
		echo "<textarea name=\"oiopub_inline_price\" style=\"width:70px; height:" . ($price_count * 26) . "px;\">" . @implode("\n", $oiopub_set->inline_ads['price']) . "</textarea>\n";
		echo "</td><td width=\"140\">\n";
		echo "<b># of " . (isset($oiopub_set->inline_ads['model']) ? $oiopub_set->inline_ads['model'] : "days") . "</b>\n";
		echo "<br />\n";
		echo "<textarea name=\"oiopub_inline_duration\" style=\"width:70px; height:" . ($price_count * 26) . "px;\">" . @implode("\n", $oiopub_set->inline_ads['duration']) . "</textarea>\n";
		echo "</td><td>\n";
		echo "&nbsp;&nbsp; <i>you can set an unlimited number of price / duration combos, each on a new line</i>";
		echo "</td></tr>\n";
		echo "</table>\n";
		echo "<br />\n";
		echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
		echo "<tr><td width=\"140\">\n";
		echo "<b>Ad Width</b>\n";
		echo "<br />\n";
		echo "<input type=\"text\" name=\"oiopub_inline_width\" size=\"5\" value=\"".$oiopub_set->inline_ads['width']."\" /> px\n";
		echo "</td><td width=\"140\">\n";
		echo "<b>Ad Height</b>\n";
		echo "<br />\n";
		echo "<input type=\"text\" name=\"oiopub_inline_height\" size=\"5\" value=\"".$oiopub_set->inline_ads['height']."\" /> px\n";
		echo "</td><td>\n";
		echo "&nbsp;&nbsp; <i>these values dictate the height and width of the inline ad</i>";
		echo "</td></tr>\n";
		echo "</table>\n";
		echo "<div class=\"submit\"><input type=\"submit\" value=\"Update Settings\" /></div>\n";
		echo "<br />\n";
		echo "<h2>Advanced Settings</h2>\n";
		echo "<br />\n";
		echo "<b>Number of Ads to Sell</b>\n";
		echo "<br /><br />\n";
		echo "<input type=\"text\" name=\"oiopub_inline_rotator\" size=\"5\" value=\"".$oiopub_set->inline_ads['rotator']."\" />\n";
		echo "&nbsp;&nbsp; <i>choose how many inline ads to sell (will be rotated)</i>\n";
		echo "<br /><br />\n";
		echo "<b>Ad Queue Length</b>\n";
		echo "<br /><br />\n";
		echo "<input type=\"text\" name=\"oiopub_inline_queue\" size=\"5\" value=\"".$oiopub_set->inline_ads['queue']."\" />\n";
		echo "&nbsp;&nbsp; <i>determines how many people can reserve future purchases, set to zero to disable</i>\n";
		echo "<br /><br />\n";
		echo "<b>Number of Ads to Show on Multi-post pages</b>\n";
		echo "<br /><br />\n";
		echo "<input type=\"text\" name=\"oiopub_inline_showposts\" size=\"5\" value=\"".$oiopub_set->inline_ads['showposts']."\" />\n";
		echo "&nbsp;&nbsp; <i>select the max number of posts to display ads on when there are multiple posts on a page</i>\n";
		echo "<br /><br />\n";
		echo "<b>Ad Positioning in Post</b>\n";
		echo "<br /><br />\n";
		$array = array( "left", "right" );
		echo oiopub_dropmenu_k($array, "oiopub_inline_position", $oiopub_set->inline_ads['position']);
		echo "&nbsp;&nbsp; <i>select whether to display the ad on the left or right hand side of the post</i>\n";
		echo "<br /><br />\n";
		echo "<b>Re-use ads?</b>\n";
		echo "<br /><br />\n";
		echo oiopub_dropmenu_kv($oiopub_set->arr_yesno, "oiopub_inline_reuse", $oiopub_set->inline_ads['reuse']);
		echo "&nbsp;&nbsp; <i>selecting 'yes' will mean that the same ads can be shown in multiple posts on a page</i>\n";
		echo "<br /><br />\n";
		if($oiopub_set->inline_ads['selection'] == 2) {
			echo "<b>Show Ads in RSS Feed?</b>\n";
			echo "<br /><br />\n";
			echo oiopub_dropmenu_kv($oiopub_set->arr_yesno, "oiopub_inline_showfeed", $oiopub_set->inline_ads['showfeed']);
			echo "&nbsp;&nbsp; <i>select whether to display inline ads in your rss feed</i>\n";
			echo "<br /><br />\n";
			echo "<b>Use nofollow attribute?</b>\n";
			echo "<br /><br />\n";
			echo oiopub_dropmenu_kv($oiopub_set->arr_nofollow, "oiopub_inline_nofollow", $oiopub_set->inline_ads['nofollow']);
			echo "&nbsp;&nbsp; <i>specify whether you want to use rel='nofollow' on banner ads</i>\n";
			echo "<br /><br />\n";
			echo "<b>Nofollow Price Boost</b>\n";
			echo "<br /><br />\n";
			echo "<input type=\"text\" name=\"oiopub_inline_nfboost\" size=\"5\" value=\"".$oiopub_set->inline_ads['nfboost']."\" /> %\n";
			echo "&nbsp;&nbsp; <i>if 'user choice' is selected above, you can add a percentage to the price for removal of the nofollow attribute</i>\n";
			echo "<br />\n";
		}
		if($oiopub_set->inline_ads['selection'] == 3) {
			echo "<br />\n";
			echo "<b>RSS Feed Template</b>\n";
			echo "<br /><br />\n";
			$array = array("title-date", "title-only", "title-desc");
			echo oiopub_dropmenu_kv($array, "oiopub_inline_template", $oiopub_set->inline_ads['template']);
			echo "&nbsp;&nbsp;<i>choose how you would like to display the post feed</i>\n";
			echo "<br />\n";
		}
		echo "<div class=\"submit\"><input type=\"submit\" value=\"Update Settings\" /></div>\n";
		echo "<br />\n";
		echo "<span id='defaults'></span>\n";
		echo "<h2>Default Ads</h2>\n";
		echo "&raquo; Below you can define an unlimited number of default ads that will be rotated at random with purchased inline ads.\n";
		echo "<br /><br />\n";
		echo "<b>Number of Default Ads?</b>\n";
		echo "<br /><br />\n";
		echo "<input type=\"text\" name=\"oiopub_inline_defnum\" size=\"5\" value=\"".$oiopub_set->inline_ads['defnum']."\" />\n";
		echo "&nbsp;&nbsp; <i>defines how many default ads you want to put in rotation</i>\n";
		if(is_array($oiopub_set->inline_defaults) && $oiopub_set->inline_ads['defnum'] > 0) {
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
			echo "function typechange(box, id) {\n";
			echo "	if(box.value == 0) {\n";
			echo "		showdiv('oio-image-'+id, 'block');\n";
			echo "		showdiv('oio-html-'+id, 'none');\n";
			echo "	} else if(box.value == 1) {\n";
			echo "		showdiv('oio-html-'+id, 'block');\n";
			echo "		showdiv('oio-image-'+id, 'none');\n";
			echo "	}\n";
			echo "}\n";
			echo "//-->\n";
			echo "</script>\n";
			$array = array( 0 => "Image", 1 => "HTML" );
			echo "<table border='0' cellpadding='4' cellspacing='4'>\n";
			for($z=1; $z <= $oiopub_set->inline_ads['defnum']; $z++) {
				if($oiopub_set->inline_defaults['type'][$z] == 0) {
					$image_style = '';
					$html_style = 'style="display:none;"';
				} else {
					$html_style = '';
					$image_style = 'style="display:none;"';
				}
				echo "<tr><td colspan='2' height='20'></td></tr>\n";
				echo "<tr><td width='125'><b>Ad #$z:</b></td><td>" . oiopub_dropmenu_kv($array, 'oiopub_inline_default'.$z.'_type', $oiopub_set->inline_defaults['type'][$z], 180, "typechange(this, $z)") . "</td></tr>\n";
				echo "<tr><td colspan='2'>\n";
				echo "<table id='oio-image-$z' cellpadding='0' cellspacing='0' $image_style>\n";			
				echo "<tr><td width='129'><b>" . $itype . " URL:</b></td><td><input type=\"text\" name=\"oiopub_inline_default".$z."_image\" size=\"43\" value=\"" . $oiopub_set->inline_defaults['image'][$z] . "\" /> &nbsp;<i>the full path to the " . strtolower($itype) . " to be displayed</i></td></tr>\n";
				if($oiopub_set->inline_ads['selection'] == 2) {
					echo "<tr><td colspan='2' height='4'></td></tr>\n";
					echo "<tr><td width='129'><b>Target Link:</b></td><td><input type=\"text\" name=\"oiopub_inline_default".$z."_site\" size=\"43\" value=\"" . $oiopub_set->inline_defaults['site'][$z] . "\" /> &nbsp;<i>the url to the site that the image will be linked to</i></td></tr>\n";
				}
				echo "</table>\n";
				echo "<table id='oio-html-$z' cellpadding='2' cellspacing='2' $html_style>\n";
				echo "<tr><td width='129' valign='top'><b>HTML Code:</b></td><td valign='top'><textarea name=\"oiopub_inline_default".$z."_html\" cols=\"60\" rows=\"6\">" . $oiopub_set->inline_defaults['html'][$z] . "</textarea></td></tr>\n";
				echo "</table>\n";
				echo "</td></tr>\n";
				if($oiopub_set->demographics['enabled'] == 1) {
					$geo = array();
					$geo['array1'] = array( 1 => "from", 2 => "not from" );
					$geo['array2'] = array_merge(array( "GLOB" => "-- global --", "LAST" => "-- last resort --" ), oiopub_geo_countries());
					$geo['name1'] = "oiopub_inline_default".$z."_geo1";
					$geo['name2'] = "oiopub_inline_default".$z."_geo2";
					$geo['current1'] = $oiopub_set->inline_defaults['geo1'][$z];
					$geo['current2'] = is_array($oiopub_set->inline_defaults['geo2'][$z]) ? $oiopub_set->inline_defaults['geo2'][$z] : array( $oiopub_set->inline_defaults['geo2'][$z] );			
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
	} elseif($opt_type == "intext") {
		echo "<h2>Intext Link Settings</h2>\n";	
		echo "This feature enables you to sell links within your existing post content automatically.";
		echo "<br /><br /><br />\n";
		echo "<b>Intext Links Enabled?</b>&nbsp;&nbsp;<input type=\"checkbox\" name=\"oiopub_inline_enabled\" value=\"1\"" . ($oiopub_set->inline_links['enabled'] == 1 ? " checked" : "")  . " />";
		if($oiopub_set->inline_links['enabled'] == 1) {
			echo "&nbsp;&nbsp;&nbsp;[<font color=\"green\"><b>intext links enabled</b></font>]\n";
		} else {
			echo "&nbsp;&nbsp;&nbsp;[<font color=\"red\"><b>intext links disabled</b></font>]\n";
		}
		echo "<br /><br /><br />\n";
		echo "<b>Charging Model</b>\n";
		echo "<br />\n";
		$array = array( 'days' => "Cost per day", 'clicks' => "Cost per click", 'impressions' => "Cost per impression" );
		echo oiopub_dropmenu_kv($array, "oiopub_inline_model", $oiopub_set->inline_links['model'], 200, "document.type.submit()");
		echo "&nbsp;&nbsp; <i>choose between 'cost per day' (default), 'cost per click' or 'cost per impression'</i>";
		echo "<br /><br />\n";
		echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
		echo "<tr><td width=\"140\">\n";
		echo "<b>Ad Price</b>\n";
		echo "<br />\n";
		$price_count = count($oiopub_set->inline_links['price']) + 2;
		echo "<textarea name=\"oiopub_inline_price\" style=\"width:70px; height:" . ($price_count * 26) . "px;\">" . @implode("\n", $oiopub_set->inline_links['price']) . "</textarea>\n";
		echo "</td><td width=\"140\">\n";
		echo "<b># of " . (isset($oiopub_set->inline_links['model']) ? $oiopub_set->inline_links['model'] : "days") . "</b>\n";
		echo "<br />\n";
		echo "<textarea name=\"oiopub_inline_duration\" style=\"width:70px; height:" . ($price_count * 26) . "px;\">" . @implode("\n", $oiopub_set->inline_links['duration']) . "</textarea>\n";
		echo "</td><td>\n";
		echo "&nbsp;&nbsp; <i>you can set an unlimited number of price / duration combos, each on a new line</i>";
		echo "</td></tr>\n";
		echo "</table>\n";
		echo "<div class=\"submit\"><input type=\"submit\" value=\"Update Settings\" /></div>\n";
		echo "<br />\n";
		echo "<h2>Advanced Settings</h2>\n";
		echo "<br />\n";
		echo "<b>Max Links per Post</b>\n";
		echo "<br /><br />\n";
		echo "<input type='text' name='oiopub_inline_max' value='" . $oiopub_set->inline_links['max'] . "' size='5' />\n";
		echo "&nbsp; <i>the maximum number of links that can be purchased per post</i>\n";
		echo "<br /><br />\n";
		echo "<b>Use Nofollow?</b>\n";
		echo "<br /><br />\n";
		echo oiopub_dropmenu_kv($oiopub_set->arr_nofollow, "oiopub_inline_nofollow", $oiopub_set->inline_links['nofollow']);
		echo "&nbsp; <i>decide whether to use nofollow on links, or to let the purchaser decide</i>\n";
		echo "<br /><br />\n";
		echo "<b>Nofollow Boost</b>\n";
		echo "<br /><br />\n";
		echo "<input type='text' name='oiopub_inline_nfboost' value='" . $oiopub_set->inline_links['nfboost'] . "' size='5' /> %\n";
		echo "&nbsp; <i>if 'user choice' is selected above, you can add a percentage to the price for removal of the nofollow attribute</i>\n";
		echo "<br />\n";
		echo "<div class=\"submit\"><input type=\"submit\" value=\"Update Settings\" /></div>\n";
		echo "</form>\n";
	}
}

?>