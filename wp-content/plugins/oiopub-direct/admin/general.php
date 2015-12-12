<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

//intro page
function oiopub_admin_general_intro($no_title=false) {
	global $oiopub_set, $oiopub_alerts;
	$page = oiopub_var('page', 'get');
	if(!$oiopub_set->version) {
		echo "<p>Hmmm... it looks as though there has been a problem with installation. Please <a href='admin.php?page=oiopub-opts.php&do=oiopub-remove'>click here</a> to uninstall OIO, then try the installation again.</p>\n";
		echo "<p>If the problem persists, please check (or ask your web host to check) that your database has the necessary permissions to create new tables.</p>\n";
		return;
	}
	echo "<table width='100%' style='margin-top:10px;'>\n";
	echo "<tr><td valign='top' style='padding-right:30px;'>\n";
	echo "<h2>OIO: quick start guide (v$oiopub_set->version)</h2>\n";
	echo "<div style='line-height:22px;'>\n";
	echo "1.) First off, check out this <a href='http://docs.oiopublisher.com/get-started/' target='_blank'><b>short tutorial</b></a>, to help you get to grips with the basics.\n";
	echo "<br />\n";
	echo "2.) Now it's time to go through  OIO's <a href='admin.php?page=oiopub-opts.php'><b>settings</b></a> and configure your <a href='admin.php?page=oiopub-adzones.php'><b>ad zones</b></a>.\n";
	echo "<br />\n";
	echo "3.) Finally, remember to place the <a href='admin.php?page=" . $page . "&help=1&show=output'><b>ad code</b></a> on your web pages, to start displaying ads.\n";
	echo "<br />\n";
	echo "4.) If you require any further help, please take a look at our <a href='http://docs.oiopublisher.com' target='_blank'><b>tutorials</b></a> or ask on the <a href='http://forum.oiopublisher.com' target='_blank'><b>forum</b></a>.\n";
	echo "</div>\n";
	echo "<br /><br />\n";
	echo "<h2>New purchase alerts</h2>\n";
	$oiopub_alerts->purchases(1);
	echo "<br /><br />\n";
	echo "<h2>Advertiser purchase url</h2>\n";
	echo "&raquo; <a href='" . $oiopub_set->plugin_url . "/purchase.php' target='_blank'>" . $oiopub_set->plugin_url . "/purchase.php</a>\n";
	echo "<br /><br /><br />\n";
	echo "<h2>OIO marketplace</h2>\n";	
	echo "Put your site in front of a wider advertising audience, by listing it in the OIO marketplace. <a href='admin.php?page=oiopub-api.php'>Find out more</a>.\n";
	echo "</td><td valign='top' style='width:300px;'>\n";
	echo "<div style='padding:12px; background:#F1F1F1; border:1px solid #D1D1D1; height:160px;'>\n";
	echo "<h3 style='margin:0 0 15px 0;'>Latest news <span>&nbsp;<img src='" . $oiopub_set->plugin_url_org . "/images/feed.png' style='border:0px; width:20px; height:20px;' alt='Subscribe' /></span></h3>\n";	
	$oiopub_alerts->news(5);
	echo "</div>\n";
	echo "<div style='margin-top:20px; padding:12px; background:#C1FFC1; border:2px solid #009900;'>\n";
	echo "<font size='3'><b><i>NEW!</i></b></font> you can now charge customers per day, per click or per impression.\n";
	echo "</div>\n";
	echo "</td></tr>\n";
	echo "</table>\n";
	echo "<br /><br />\n";	
	echo "<span id='cron'></span>\n";
	echo "<h2>Scheduled tasks (last accessed: " . ($oiopub_set->cron_accessed ? (time() - $oiopub_set->cron_accessed - 60) . 's' : 'never') . ", last run: " . ($oiopub_set->cron_running ? (time() - $oiopub_set->cron_running - 30) . 's' : 'never') . ")</h2>\n";
	echo "<i>&raquo; If something stops working correctly, try re-setting the automated tasks by <a href='admin.php?page=" . $page . "&oiopub_cron=all#cron' style='color:red;'>clicking here</a>.</i>\n";
	echo "<br /><br />\n";
	oiopub_admin_general_tasks();
}

