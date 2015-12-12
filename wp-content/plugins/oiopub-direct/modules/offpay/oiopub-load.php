<?php

/*
Module: Offline Payment v2.00
Developer: http://www.simonemery.co.uk

Module constructed for OIOpublisher Direct
http://www.oiopublisher.com

Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();


//module vars
$oio_enabled = 1;
$oio_version = "2.00";
$oio_name = __oio("Offline Payment");
$oio_module = "offpay";
$oio_menu = "payment";

//min plugin version
$oio_min_version = "2.11";

//check minimum version
oiopub_module_vcheck($oio_module, $oio_min_version);


//offline pay class
class oiopub_offpay {

	//global vars
	var $name;
	var $folder;
	var $version;
	
	//init
	function oiopub_offpay($version='', $name='') {
		///set vars
		$this->name = $name;
		$this->version = $version;
		//call methods
		$this->settings();
		$this->install();
		$this->redirect();
		$this->hooks();
	}
	
	//settings
	function settings() {
		global $oiopub_set;
		//get folder name
		$dir = trim(str_replace('\\', '/', dirname(__FILE__)));
		$exp = explode('/', $dir);
		$this->folder = $exp[count($exp)-1];
		//misc settings
		$oiopub_set->offpay_folder = $this->folder;
		$oiopub_set->offpay_subscription = 0;
		//payment arrays
		if(isset($oiopub_set->offpay)) {
			if($oiopub_set->offpay['enable'] == 1 && $oiopub_set->offpay['valid'] == 1) {
				$oiopub_set->arr_payment['offpay'] = $this->name;
			}
		}
	}

	//install
	function install() {
		global $oiopub_set;
		if(!isset($oiopub_set->offpay) || $oiopub_set->offpay['install'] < $this->version) {
			if(empty($oiopub_set->offpay['install'])) {
				include_once($oiopub_set->modules_dir . '/' . $this->folder . '/install/install.php');
			} else {
				include_once($oiopub_set->modules_dir . '/' . $this->folder . '/install/upgrade.php');
			}
			$oiopub_set->offpay['install'] = $this->version;
			oiopub_update_config('offpay', $oiopub_set->offpay);
		}
	}
	
	//uninstall
	function uninstall() {
		global $oiopub_set;
		include_once($oiopub_set->modules_dir . '/' . $this->folder . '/install/uninstall.php');
	}
	
	//page redirect
	function redirect() {
		if(oiopub_is_admin()) {
			if(isset($_GET['page']) && $_GET['page'] == 'oiopub-offpay.php') {
				header("Location: admin.php?page=oiopub-opts.php&popup=offpay");
				exit();
			}
		}
	}
	
	//add actions
	function hooks() {
		global $oiopub_hook;
		if(oiopub_is_admin()) {
			if(isset($_GET['page']) && $_GET['page'] == "oiopub-opts.php") {
				$oiopub_hook->add('admin_payment', array(&$this, 'admin_options'));
				if(isset($_GET['popup']) && $_GET['popup'] == "offpay") {
					$oiopub_hook->add('settings_popup', array(&$this, 'admin_popup'));
				}
			}
			if(isset($_REQUEST['do']) && $_REQUEST['do'] == "oiopub-remove") {
				$oiopub_hook->add('delete_modules', array(&$this, 'uninstall'));
			}
		}
	}
	
	//admin options
	function admin_options() {
		global $oiopub_set;
		if($oiopub_set->offpay['valid'] == 1) {
			if($oiopub_set->offpay['enable'] == 1) {
				echo "<font color='green'><b>Offline Payments Active</b></font> - ";
			} else {
				echo "<font color='blue'><b>Offline Payments Disabled</b></font> - ";
			}
		} else {
			echo "<font color='red'><b>Offline Payments Setup Incomplete</b></font> - ";
		}
		echo "[<a href='admin.php?page=oiopub-opts.php&popup=offpay'>edit settings</a>]\n";
		echo "<br /><br />\n";
	}
	
	//admin popup
	function admin_popup() {
		global $oiopub_set;
		$info = ""; $checked = "";
		if(isset($_POST['oiopub_offpay_instructions'])) {
			$enable = intval($_POST['oiopub_offpay_enable']);
			$instructions = str_replace("\r\n", "\n", $_POST['oiopub_offpay_instructions']);
			if(!empty($instructions)) {
				$oiopub_set->offpay['enable'] = $enable;
				$oiopub_set->offpay['valid'] = 1;
				$oiopub_set->offpay['instructions'] = $instructions;
				oiopub_update_config('offpay', $oiopub_set->offpay);
				$info = "<font color='green'><i><b>Information successfully updated!</b></i></font>";
			} else {
				$oiopub_set->offpay['valid'] = 0;
				oiopub_update_config('offpay', $oiopub_set->offpay);
				$info = "<font color='red'><i><b>Information not validated. Please try again!</b></i></font>";
			}
		}
		if($oiopub_set->offpay['enable'] == 1) {
			$checked = " checked=\"checked\"";
		}
		echo "<div style='border-top:1px solid #D8D8D8; border-bottom:1px solid #D8D8D8; padding:5px 0 5px 0; margin:5px 0 20px 0;'>\n";
		if(!empty($info)) {
			echo $info . "\n";
		} else {
			echo "<i><b>Integration type:</b> you will need to update the status of purchases manually.</i>\n";
			echo "<br />\n";
			echo "<i><b>Subscriptions Supported:</b> " . ($oiopub_set->offpay_subscription == 1 ? 'yes' : 'no') . ".</i>\n";		
		}
		echo "</div>\n";
		echo "<form method='post' action='" . $oiopub_set->request_uri . "'>\n";
		echo "<input type='hidden' name='csrf' value='" . oiopub_csrf_token() . "' />\n";
		echo "<b>Enable Offline Payments?</b>\n";
		echo "<br /><br />\n";
		echo "<input type=\"checkbox\" name=\"oiopub_offpay_enable\" value=\"1\"" . $checked . " />\n";
		echo "&nbsp;&nbsp;<i>enabling this will option will give advertisers the choice to pay offline</i>\n";
		echo "<br /><br />\n";
		echo "<b>Payment Instructions:</b>\n";
		echo "<br /><br />\n";
		echo "<textarea name=\"oiopub_offpay_instructions\" style=\"width:98%; height:120px;\">" . stripslashes($oiopub_set->offpay['instructions']) . "</textarea>\n";
		echo "<br /><br />\n";
		echo "<input type='submit' value='Update Settings' />\n";
		echo "</form>\n";
	}
	
	//offpay form
	function form($rand_id) {
		global $oiopub_set;
		echo "<p><b>" . __oio("Please reference %s when making your payment.", array( "<i>" . $rand_id . "</i>" )) . "</b></p>\n";
		echo "<div style='text-align:left; width:60%; border-top:1px solid #999; border-bottom:1px solid #999; margin:auto; padding:10px 0 10px 0;'>\n";
		echo stripslashes(str_replace("\n", "<br />", $oiopub_set->offpay['instructions'])) . "\n";
		echo "</div>\n";
	}
	
}


//execute class
$oiopub_plugin[$oio_module] = new oiopub_offpay($oio_version, $oio_name);

?>