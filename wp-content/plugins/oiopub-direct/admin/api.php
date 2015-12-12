<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

//api key manager
function oiopub_admin_api_key() {
	global $oiopub_set, $oiopub_api, $oiopub_cron;
	$res = "";
	if(isset($_POST['oiopub_api_key'])) {
		$validating = false;
		$key = oiopub_clean($_POST['oiopub_api_key']);
		if($key != $oiopub_set->api_key) {
			if(!empty($key) && strlen($key) == 16) {
				$validating = true;
				//show load screen
				echo "<div class='loading'>Processing... <img src='" . $oiopub_set->plugin_url_org . "/images/loading.gif' style='border:0px;' alt='' /></div>\n";
				flush();
				//make api call
				$res = $oiopub_api->validate_key($key);
				//hide load screen
				echo "<style type='text/css'>.loading{display:none;}</style>\n";
				flush();
				if($res == "VALID") {
					oiopub_update_config('api_key', $key);
					oiopub_update_config('api_valid', 1);
				} else {
					oiopub_update_config('api_key', '');
					oiopub_update_config('api_valid', 0);
				}
			} elseif(empty($key)) {
				oiopub_update_config('api_key', '');
				oiopub_update_config('api_valid', 0);
				$res = "NONE";
			}
		}
		if($validating == true) {
			if(empty($res)) {
				$res = "CONTACT";
			} elseif($res != "VALID") {
				$res = "INVALID";
			}
		} elseif(empty($res)) {
			$res = "INVALID";
		}
	}
	if($oiopub_set->api_valid == 1 || $res == "VALID") {
		$valid_status = "&nbsp;<b><font color='green'>API Key is valid</font></b>";
	} elseif($res == "INVALID") {
		$valid_status = "&nbsp;<b><font color='red'>API Key entered is not valid</font></b> &nbsp;&nbsp;[have you changed the script's location? Then <a href='http://www.oiopublisher.com' target='_blank'>login</a> and update your listing]";
	} elseif($res == "CONTACT") {
		$valid_status = "&nbsp;<b><font color='blue'>Unable to complete request. Does your site use any security measures that might block incoming requests?</font></b>";
	} else {
		$valid_status = "&nbsp;<b><font color='blue'>please enter your key here</font></b>";
	}
	echo "<b>OIOpublisher API Key:</b>\n";
	echo "<br /><br />\n";
	if(function_exists('fsockopen')) {
		echo "<input type=\"text\" name=\"oiopub_api_key\" value=\"" . $oiopub_set->api_key . "\" size=\"30\" />\n";
		echo "&nbsp;&nbsp;<i>" . $valid_status . "</i>\n";
	} else {
		echo "<i>The php function fsockopen() is not enabled on your server. This function is required to use the API.</i>\n";
	}
}

//job matching
function oiopub_admin_api_jobs() {
	global $oiopub_set, $oiopub_api;
	return;
	if(isset($_POST['jobs_process']) && $_POST['jobs_process'] == 'yes') {
		$res = $oiopub_api->jobs_match();
		oiopub_update_config('jobs_match', $res);
	}
	echo "<h2>Blogging Jobs [<a href='http://labs.oiopublisher.com' target='_blank'>lab work</a>]</h2>\n";
	echo "View some of the blogging jobs you could apply for at OIOpublisher from your blogging dashboard (experimental):\n";
	echo "<table id='jobs' width='100%' border='0' cellpadding='4' cellspacing='4' style='padding-top:25px;'>\n";
	echo "<tr><td width='20%'><b>Job</b></td><td width='10%'><b>Payment</b></td><td width='10%'><b>Author</b></td><td><b>Details</b></td></tr>\n";
	echo "<tr><td colspan='4' style='border-top:1px dashed #999;'></td></tr>\n";
	$jobs = $oiopub_set->jobs_match;
	if(is_array($jobs) && oiopub_count($jobs) > 0) {
		$background = '#FFFFFF';
		$jobs_count = count($jobs);
		for($z=1; $z <= $jobs_count; $z++) {
			$job_link = "<a target='_blank' href='http://jobs.oiopublisher.com/" . $jobs[$z]['id'] . "/" . oiopub_nice_url($jobs[$z]['title']) . "/'>" . $jobs[$z]['title'] . "</a>";
			$user_link = "<a target='_blank' href='http://user.oiopublisher.com/" . $jobs[$z]['uid'] . "/" . oiopub_nice_url($jobs[$z]['login']) . "/'>" . $jobs[$z]['login'] . "</a>";
			echo "<tr style='background:$background;'><td>" . $job_link . "</td><td>$" . $jobs[$z]['price'] . "</td><td>" . $user_link. "</td><td>" . stripslashes($jobs[$z]['details']) . "</td></tr>\n";
			if($background == "#FFFFFF") {
				$background = "#E0EEEE";
			} else {
				$background = "#FFFFFF";
			}
		}
	} else {
		echo "<tr><td colspan='4' align='center' style='padding-top:10px;'><i>No matches found. Have you made a request?</i></td></tr>\n";
	}
	echo "<tr><td colspan='4' align='left' style='padding-top:30px;'>\n";
	echo "<form method='post' action='" . oiopub_clean($_SERVER['REQUEST_URI']) . "#jobs'>\n";
	echo "<input type='hidden' name='jobs_process' value='yes' />\n";
	echo "<input type='hidden' name='csrf' value='" . oiopub_csrf_token() . "' />\n";
	echo "<div class=\"submit\">";
	echo "<input type='submit' value='Update Latest Jobs Display' />&nbsp;&nbsp;";
	echo oiopub_admin_key_required();
	echo "</div>\n";
	echo "</form>\n";
	echo "</td></tr>\n";
	echo "</table>\n";
}

?>