//schedulred tasks manager
function oiopub_admin_general_tasks() {
	global $oiopub_set, $oiopub_cron;
	//get vars
	$time = time();
	$page = oiopub_var("page", "get");
	$cron_run = oiopub_var("oiopub_cron", "get");
	//run cron
	if($cron_run == "all") {
		if(oiopub_auth_check()) {
			$oiopub_cron->refresh_all();
		}
	} elseif(is_numeric($cron_run) && $cron_run > 0) {
		if(oiopub_auth_check()) {
			$oiopub_cron->run_job($cron_run, false);
		}
	}
	if(oiopub_count($oiopub_set->cron_jobs) > 0) {
		$count = 0;
		echo "<div style='line-height:20px;'>\n";
		foreach($oiopub_set->cron_jobs as $key => $val) {
			$count++;
			$type = "";
			$val['time'] = $val['time'] - $time;
			if($val['time'] > 5400) {
				$diff = ceil($val['time'] / 3600) . " hours";
			} else {
				$diff = ceil($val['time'] / 60) . " minutes";
			}
			if($val['period'] > 5400) {
				$period = ceil($val['period'] / 3600) . " hours";
			} else {
				$period = ceil($val['period'] / 60) . " minutes";
			}
			if(strpos($key, "oiopub_cron") !== false) {
				$type = "A <b>core</b>";
			} elseif(strpos($key, "oiopub_affiliates") !== false) {
				$type = "An <b>affiliate</b>";
			} elseif(strpos($key, "oiopub_tracker") !== false) {
				$type = "A <b>stats tracker</b>";
			} elseif(strpos($key, "oiopub_api") !== false) {
				$type = "An <b>api</b>";
			} else {
				$type = "A <b>custom</b>";
			}	
			$output  = $type . " task is scheduled to run in " . $diff . ", runs once every " . $period . " &nbsp; [<a href='admin.php?page=" . $page . "&oiopub_cron=" . $count . "#cron'>run manually</a>]";
			$output .= "<br />\n";
			echo $output;
		}
		echo "</div>\n";
	} else {
		echo "No cron jobs are set to run\n";
	}
}

//general settings
function oiopub_admin_general_settings() {
	global $oiopub_set;
	oiopub_admin_general_update();
	echo "<h2>On / Off Switches</h2>\n";
	oiopub_admin_general_enabled();
	oiopub_admin_general_uninstall();
	oiopub_admin_general_testpay();
	echo "<br /><br />\n";
	echo "<form action=\"" . $oiopub_set->request_uri . "\" method=\"post\">\n";
	echo "<input type='hidden' name='csrf' value='" . oiopub_csrf_token() . "' />\n";
	echo "<input type=\"hidden\" name=\"notify\" value=\"1\" />\n";
	oiopub_admin_general_basic();
	oiopub_admin_general_payment();
	oiopub_admin_general_advanced();
	oiopub_admin_url_rewrite();
	oiopub_admin_general_guidelines();
	echo "</form>\n";
}

//enable switch
function oiopub_admin_general_enabled() {
	global $oiopub_set;
	if(isset($_POST['wp_enable'])) {
		oiopub_update_config('enabled', 1);
	}
	if(isset($_POST['wp_disable']) && !$oiopub_set->demo) {
		oiopub_update_config('enabled', 0);
	}
	echo "<form action=\"" . $oiopub_set->request_uri . "\" method=\"post\">\n";
	echo "<input type='hidden' name='csrf' value='" . oiopub_csrf_token() . "' />\n";
	echo "<input type=\"hidden\" name=\"notify\" value=\"2\" />\n";
	if($oiopub_set->enabled == 1) {
		echo "<table width=\"100%\" border=\"0\">\n";
		echo "<tr><td>\n";
		echo "<b>OIOpublisher Direct is Enabled:</b>\n";
		echo "<br /><br />\n";
		echo "<i>disable all script functions by clicking the 'disable' button</i>\n";
		echo "</td><td align=\"right\">\n";
		echo "<input type=\"hidden\" name=\"wp_disable\" />\n";
		echo "<div class=\"submit\"><input type=\"submit\" value=\"Disable OIOpublisher\" /></div>\n";
		echo "</td></tr>\n";
		echo "</table>\n";
	} else {
		echo "<table width=\"100%\ border=\"0\">\n";
		echo "<tr><td>\n";
		echo "<b>OIOpublisher Direct is Disabled:</b>\n";
		echo "<br /><br />\n";
		echo "<i>enable all script functions by clicking the 'enable' button</i>\n";
		echo "</td><td align=\"right\">\n";
		echo "<input type=\"hidden\" name=\"wp_enable\" />\n";
		echo "<div class=\"submit\"><input type=\"submit\" value=\"Enable OIOpublisher\" /></div>\n";
		echo "</td></tr>\n";
		echo "</table>\n";
	}
	echo "</form>\n";
}

