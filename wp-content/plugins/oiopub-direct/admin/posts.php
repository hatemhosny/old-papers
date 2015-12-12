<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

//banners purchase manager
function oiopub_admin_posts_purchase() {
	global $oiopub_set;
	$option_type = oiopub_var('opt', 'get');
	$oiopub_page = oiopub_var('page', 'get');
	$oiopub_type = intval(oiopub_var('type', 'get'));
	$pub_post = intval(oiopub_var('pub', 'get'));
	$menu = "<select size=\"1\" name=\"type\">";
	$array = $oiopub_set->arr_status;
	unset($array[6]);
	echo "<script language=\"javascript\">function alert1(url){var msg=confirm('Confirm Post Approval?');if(msg){window.location=url;}}</script>\n";
	echo "<script language=\"javascript\">function alert2(url){var msg=confirm('Confirm Post Rejection?');if(msg){window.location=url;}}</script>\n";
	echo "<script language=\"javascript\">function alert3(url){var msg=confirm('Confirm Payment Reminder?');if(msg){window.location=url;}}</script>\n";
	echo "<script language=\"javascript\">function alert4(url){var msg=confirm('Confirm Payment Validation?');if(msg){window.location=url;}}</script>\n";
	echo "<script language=\"javascript\">function alert5(url){var msg=confirm('Confirm Void Transaction?');if(msg){window.location=url;}}</script>\n";
	echo "<script language=\"javascript\">function alert6(url){var msg=confirm('Confirm Post Publication?');if(msg){window.location=url;}}</script>\n";
	echo "<script language=\"javascript\">function alert7(url){var msg=confirm('Confirm Permanent Deletion?');if(msg){window.location=url;}}</script>\n";
	if($pub_post > 0) {
		echo "<br />\n";
		echo "<font color='green'><b>You have attempted to publish post ID " . $pub_post . " before it has been paid for. The post has been approved, but not published!</b></font>\n";
		echo "<br />\n";
	}
	echo "<br />\n";
	echo "<table width=\"100%\">\n";
	echo "<tr>\n";
	echo "<td><h3>Paid Reviews</h3></td>\n";
	echo "<td align=\"right\" style=\"padding-right:15px;\">\n";
	echo "<script type=\"text/javascript\">\n";
	echo "function switch_status(type, id) {\n";
	echo "window.location = 'admin.php?page=oiopub-manager.php&opt='+type+'&type='+id;\n";
	echo "}\n";
	echo "</script>\n";
	echo "<form method=\"get\" action=\"" . oiopub_clean($_SERVER['REQUEST_URI']) . "\">\n";
	echo "<input type=\"hidden\" name=\"page\" value=\"$oiopub_page\" />\n";
	echo "<input type=\"hidden\" name=\"opt\" value=\"$option_type\" />\n";
	echo "View ads: " . oiopub_dropmenu_kv($oiopub_set->arr_status, "type", $oiopub_type, 100, "switch_status('post', this.value);") . "\n";
	echo "</form>\n";
	echo "</td>\n";
	echo "</tr>\n";
	echo "</table>\n";
	if(empty($oiopub_type) || $oiopub_type == 1) {
		//active
		oiopub_admin_posts_edit(0, -2, 1);
	} elseif($oiopub_type == 2) {
		//pending
		oiopub_admin_posts_edit(0, -2);
	} elseif($oiopub_type == 3) {
		//queued
		oiopub_admin_posts_edit(-1);
	} elseif($oiopub_type == 4) {
		//rejected
		oiopub_admin_posts_edit(2);
	} elseif($oiopub_type == 5) {
		//expired
		oiopub_admin_posts_edit(3);
	} elseif($oiopub_type == 6) {
		//all
		oiopub_admin_posts_edit();
	}
}

