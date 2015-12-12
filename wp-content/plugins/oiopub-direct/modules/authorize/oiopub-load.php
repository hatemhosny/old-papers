<?php

/*
Module: Google Checkout v2.00
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
$oio_name = __oio("Authorize.net");
$oio_module = "authorize";
$oio_menu = "payment";

//min plugin version
$oio_min_version = "2.10.b1";

//check minimum version
oiopub_module_vcheck($oio_module, $oio_min_version);


//authorize.net class
class oiopub_authorize {

	//global vars
	var $name;
	var $folder;
	var $version;
	
	//init
	function oiopub_authorize($version='', $name='') {
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
		$oiopub_set->authorize_folder = $this->folder;
		$oiopub_set->authorize_ipn = $oiopub_set->plugin_url . '/modules/' . $this->folder . '/authorize-ipn.php';
		$oiopub_set->authorize_form = $oiopub_set->plugin_url . '/modules/' . $this->folder . '/authorize-form.php';
		$oiopub_set->authorize_success = $oiopub_set->plugin_url . '/payment.php?do=success';
		$oiopub_set->authorize_failed = $oiopub_set->plugin_url . '/payment.php?do=failed';
		$oiopub_set->authorize_subscription = 0;
		//payment arrays
		if(isset($oiopub_set->authorize)) {
			if($oiopub_set->authorize['enable'] == 1 && $oiopub_set->authorize['valid'] == 1 && $oiopub_set->general_set['currency'] == "USD") {
				$oiopub_set->arr_payment['authorize'] = $this->name;
			}
		}
	}

	//install
	function install() {
		global $oiopub_set;
		if(!isset($oiopub_set->authorize) || $oiopub_set->authorize['install'] < $this->version) {
			if(empty($oiopub_set->authorize['install'])) {
				include_once($oiopub_set->modules_dir . '/' . $this->folder . '/install/install.php');
			} else {
				include_once($oiopub_set->modules_dir . '/' . $this->folder . '/install/upgrade.php');
			}
			$oiopub_set->authorize['install'] = $this->version;
			oiopub_update_config('authorize', $oiopub_set->authorize);
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
			if(isset($_GET['page']) && $_GET['page'] == 'oiopub-authorize.php') {
				header("Location: admin.php?page=oiopub-opts.php&popup=authorize");
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
				if(isset($_GET['popup']) && $_GET['popup'] == "authorize") {
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
		if($oiopub_set->authorize['valid'] == 1) {
			if($oiopub_set->authorize['enable'] == 1) {
				echo "<font color='green'><b>Authorize.net Payments Active</b></font> - ";
			} else {
				echo "<font color='blue'><b>Authorize.net Payments Disabled</b></font> - ";
			}
		} else {
			echo "<font color='red'><b>Authorize.net Setup Incomplete</b></font> - ";
		}
		echo "[<a href='admin.php?page=oiopub-opts.php&popup=authorize'>edit settings</a>]\n";
		echo "<br /><br />\n";
	}
	
	//admin popup
	function admin_popup() {
		global $oiopub_set;
		//USD only
		if($oiopub_set->general_set['currency'] != "USD") {
			echo "<div style='border-top:1px solid #D8D8D8; border-bottom:1px solid #D8D8D8; padding:5px 0 5px 0; margin:5px 0 30px 0;'>\n";
			echo "<a href='http://www.authorize.net' target='_blank'>Authorize.net</a> only supports transactions in USD. To use this payment module you will need to switch the OIOpublisher currency setting to USD.\n";
			echo "</div>\n";
			return;
		}
		$info = ""; $checked = "";
		if(isset($_POST['oiopub_authorize_login_id'])) {
			$enable = intval($_POST['oiopub_authorize_enable']);
			$login_id = oiopub_clean($_POST['oiopub_authorize_login_id']);
			$transaction_key = oiopub_clean($_POST['oiopub_authorize_transaction_key']);
			$md5_hash = oiopub_clean($_POST['oiopub_authorize_md5_hash']);
			if(!empty($login_id)) {
				$oiopub_set->authorize['enable'] = $enable;
				$oiopub_set->authorize['valid'] = 1;
				$oiopub_set->authorize['login_id'] = $login_id;
				$oiopub_set->authorize['transaction_key'] = $transaction_key;
				$oiopub_set->authorize['md5_hash'] = $md5_hash;
				oiopub_update_config('authorize', $oiopub_set->authorize);
				$info = "<font color='green'><i><b>Information successfully updated!</b></i></font>";
			} else {
				$oiopub_set->authorize['valid'] = 0;
				oiopub_update_config('authorize', $oiopub_set->authorize);
				$info = "<font color='red'><i><b>Information not validated. Please try again!</b></i></font>";
			}
		}
		if($oiopub_set->authorize['enable'] == 1) {
			$checked = " checked=\"checked\"";
		}
		echo "<div style='border-top:1px solid #D8D8D8; border-bottom:1px solid #D8D8D8; padding:5px 0 5px 0; margin:5px 0 20px 0;'>\n";
		if(!empty($info)) {
			echo $info . "\n";
		} else {
			echo "<i><b>Integration type:</b> fully automated.</i>\n";
			echo "<br />\n";
			echo "<i><b>Subscriptions Supported:</b> " . ($oiopub_set->authorize_subscription == 1 ? 'yes' : 'no') . ".</i>\n";	
		}
		echo "</div>\n";
		echo "<form method='post' action='" . $oiopub_set->request_uri . "'>\n";
		echo "<input type='hidden' name='csrf' value='" . oiopub_csrf_token() . "' />\n";
		echo "<b>Enable Authorize.net?</b>\n";
		echo "<br /><br />\n";
		echo "<input type=\"checkbox\" name=\"oiopub_authorize_enable\" value=\"1\"" . $checked . " />\n";
		echo "&nbsp;&nbsp;<i>enabling this will option will let advertisers pay using <a href='http://www.authorize.net' target='_blank'>authorize.net</a></i>\n";
		echo "<br /><br />\n";
		echo "<b>API Login ID:</b>\n";
		echo "<br /><br />\n";
		echo "<input type=\"text\" name=\"oiopub_authorize_login_id\" value=\"" . $oiopub_set->authorize['login_id'] . "\" size=\"30\" />\n";
		echo "&nbsp;&nbsp;<i>your authorize.net Merchant <a href='https://secure.authorize.net' target='_blank'>API Login ID</a></i>\n";
		echo "<br /><br />\n";
		echo "<b>Transaction Key:</b>\n";
		echo "<br /><br />\n";
		echo "<input type=\"text\" name=\"oiopub_authorize_transaction_key\" value=\"" . $oiopub_set->authorize['transaction_key'] . "\" size=\"30\" />\n";
		echo "&nbsp;&nbsp;<i>your authorize.net Merchant <a href='https://secure.authorize.net' target='_blank'>Transaction Key</a></i>\n";
		echo "<br /><br />\n";
		echo "<b>MD5 Hash Value:</b>\n";
		echo "<br /><br />\n";
		echo "<input type=\"text\" name=\"oiopub_authorize_md5_hash\" value=\"" . $oiopub_set->authorize['md5_hash'] . "\" size=\"30\" />\n";
		echo "&nbsp;&nbsp;<i>this optional field provides extra security (see your <a href='https://secure.authorize.net' target='_blank'>merchant account</a>)</i>\n";
		echo "<br /><br />\n";
		echo "<input type='submit' value='Update Settings' />\n";
		echo "</form>\n";
	}
	
	//paypal form
	function form($rand_id) {
		global $oiopub_set;
		echo '<form id="authorize" action="' . $oiopub_set->authorize_form . '" method="post">';
		echo '<input type="hidden" name="rand" value="' . $rand_id . '" />';
		echo '<input type="image" name="Checkout" alt="Checkout" src="http://www.authorize.net/resources/images/merchants/products/buy_now_blue.gif" height="38"  width="135" />';
		echo '</form>';
	}
	
}


//execute class
$oiopub_plugin[$oio_module] = new oiopub_authorize($oio_version, $oio_name);

?>