//payment test mode
function oiopub_admin_general_testpay() {
	global $oiopub_set;
	if(isset($_POST['oiopub_testpay_on'])) {
		oiopub_update_config('testmode_payment', 1);
	}
	if(isset($_POST['oiopub_testpay_off']) && !$oiopub_set->demo) {
		oiopub_update_config('testmode_payment', 0);
	}
	echo "<br />\n";
	echo "<form action=\"" . $oiopub_set->request_uri . "\" method=\"post\">\n";
	echo "<input type='hidden' name='csrf' value='" . oiopub_csrf_token() . "' />\n";
	echo "<input type=\"hidden\" name=\"notify\" value=\"2\" />\n";
	if($oiopub_set->testmode_payment == 1) {
		echo "<table width=\"100%\" border=\"0\">\n";
		echo "<tr><td>\n";
		echo "<b>Payment Testmode is Enabled:</b>\n";
		echo "<br /><br />\n";
		echo "<i>disabling payment testmode will require payment for every purchase</i>\n";
		echo "</td><td align=\"right\">\n";
		echo "<input type=\"hidden\" name=\"oiopub_testpay_off\" />\n";
		echo "<div class=\"submit\"><input type=\"submit\" value=\"Disable Payment Testmode\" /></div>\n";
		echo "</td></tr>\n";
		echo "</table>\n";
	} else {
		echo "<table width=\"100%\" border=\"0\">\n";
		echo "<tr><td>\n";
		echo "<b>Payment Testmode is Disabled:</b>\n";
		echo "<br /><br />\n";
		echo "<i>enabling payment testmode will automatically mark purchases as paid when a submission is made</i>\n";
		echo "</td><td align=\"right\">\n";
		echo "<input type=\"hidden\" name=\"oiopub_testpay_on\" />\n";
		echo "<div class=\"submit\"><input type=\"submit\" value=\"Enable Payment Testmode\" /></div>\n";
		echo "</td></tr>\n";
		echo "</table>\n";
	}
	echo "</form>\n";
}

//payment test mode
function oiopub_admin_general_uninstall() {
	global $oiopub_set;
	echo "<br />\n";
	echo "<form action=\"" . $oiopub_set->request_uri . "\" method=\"post\" onsubmit=\"return confirm('Are you sure you wish to uninstall OIOpublisher? You will lose all of your data!');\">\n";
	echo "<input type='hidden' name='csrf' value='" . oiopub_csrf_token() . "' />\n";
	echo "<input type=\"hidden\" name=\"page\" value=\"oiopub-opts.php\" />\n";
	echo "<input type=\"hidden\" name=\"do\" value=\"oiopub-remove\" />\n";
	echo "<table width=\"100%\" border=\"0\">\n";
	echo "<tr><td>\n";
	echo "<b>Uninstall Script?</b>\n";
	echo "<br /><br />\n";
	echo "<i>this will remove all database tables and settings (be careful!)</i>\n";
	echo "</td><td align=\"right\">\n";
	echo "<div class=\"submit\"><input type=\"submit\" value=\"Uninstall OIOpublisher\" /></div>\n";
	echo "</td></tr>\n";
	echo "</table>\n";
	echo "</form>\n";
}

