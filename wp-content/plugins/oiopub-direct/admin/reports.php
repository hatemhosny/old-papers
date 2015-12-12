<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

//reports manager
function oiopub_admin_reports_manager() {
	global $oiopub_set;
	$time = time() + 86400;
	$date = date("Y-m-d", $time);
	$date_from = $date_to = $history = 0;
	if(isset($_POST['oiopub_date_day1'])) {
		//date from
		$date_day1 = intval($_POST['oiopub_date_day1']);
		$date_month1 = intval($_POST['oiopub_date_month1']);
		$date_year1 = intval($_POST['oiopub_date_year1']);
		if($date_day1 > 0 && $date_month1 > 0 && $date_year1 > 0) {
			$date_from = $date_year1 . "-" . $date_month1 . "-" . $date_day1;
		}
		//date to
		$date_day2 = intval($_POST['oiopub_date_day2']);
		$date_month2 = intval($_POST['oiopub_date_month2']);
		$date_year2 = intval($_POST['oiopub_date_year2']);
		if($date_day2 > 0 && $date_month2 > 0 && $date_year2 > 0) {
			$date_to = $date_year2 . "-" . $date_month2 . "-" . $date_day2;
		}
		//history
		$history = intval($_POST['oiopub_history']);
	} else {
		//date from
		$exp = explode("-", $date);
		$t = oiopub_var('t', 'get');
		if($t == 0) $start_date = date("Y-m-d", $time - (86400 * 7));
		if($t == 1) $start_date = date("Y-m-d", $time - (86400 * 7));
		if($t == 2) $start_date = date("Y-m-d", $time - (86400 * 30));
		if($t == 3) $start_date = date("Y-m-d", $time - (86400 * 90));
		if($t == 4) { $start_date = 0; $date = 0; }
		$exp = explode("-", $start_date);
		$date_day1 = $exp[2];
		$date_month1 = $exp[1];
		$date_year1 = $exp[0];
		if($date_day1 > 0 && $date_month1 > 0 && $date_year1 > 0) {
			$date_from = $date_year1 . "-" . $date_month1 . "-" . $date_day1;
		}
		//date to
		$exp = explode("-", $date);
		$date_day2 = $exp[2];
		$date_month2 = $exp[1];
		$date_year2 = $exp[0];
		if($date_day2 > 0 && $date_month2 > 0 && $date_year2 > 0) {
			$date_to = $date_year2 . "-" . $date_month2 . "-" . $date_day2;
		}
	}
	$array1[0] = "- day -"; for($z=1; $z <= 31; $z++) $array1[$z] = $z;
	$array2[0] = "- month -"; $array2 = oiopub_get_months();
	$array3[0] = "- year -"; for($z=2007; $z <= date('Y', time()); $z++) $array3[$z] = $z;
	echo "<h2>Reporting: Purchase Data</h2>\n";
	echo "View and export purchase data within your specified criteria. The default time-frame is <b>last 7 days</b>.\n";
	echo "<br /><br /><br />\n";
	echo "<b>Quick Links:</b> &nbsp;";
	echo "<a href='admin.php?page=oiopub-manager.php&opt=reports&t=1'>Last 7 Days</a> | ";
	echo "<a href='admin.php?page=oiopub-manager.php&opt=reports&t=2'>Last 30 Days</a> | ";
	echo "<a href='admin.php?page=oiopub-manager.php&opt=reports&t=3'>Last 90 Days</a> | ";
	echo "<a href='admin.php?page=oiopub-manager.php&opt=reports&t=4'>All Time</a>\n";
	echo "<br /><br />\n";
	echo "<b>Export:</b> &nbsp;";
	echo "<a href='" . $oiopub_set->plugin_url_org . "/export.php?do=html&type=purchases&from=" . $date_from . "&to=" . $date_to ."&history=" . $history . "' target='_blank'>html format</a> | ";
	echo "<a href='" . $oiopub_set->plugin_url_org . "/export.php?do=excel&type=purchases&from=" . $date_from . "&to=" . $date_to ."&history=" . $history . "'>excel format</a>\n";
	echo "<br /><br /><br />\n";
	echo "<form action=\"" . oiopub_clean($_SERVER["REQUEST_URI"]) . "\" method=\"post\">\n";
	echo "<input type='hidden' name='csrf' value='" . oiopub_csrf_token() . "' />\n";
	echo "<table border='0' cellspacing='4' cellpadding='4'>";
	echo "<tr>";
	echo "<td><b>Purchased from:</b></td>";
	echo "<td>" . oiopub_dropmenu_kv($array1, 'oiopub_date_day1', $date_day1) . " " . oiopub_dropmenu_kv($array2, 'oiopub_date_month1', $date_month1) . " " . oiopub_dropmenu_kv($array3, 'oiopub_date_year1', $date_year1) . "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td><b>Purchased to:</b></td>";
	echo "<td>" . oiopub_dropmenu_kv($array1, 'oiopub_date_day2', $date_day2) . " " . oiopub_dropmenu_kv($array2, 'oiopub_date_month2', $date_month2) . " " . oiopub_dropmenu_kv($array3, 'oiopub_date_year2', $date_year2) . "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td></td>";
	echo "<td><input type='checkbox' name='oiopub_history' value='1'" . ($history == 1 ? " checked='checked'" : "") . " /> Include purchase history (expired ads)?</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td></td>";
	echo "<td><input type=\"submit\" value=\"Filter Results\" /></td>";
	echo "</tr>";
	echo "</table>";
	echo "</form>\n";
	echo "<br /><br />\n";
	$args = array( "from"=>$date_from, "to"=>$date_to, "history"=>$history );
	echo oiopub_reports("html", "purchases", $args);
	echo "<br /><br />\n";
}

?>