//post view / edit
function oiopub_admin_posts_edit() {
	global $oiopub_db, $oiopub_set;
	$itype = oiopub_var('type', 'get');
	echo "<table width='100%' border='0' cellspacing='0' cellpadding='6' class='widefat'>\n";
	echo "<thead>\n";
	echo "<tr>\n";
	echo "<th scope=\"col\">ID</th>\n";
	echo "<th scope=\"col\">Client</th>\n";
	echo "<th scope=\"col\">Author</th>\n";
	echo "<th scope=\"col\">Type</th>\n";
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
	if($_GET['order'] == 'author') $order = 'post_author';
	if($_GET['order'] == 'cost') $order = 'payment_amount';
	if($_GET['order'] == 'status') $order = 'item_status';
	if(!isset($order)) $order = 'item_id DESC';
	//run query
	$posts = $oiopub_db->GetAll("SELECT * FROM " . $oiopub_set->dbtable_purchases . " WHERE item_channel=1" . ($statuses ? " AND item_status IN(" . implode(',', $statuses) . ")" : "") . " ORDER BY $order");
	foreach($posts as $post) {
		$actions = ''; $remind = '';
		$outstanding = false; $active = false;
		if($mystatus == 3) $outstanding = true;
		if($post->item_status == 1 && $post->payment_status == 1 && $post->published_status == 1) $active = true;
		if($outstanding == false || ($outstanding == true && $active == false)) {
			$id = $post->item_id;  
			$name = $post->adv_name;
			$email = $post->adv_email; 
			$cost = $post->payment_amount == "0.00" ? "Free Submission" : $post->payment_amount;
			$currency = $post->payment_currency;
			$post_id = $post->post_id;
			if($post->post_author == 1) $author = 'Blogger (you!)'; 
			if($post->post_author == 2) $author = 'Advertiser (them!)';
			if($post->submit_api == 0) $type .= '<br />Direct Sale';
			$type = "Paid Review";
			if($post->submit_api == 1) $type .= '<br /><a href="http://www.oiopublisher.com/market.php" target="_blank">OIOpublisher Marketplace</a>';
			if($post->submit_api == 2) $type .= '<br /><a href="http://jobs.oiopublisher.com" target="_blank">OIOpublisher Jobs</a>';
			if($post->item_status == 0) $status = '<font color="red"><b>Draft</b></font><br /><a href="' . oiopub_post_admin_edit($post_id) . '"><b>Review</b></a>'; 
			if($post->item_status == 1 && $post->published_status == 0) $status = 'Approved<br /><a href="' . oiopub_post_admin_edit($post_id) . '">Edit</a>';
			if($post->item_status == 1 && $post->published_status == 1) $status = 'Published<br /><a href="' . oiopub_post_admin_edit($post_id) . '">Edit</a>';
			if($post->item_status == 2) $status = 'Rejected';
			if($post->payment_status == 0) $paid = 'Not Paid Yet'; 
			if($post->payment_status == 1) $paid = 'Payment Made'; 
			if($post->payment_status == 2) {
				$paid = "<a href=\"".$oiopub_set->plugin_url_org."/notes.php?type=paylog&id=".$id."\" onclick=\"window.open('".$oiopub_set->plugin_url_org."/notes.php?type=paylog&id=".$id."','oiopublisher','location=0,status=0,scrollbars=0,width=400,height=400'); return false;\"><font color=\"red\">Invalid Payment</font></a>";
			}
			$my_url1 = $oiopub_set->plugin_url_org."/approvals.php?opt=post&type=" . $itype . "&status=approve&id=" . $id;
			$my_url2 = $oiopub_set->plugin_url_org."/approvals.php?opt=post&type=" . $itype . "&status=reject&id=" . $id;
			$my_url3 = $oiopub_set->plugin_url_org."/approvals.php?opt=post&type=" . $itype . "&status=remind&id=" . $id;
			$my_url4 = $oiopub_set->plugin_url_org."/approvals.php?opt=post&type=" . $itype . "&status=validate&id=" . $id;
			$my_url5 = $oiopub_set->plugin_url_org."/approvals.php?opt=post&type=" . $itype . "&status=void&id=" . $id;
			$my_url6 = $oiopub_set->plugin_url_org."/approvals.php?opt=post&type=" . $itype . "&status=publish&id=" . $id;
			$my_url7 = $oiopub_set->plugin_url_org."/approvals.php?opt=post&type=" . $itype . "&status=delete&id=" . $id;
			if($post->item_status == 0) {
				$actions .= "<a href=\"$my_url1\" onclick=\"alert1('".$my_url1."'); return false;\" title=\"Approve Purchase\">Approve</a> ";
				$actions .= "<a href=\"$my_url2\" onclick=\"alert2('".$my_url2."'); return false;\" title=\"Reject Purchase\">Reject</a> ";
			}
			if($post->item_status == 2) {
				$actions .= "<a href=\"$my_url1\" onclick=\"alert1('".$my_url1."'); return false;\" title=\"Approve Purchase\">Approve</a> ";
				$actions .= "<a href=\"$my_url7\" onclick=\"alert7('".$my_url7."'); return false;\" title=\"Delete Purchase from Database\">Delete</a> ";
			}
			if($post->item_status == 1 && $post->payment_status == 0) {
				$remind = "<br /><a href=\"$my_url3\" onclick=\"alert3('".$my_url3."'); return false;\" title=\"Send payment reminder to advertiser\">Send Reminder?</a>";
				$actions .= "<a href=\"$my_url4\" onclick=\"alert4('".$my_url4."'); return false;\" title=\"Mark purchase as paid manually\">Mark Paid</a> ";
				$actions .= "<a href=\"$my_url5\" onclick=\"alert5('".$my_url5."'); return false;\" title=\"Mark purchase as invalid manually\">Mark Void</a> ";
			}
			if($post->item_status == 1 && $post->published_status == 0) {
				$actions .= "<a href=\"$my_url6\" onclick=\"alert6('".$my_url6."'); return false;\" title=\"Mark purchase as published manually\">Publish</a> ";
			}
			if($post->item_status == 1 && $post->payment_status == 1 && $post->published_status == 1) {
				$actions .= "<a href=\"$my_url5\" onclick=\"alert5('".$my_url5."'); return false;\" title=\"Mark purchase as invalid manually\">Mark Void</a> ";
			}
			if($post->payment_status == 2) {
				$actions .= "<a href=\"$my_url3\" onclick=\"alert3('".$my_url3."'); return false;\" title=\"Request purchaser make payment again\">Request Payment</a> ";
				$actions .= "<a href=\"$my_url4\" onclick=\"alert4('".$my_url4."'); return false;\" title=\"Mark purchase as paid manually\">Mark Valid</a> ";
				$actions .= "<a href=\"$my_url7\" onclick=\"alert7('".$my_url7."'); return false;\" title=\"Delete Purchase from Database\">Delete</a> ";
			}
			if(empty($actions)) {
				$actions = "N/A";
			} else {
				$actions = str_replace("> <", "><br /><", trim($actions));
			}
			echo "<tr id='post-".$id."' $class>\n";
			echo "<td>".$id."<br /><a href='".$oiopub_set->plugin_url_org."/edit.php?type=post&id=$id' target='_blank'>Edit</a></td>\n";
			echo "<td>".$name."<br /><a href=\"mailto:".$email."\">".$email."</a></td>\n";
			echo "<td>".$author."</td>\n";
			echo "<td>".$type."</td>\n";
			echo "<td>".$cost." ".$currency."<br /><i>".$paid."</i>".$remind. ($post->coupon ? "<br />Coupon: " . $post->coupon : "") . "</td>\n";
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

//posts settings
function oiopub_admin_posts_settings() {
	global $oiopub_set;
	if(isset($_POST['oiopub_postprice_advertiser'])) {
		$oiopub_set->posts['price_adv'] = number_format(floatval($_POST['oiopub_postprice_advertiser']), 2, '.', '');
		$oiopub_set->posts['price_blogger'] = number_format(floatval($_POST['oiopub_postprice_blogger']), 2, '.', '');
		$oiopub_set->posts['price_free'] = intval($_POST['oiopub_postprice_free']);
		$oiopub_set->posts['min_words'] = intval($_POST['oiopub_postwords_min']);
		$oiopub_set->posts['max_posts_num'] = empty($_POST['oiopub_postday_max1']) ? 1 : intval($_POST['oiopub_postday_max1']);
		$oiopub_set->posts['max_posts_days'] = empty($_POST['oiopub_postday_max2']) ? 1 : intval($_POST['oiopub_postday_max2']);
		$oiopub_set->posts['tags'] = intval($_POST['oiopub_tagging']);
		oiopub_update_config('posts', $oiopub_set->posts);
		oiopub_update_config('disclosure', $_POST['oiopub_disclosure']);
	}
	echo "<form action=\"" . oiopub_clean($_SERVER["REQUEST_URI"]) . "#update\" method=\"post\" id=\"update\">\n";
	echo "<input type='hidden' name='csrf' value='" . oiopub_csrf_token() . "' />\n";
	echo "<input type=\"hidden\" name=\"notify\" value=\"1\" />\n";
	echo "<h2>Post Settings</h2>\n";
	echo "This feature enables you to sell posts or paid reviews directly through your website.\n";
	echo "<br /><br /><br />\n";
	echo "<b>Advertiser Post Price</b>\n";
	echo "<br /><br />\n";
	echo "<input type=\"text\" name=\"oiopub_postprice_advertiser\" size=\"7\" value=\"".$oiopub_set->posts['price_adv']."\" />\n";
	echo "&nbsp;&nbsp;<i>price of post if written by the advertiser, set to zero to disable this feature</i>\n";
	echo "<br /><br />\n";
	echo "<b>Blogger Post Price</b>\n";
	echo "<br /><br />\n";
	echo "<input type=\"text\" name=\"oiopub_postprice_blogger\" size=\"7\" value=\"".$oiopub_set->posts['price_blogger']."\" />\n";
	echo "&nbsp;&nbsp;<i>price of post if written by <b>you</b>, set to zero to disable this feature</i>\n";
	echo "<br /><br />\n";
	echo "<b>Free Posts Allowed?</b>\n";
	echo "<br /><br />\n";
	echo oiopub_dropmenu_kv($oiopub_set->arr_yesno, "oiopub_postprice_free", $oiopub_set->posts['price_free']);
	echo "&nbsp;&nbsp;<i>free submissions, only applies if the advertiser post price is set to zero</i>\n";
	echo "<br />\n";
	echo "<div class=\"submit\"><input type=\"submit\" value=\"Update Settings\" /></div>\n";
	echo "<br />\n";
	echo "<h2>Advanced Settings</h2>\n";
	echo "<br />\n";
	echo "<b>Min Words per Post</b>\n";
	echo "<br /><br />\n";
	echo "<input type=\"text\" name=\"oiopub_postwords_min\" value=\"".$oiopub_set->posts['min_words']."\" />\n";
	echo "&nbsp;&nbsp;<i>minimum number of words allowed per post submitted, recommended value of 100</i>\n";
	echo "<br /><br />\n";
	echo "<b>Post Submission Rate</b>\n";
	echo "<br /><br />\n";
	echo "<input type=\"text\" name=\"oiopub_postday_max1\" size=\"3\" value=\"".$oiopub_set->posts['max_posts_num']."\" /> posts every <input type=\"text\" name=\"oiopub_postday_max2\" size=\"3\" value=\"".$oiopub_set->posts['max_posts_days']."\" /> days ";
	echo "&nbsp;&nbsp;<i>max number of post submissions allowed per X days</i>\n";
	echo "<br /><br />\n";
	echo "<b>Allow purchasers to submit tags?</b>\n";
	echo "<br /><br />\n";
	echo oiopub_dropmenu_kv($oiopub_set->arr_yesno, "oiopub_tagging", $oiopub_set->posts['tags']);
	echo "&nbsp;&nbsp;<i>decide whether you will allow purchasers to add tags to posts (requires tagging capabilities)</i>\n";
	echo "<br /><br />\n";
	echo "<b>Post Disclosure:</b>\n";
	echo "<br /><br />\n";
	echo "<input type=\"text\" name=\"oiopub_disclosure\" value=\"" . stripslashes($oiopub_set->disclosure) . "\" size=\"40\" />";
	echo "&nbsp;&nbsp;<i>text / html used to disclose paid posts, leave blank to disable this feature</i>\n";
	echo "<br />\n";
	echo "<div class=\"submit\"><input type=\"submit\" value=\"Update Settings\" /></div>\n";
	echo "</form>\n";
}

?>