//basic settings
function oiopub_admin_general_basic() {
	global $oiopub_set;
	if(strlen($oiopub_set->global_pass) == 32) {
		$global_pass = "password";
	} else {
		$global_pass = "";
	}
	echo "<span id='basic'></span>\n";
	echo "<h2>Basic Settings</h2>\n";
	echo "<br />\n";
	echo "<b>Website Name:</b>\n";
	echo "<br /><br />\n";
	echo "<input type=\"text\" name=\"oiopub_site_name\" size=\"40\" value=\"" . $oiopub_set->site_name . "\" />\n";
	echo "&nbsp;&nbsp;<i>the website name you want to display to advertisers (eg. My Website Ads)</i>\n";
	echo "<br /><br />\n";
	echo "<b>Contact Email:</b>\n";
	echo "<br /><br />\n";
	echo "<input type=\"text\" name=\"oiopub_contact_mail\" size=\"40\" value=\"".$oiopub_set->admin_mail."\" size=\"30\" />\n";
	echo "&nbsp;&nbsp;<i>the email address used for sending email to advertisers</i>\n";
	echo "<br /><br />\n";
	echo "<b>Affiliate ID:</b>\n";
	echo "<br /><br />\n";
	echo "<input type=\"text\" name=\"oiopub_affiliate_id\" size=\"40\" value=\"" . $oiopub_set->affiliate_id . "\" />\n";
	echo "&nbsp;&nbsp;<i>entering your OIOpublisher.com affiliate ID here will add it automatically to the 'powered by' links</i>\n";
	echo "<br />\n";
	if($oiopub_set->platform == "standalone") {
		echo "<br />\n";
		echo "<b>Admin Password:</b>\n";
		echo "<br /><br />\n";
		echo "<input type=\"password\" name=\"oiopub_admin_pass\" size=\"40\" value=\"\" />\n";
		echo "&nbsp;&nbsp;<i>change your password when logging into OIOpublisher</i>\n";
		echo "<br />\n";
	}
	/*
	echo "<b>Global Password:</b>\n";
	echo "<br /><br />\n";
	echo "<input type=\"password\" name=\"oiopub_global_pass\" size=\"40\" value=\"" . $global_pass . "\" />\n";
	echo "&nbsp;&nbsp;<i>please ensure this password is the same for all sites you install OIOpublisher on</i>\n";
	echo "<br />\n";
	*/
	echo "<div class=\"submit\"><input type=\"submit\" value=\"Update Settings\" /></div>\n";
	echo "<br />\n";
}

//general settings, payment
function oiopub_admin_general_payment() {
	global $oiopub_set, $oiopub_hook;
	echo "<span id='payment'></span>\n";
	echo "<h2>Payment Settings</h2>\n";
	echo "<br />\n";
	echo "<b>Currency code:</b>\n";
	echo "<br /><br />\n";
	echo "<input type=\"text\" name=\"oiopub_currency\" size=\"40\" value=\"" . $oiopub_set->general_set['currency'] . "\" />\n";
	echo "&nbsp;&nbsp;<i>required: the currency code used when taking payments (full list <a href='http://en.wikipedia.org/wiki/ISO_4217#Active_codes' target='_blank'>available here</a>)</i>\n";
	echo "<br /><br />\n";
	echo "<b>Currency symbol:</b>\n";
	echo "<br /><br />\n";
	echo "<input type=\"text\" name=\"oiopub_currency_symbol\" size=\"40\" value=\"" . (isset($oiopub_set->general_set['currency_symbol']) ? $oiopub_set->general_set['currency_symbol'] : '') . "\" />\n";
	echo "&nbsp;&nbsp;<i>optional: if entered, the currency symbol will be shown to advertisers instead of the currency code (e.g $)</i>\n";
	echo "<br /><br />\n";
	echo "<b>Payment Methods:</b>\n";
	echo "<br /><br />\n";
	$oiopub_hook->fire('admin_payment');
	echo "<br />\n";
}

