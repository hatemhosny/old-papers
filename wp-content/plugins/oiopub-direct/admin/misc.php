<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

//testmode reminder
function oiopub_testmode_reminder() {
	global $oiopub_set;
	if($oiopub_set->demo) {
		echo "<font color='red'><b>This is a read-only demonstration, you cannot make any changes to the settings!</b></font>\n";
		echo "<br /><br />";
	} elseif($oiopub_set->testmode_payment == 1) {
		echo "<font color='red'><b>Payment testmode is currently active. You must turn this feature off before going live!</b></font>\n";
		echo "<br /><br />";
	}
}

//key required message
function oiopub_admin_key_required($styling='') {
	global $oiopub_set;
	$output = ''; $css1 = ''; $css2 = '';
	if(strlen($oiopub_set->api_key) != 16 || $oiopub_set->api_valid != 1) {
		if(!empty($styling)) {
			$css1 = "<div style='$styling'>";
			$css2 = "</div>";
		}
		$output = $css1 . "<font color='red'><b>Please enter your OIOpublisher <a href='admin.php?page=oiopub-api.php' style='color:red;'>API Key</a> to continue!</b></font>" . $css2 . "\n";
	}
	return $output;
}

function oiopub_admin_options_menu($page, $option_type) {
	global $oiopub_set;
	$items = array();
	if($page != 'oiopub-adzones') {
		if(empty($option_type)) {
			$items[] = "<a href='admin.php?page=" . $page . ".php'><font color='red'><b>General</b></font></a>";
		} else {
			$items[] = "<a href='admin.php?page=" . $page . ".php'><b>General</b></a>";
		}
	}
	if($page == 'oiopub-opts') {
		if($option_type == "emails") {
			$items[] = "<a href='admin.php?page=" . $page . ".php&opt=emails'><font color='red'><b>Emails</b></font></a>";
		} else {
			$items[] = "<a href='admin.php?page=" . $page . ".php&opt=emails'><b>Emails</b></a>";
		}
		if($option_type == "templates") {
			$items[] = "<a href='admin.php?page=" . $page . ".php&opt=templates'><font color='red'><b>Themes</b></font></a>";
		} else {
			$items[] = "<a href='admin.php?page=" . $page . ".php&opt=templates'><b>Themes</b></a>";
		}
		if($option_type == "coupons") {
			$items[] = "<a href='admin.php?page=" . $page . ".php&opt=coupons'><font color='red'><b>Coupons</b></font></a>";
		} else {
			$items[] = "<a href='admin.php?page=" . $page . ".php&opt=coupons'><b>Coupons</b></a>";
		}
		if($option_type == "geolocation") {
			$items[] = "<a href='admin.php?page=" . $page . ".php&opt=geolocation'><font color='red'><b>Geolocation</b></font></a>";
		} else {
			$items[] = "<a href='admin.php?page=" . $page . ".php&opt=geolocation'><b>Geolocation</b></a>";
		}
		if($option_type == "lang") {
			$items[] = "<a href='admin.php?page=" . $page . ".php&opt=lang'><font color='red'><b>Languages</b></font></a>";
		} else {
			$items[] = "<a href='admin.php?page=" . $page . ".php&opt=lang'><b>Languages</b></a>";
		}
		//show ad zones here too
		$items[] = "<a href='admin.php?page=oiopub-adzones.php'><b>Ad zones</b></a>";
	} else {
		if(oiopub_banners) {
			if($option_type == "banner" || ($page == 'oiopub-adzones' && !$option_type)) {
				$items[] = "<a href='admin.php?page=" . $page . ".php&opt=banner'><font color='red'><b>Banner Ads</b></font></a>";
			} else {
				$items[] = "<a href='admin.php?page=" . $page . ".php&opt=banner'><b>Banner Ads</b></a>";
			}
		}
		if(oiopub_links) {
			if($option_type == "link") {
				$items[] = "<a href='admin.php?page=" . $page . ".php&opt=link'><font color='red'><b>Text Ads</b></font></a>";
			} else {
				$items[] = "<a href='admin.php?page=" . $page . ".php&opt=link'><b>Text Ads</b></a>";
			}
		}
		if(oiopub_inline) {
			if($option_type == "inline") {
				$items[] = "<a href='admin.php?page=" . $page . ".php&opt=inline'><font color='red'><b>Inline Ads</b></font></a>";
			} else {
				$items[] = "<a href='admin.php?page=" . $page . ".php&opt=inline'><b>Inline Ads</b></a>";
			}
		}
		if(oiopub_posts) {
			if($option_type == "post") {
				$items[] = "<a href='admin.php?page=" . $page . ".php&opt=post'><font color='red'><b>Posts</b></font></a>";
			} else {
				$items[] = "<a href='admin.php?page=" . $page . ".php&opt=post'><b>Posts</b></a>";
			}
		}
		if(oiopub_custom) {
			if($option_type == "custom") {
				$items[] = "<a href='admin.php?page=" . $page . ".php&opt=custom'><font color='red'><b>Custom</b></font></a>";
			} else {
				$items[] = "<a href='admin.php?page=" . $page . ".php&opt=custom'><b>Custom</b></a>";
			}
		}
		if($page == 'oiopub-manager') {
			if($option_type == "reports") {
				$items[] = "<a href='admin.php?page=" . $page . ".php&opt=reports'><font color='red'><b>Reports</b></font></a>";
			} else {
				$items[] = "<a href='admin.php?page=" . $page . ".php&opt=reports'><b>Reports</b></a>";
			}
		}
	}
	$options  = "<table width='100%' style='background:#f3f3f3; padding:10px; margin-top:5px;'>\n";
	$options .= "<tr><td>\n";
	$options .= "<font color='blue'><b>Options:</b></font> ";
	$options .= implode(" | ", $items);
	$options .= "</td></tr>\n";
	$options .= "</table>\n";
	return $options;	
}

//admin help link
function oiopub_admin_help_link() {
	return urlencode("http://" . oiopub_clean($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']));
}

?>