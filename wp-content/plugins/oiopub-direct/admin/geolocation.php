<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();


//geolocation admin options
function oiopub_admin_geolocation() {
	global $oiopub_set;
	//save data?
	if(isset($_POST['oiopub_demographics_enabled'])) {
		//download local db?
		if($_POST['oiopub_demographics_db'] == 'local' && !is_file($oiopub_set->folder_dir . '/include/geo/GeoIP.dat')) {
			//temp update
			$_POST['oiopub_demographics_db'] = 'webservice';
			//continue?
			if(function_exists('file_put_contents') && $data = oiopub_file_contents('http://download.oiopublisher.com/misc/geo/GeoIP.dat')) {
				//save data
				$res = file_put_contents($oiopub_set->folder_dir . '/include/geo/GeoIP.dat', $data, LOCK_EX);
				//success?
				if($res !== false) {
					$_POST['oiopub_demographics_db'] = 'local';
				}
			}
		}
		//set vars
		$array = array();
		$array['enabled'] = intval($_POST['oiopub_demographics_enabled']);
		$array['db'] = oiopub_clean($_POST['oiopub_demographics_db']);
		oiopub_update_config('demographics', $array);
		unset($array);
	}
	//display admin form
	echo "<h2>Ad geolocation</h2>\n";
	echo "Target ads based on the location of your visitors.\n";
	echo "<br /><br />\n";
	echo "<form action=\"" . oiopub_clean($_SERVER["REQUEST_URI"]) . "\" method=\"post\">\n";
	echo "<input type='hidden' name='csrf' value='" . oiopub_csrf_token() . "' />\n";
	echo "<b>Enable geolocation?</b>";
	echo "<br /><br />\n";
	echo oiopub_dropmenu_kv($oiopub_set->arr_yesno, "oiopub_demographics_enabled", $oiopub_set->demographics['enabled']);
	echo "&nbsp;&nbsp;<i>do you wish to enable this feature?</i>\n";
	echo "<br /><br />\n";
	echo "<b>Geo database type</b>";
	echo "<br /><br />\n";
	echo oiopub_dropmenu_k(array( 'webservice', 'local' ), "oiopub_demographics_db", $oiopub_set->demographics['db']);
	echo "&nbsp;&nbsp;<i>Select the local database option, if you are having issues with site loading times using the webservice.</i>\n";	
	echo "<br />\n";
	echo "<div class=\"submit\"><input type=\"submit\" value=\"Update Settings\" /></div>\n";
	echo "</form>\n";
	echo "<br /><br />\n";
	echo "<b>Current limitations:</b>\n";
	echo "<br /><br />\n";
	echo "* It can be used with OIO's default ads only.\n";
	echo "<br />\n";
	echo "* It can target visitors by country, but not city or state.\n";
	echo "<br /><br />\n";
	echo "<b>Location Targeting:</b>\n";
	echo "<br /><br />\n";
	if(oiopub_links) {
		echo "* <a href='admin.php?page=oiopub-adzones.php&opt=link#defaults'>Text Ads</a>\n";
		echo "<br />\n";
	}
	if(oiopub_banners) {
		echo "* <a href='admin.php?page=oiopub-adzones.php&opt=banner#defaults'>Banner Ads</a>\n";
		echo "<br />\n";
	}
	if(oiopub_inline) {
		echo "* <a href='admin.php?page=oiopub-adzones.php&opt=inline#defaults'>Inline Ads</a>\n";
		echo "<br />\n";
	}
}

?>