//general settings, advanced
function oiopub_admin_general_advanced() {
	global $oiopub_set;
	echo "<span id='advanced'></span>\n";
	echo "<h2>Advanced Settings</h2>\n";
	echo "<br />\n";
	echo "<b>Allow advertisers to edit ads?</b>\n";
	echo "<br /><br />\n";
	echo oiopub_dropmenu_kv($oiopub_set->arr_yesno, "oiopub_edit_ads", $oiopub_set->general_set['edit_ads']);
	echo "&nbsp;&nbsp;<i>if set to 'yes', advertisers will be able to update their text / banner ads after purchasing</i>\n";
	echo "<br /><br />\n";
	echo "<b>Only allow payment after ad approved?</b>\n";
	echo "<br /><br />\n";
	echo oiopub_dropmenu_kv($oiopub_set->arr_yesno, "oiopub_paytime", $oiopub_set->general_set['paytime']);
	echo "&nbsp;&nbsp;<i>selecting 'yes' will require the ad to be approved before the advertiser can pay, otherwise the ad can be approved after payment</i>\n";
	echo "<br /><br />\n";
	echo "<b>Allow Subscription Payments?</b>\n";
	echo "<br /><br />\n";
	echo oiopub_dropmenu_kv($oiopub_set->arr_yesno, "oiopub_subscription", $oiopub_set->general_set['subscription']);
	echo "&nbsp;&nbsp;<i>only enable this option if you are happy to let advertisers pay the same amount per purchase period indefinitely (<font color='red'>'cost per day' charging model only</font>)</i>\n";
	echo "<br /><br />\n";
	echo "<b>Display Ads in New Window?</b>\n";
	echo "<br /><br />\n";
	echo oiopub_dropmenu_kv($oiopub_set->arr_yesno, "oiopub_new_window", $oiopub_set->general_set['new_window']);
	echo "&nbsp;&nbsp;<i>setting this value to yes will mean that when an ad is clicked on, it will open in a new window</i>\n";
	echo "<br /><br />\n";
	echo "<b>Allow Image Uploading?</b>\n";
	echo "<br /><br />\n";
	echo oiopub_dropmenu_kv($oiopub_set->arr_yesno, "oiopub_upload", $oiopub_set->general_set['upload']);
	echo "&nbsp;&nbsp;<i>setting this value to yes will mean that advertisers can upload their ads to your server</i>\n";
	echo "<br /><br />\n";
	echo "<b>Use Security Question?</b>\n";
	echo "<br /><br />\n";
	echo oiopub_dropmenu_kv($oiopub_set->arr_yesno, "oiopub_security_question", $oiopub_set->general_set['security_question']);
	echo "&nbsp;&nbsp;<i>setting this value to yes will mean that advertisers have to answer a security question</i>\n";
	echo "<br /><br />\n";
	if(oiopub_inline && oiopub_posts) {
		echo "<b>Post Specific Purchase Links?</b>\n";
		echo "<br /><br />\n";
		echo oiopub_dropmenu_kv($oiopub_set->arr_yesno, "oiopub_postlinks", $oiopub_set->general_set['postlinks']);
		echo "&nbsp;&nbsp;<i>allow the display of post specific purchase links directly under post content</i>\n";
		echo "<br /><br />\n";
	}
	echo "<b>Redirect Purchase links to Custom URL?</b>\n";
	echo "<br /><br />\n";
	echo "<input type=\"text\" name=\"oiopub_buypage\" value=\"".$oiopub_set->general_set['buypage']."\" size=\"40\" />\n";
	echo "&nbsp;&nbsp;<i>takes advertisers to the link you enter instead of the purchase form (leave empty to disable)</i>\n";
	echo "<br /><br />\n";
	echo "<b>Request Feedback:</b>\n";
	echo "<br /><br />\n";
	echo "<input type=\"text\" name=\"oiopub_feedback\" size=\"40\" value=\"".$oiopub_set->feedback."\" />\n";
	echo "&nbsp;&nbsp;<i>enter the url to your OIOpublisher Marketplace listing, to request feedback from clients</i>\n";
	echo "<br />\n";
	echo "<div class=\"submit\"><input type=\"submit\" value=\"Update Settings\" /></div>\n";
	echo "<br />\n";
}

//marketplace fedback
function oiopub_admin_url_rewrite() {
	global $oiopub_set;
	$server = $oiopub_set->host_name . "/";
	echo "<style type=\"text/css\">.example { color:#675D1C; border:1px solid #FFE95A; background:#FFFBE0; padding:10px; overflow:visible; font-size:12px; }</style>\n";
	echo "<span id='rewrite'></span>\n";
	echo "<h2>Rewrite Rules</h2>\n";
	echo "<br />\n";
	echo "<b>Original Script URL:</b>\n";
	echo "<br /><br />\n";
	echo "<i>" . $oiopub_set->plugin_url_org . "</i>\n";
	echo "<br /><br />\n";
	echo "<b>Rewrite Script URL:</b>\n";
	echo "<br /><br />\n";
	echo "<input type=\"text\" name=\"oiopub_plugin_rewrite\" size=\"60\" value=\"".$oiopub_set->plugin_rewrite."\" />\n";
	echo "&nbsp;&nbsp;<i>if you'd like to change your script url, please enter the url here</i>\n";
	if(!empty($oiopub_set->plugin_rewrite)) {
		echo "<br /><br />\n";
		echo "<b>Rewrite Rules Instructions:</b>\n";
		echo "<br /><br />\n";
		echo "* In your site root directory, check for an .htaccess file. Create one if not present (requires apache web server).\n";
		echo "<br />\n";
		echo "* In the .htaccess file, check for the line 'RewriteEngine On'. Add it to the top of the file if not present.\n";
		echo "<br />\n";
		echo "* Now <b>manually</b> add the following code <u>directly underneath</u> the 'RewriteEngine On' line.\n";
		echo "<br />\n";
		echo "<div class='example' style='margin-top:30px; margin-bottom:-10px;'>\n";
		echo "## OIOpublisher Rewrite\n";
		echo "<br />\n";
		echo "RewriteCond %{REQUEST_FILENAME} !-f\n";
		echo "<br />\n";
		echo "RewriteCond %{REQUEST_FILENAME} !-d\n";
		echo "<br />\n";
		echo "RewriteRule ^" . str_replace($server, "", $oiopub_set->plugin_rewrite) . "/(.+)$ " . str_replace($server, "", $oiopub_set->plugin_url_org) . "/$1 [L]\n";
		echo "</div>\n";
	}
	echo "<br />\n";
	echo "<div class=\"submit\"><input type=\"submit\" value=\"Update Settings\" /></div>\n";
	echo "<br />\n";
}

//purchasing guidelines
function oiopub_admin_general_guidelines() {
	global $oiopub_set;
	echo "<span id='guidelines'></span>\n";
	echo "<h2>Purchasing Guidelines</h2>\n";
	echo "<br />\n";
	echo "<b>Guidelines to advertisers when making a purchase:</b>\n";
	echo "<br /><br />\n";
	echo "<textarea name=\"oiopub_rules\" cols=\"80\" rows=\"8\">".stripslashes($oiopub_set->rules)."</textarea>\n";
	echo "<br />\n";
	echo "<div class=\"submit\"><input type=\"submit\" value=\"Update Settings\" /></div>\n";
	echo "<br />\n";
}

//general settings, update
function oiopub_admin_general_update() {
	global $oiopub_set;
	if(isset($_POST['oiopub_buypage'])) {
		//get vars
		$array = array();
		$array['currency'] = oiopub_clean($_POST['oiopub_currency']);
		$array['currency_symbol'] = oiopub_clean($_POST['oiopub_currency_symbol']);
		$array['edit_ads'] = intval($_POST['oiopub_edit_ads']);
		$array['paytime'] = intval($_POST['oiopub_paytime']);
		$array['thickbox'] = intval($_POST['oiopub_thickbox']);
		$array['subscription'] = intval($_POST['oiopub_subscription']);
		$array['postlinks'] = intval($_POST['oiopub_postlinks']);
		$array['new_window'] = intval($_POST['oiopub_new_window']);
		$array['upload'] = intval($_POST['oiopub_upload']);
		$array['buypage'] = oiopub_clean($_POST['oiopub_buypage']);
		$array['security_question'] = intval($_POST['oiopub_security_question']);
		$feedback = oiopub_clean($_POST['oiopub_feedback']);
		$plugin_rewrite = oiopub_clean(rtrim($_POST['oiopub_plugin_rewrite'], '/'));
		$site_name = oiopub_clean($_POST['oiopub_site_name']);
		$site_mail = oiopub_clean($_POST['oiopub_contact_mail']);
		$affiliate_id = intval($_POST['oiopub_affiliate_id']);
		$global_pass = (empty($_POST['oiopub_global_pass']) ? '' : md5($_POST['oiopub_global_pass']));
		//format plugin rewrite
		if($parse = parse_url($plugin_rewrite)) {
			if(!isset($parse['host']) && $parse['path']) {
				$parse['path'] = "/" . $parse['path'];
				$plugin_rewrite = $oiopub_set->host_name . str_replace('//', '/', $parse['path']);
			}
		}
		//ID redirect?
		if($plugin_rewrite !== $oiopub_set->plugin_rewrite) {
			$redirect = true;
		} else {
			$redirect = false;
		}
		//update
		oiopub_update_config('general_set', $array);
		oiopub_update_config('site_name', $site_name);
		oiopub_update_config('admin_mail', $site_mail);
		oiopub_update_config('affiliate_id', $affiliate_id);
		oiopub_update_config('global_pass', $global_pass);
		oiopub_update_config('plugin_rewrite', $plugin_rewrite);
		oiopub_update_config('rules', $_POST['oiopub_rules']);
		oiopub_update_config('feedback', $feedback);
		//update admin password?
		if(isset($_POST['oiopub_admin_pass']) && !empty($_POST['oiopub_admin_pass'])) {
			global $oiopub_admin;
			$password = oiopub_clean($_POST['oiopub_admin_pass']);
			$oiopub_admin->update_password('', $password);
		}
		//unset
		unset($array);
		//redirect
		if($redirect == true) {
			echo "<meta http-equiv=\"refresh\" content=\"0;URL=admin.php?page=oiopub-opts.php#rewrite\" />\n";
		}
	}
}

?>