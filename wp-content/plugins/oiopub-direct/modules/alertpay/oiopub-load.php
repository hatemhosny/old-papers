<?php

/*
Module: AlertPay Checkout v2.00
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
$oio_name = __oio("Payza");
$oio_module = "alertpay";
$oio_menu = "payment";

//min plugin version
$oio_min_version = "2.10.b1";

//check minimum version
oiopub_module_vcheck($oio_module, $oio_min_version);


//alertpay class
class oiopub_alertpay {

	//global vars
	var $name;
	var $folder;
	var $version;
	
	//init
	function oiopub_alertpay($version='', $name='') {
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
		$oiopub_set->alertpay_folder = $this->folder;
		$oiopub_set->alertpay_ipn = $oiopub_set->plugin_url . '/modules/' . $this->folder . '/alertpay-ipn.php';
		$oiopub_set->alertpay_form = $oiopub_set->plugin_url . '/modules/' . $this->folder . '/alertpay-form.php';
		$oiopub_set->alertpay_success = $oiopub_set->plugin_url . '/payment.php?do=success';
		$oiopub_set->alertpay_failed = $oiopub_set->plugin_url . '/payment.php?do=failed';
		$oiopub_set->alertpay_subscription = 1;
		//payment arrays
		if(isset($oiopub_set->alertpay)) {
			if($oiopub_set->alertpay['enable'] == 1 && $oiopub_set->alertpay['valid'] == 1) {
				$oiopub_set->arr_payment['alertpay'] = $this->name;
			}
		}
	}

	//install
	function install() {
		global $oiopub_set;
		if(!isset($oiopub_set->alertpay) || $oiopub_set->alertpay['install'] < $this->version) {
			if(empty($oiopub_set->alertpay['install'])) {
				include_once($oiopub_set->modules_dir . '/' . $this->folder . '/install/install.php');
			} else {
				include_once($oiopub_set->modules_dir . '/' . $this->folder . '/install/upgrade.php');
			}
			$oiopub_set->alertpay['install'] = $this->version;
			oiopub_update_config('alertpay', $oiopub_set->alertpay);
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
			if(isset($_GET['page']) && $_GET['page'] == 'oiopub-alertpay.php') {
				header("Location: admin.php?page=oiopub-opts.php&popup=alertpay");
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
				if(isset($_GET['popup']) && $_GET['popup'] == "alertpay") {
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
		if($oiopub_set->alertpay['valid'] == 1) {
			if($oiopub_set->alertpay['enable'] == 1) {
				echo "<font color='green'><b>Payza Payments Active</b></font> - ";
			} else {
				echo "<font color='blue'><b>Payza Payments Disabled</b></font> - ";
			}
		} else {
			echo "<font color='red'><b>Payza Setup Incomplete</b></font> - ";
		}
		echo "[<a href='admin.php?page=oiopub-opts.php&popup=alertpay'>edit settings</a>]\n";
		echo "<br /><br />\n";
	}
	
	//admin popup
	function admin_popup() {
		global $oiopub_set;
		$info = ""; $checked = "";
		if(isset($_POST['oiopub_alertpay_mail'])) {
			$enable = intval($_POST['oiopub_alertpay_enable']);
			$email = oiopub_clean($_POST['oiopub_alertpay_mail']);
			$security = oiopub_clean($_POST['oiopub_alertpay_security']);
			if(oiopub_validate_email($email) && !empty($security)) {
				$oiopub_set->alertpay['enable'] = $enable;
				$oiopub_set->alertpay['valid'] = 1;
				$oiopub_set->alertpay['mail'] = $email;
				$oiopub_set->alertpay['security'] = $security;
				oiopub_update_config('alertpay', $oiopub_set->alertpay);
				$info = "<font color='green'><i><b>Information successfully updated!</b></i></font>";
			} else {
				$oiopub_set->alertpay['valid'] = 0;
				oiopub_update_config('alertpay', $oiopub_set->alertpay);
				$info = "<font color='red'><i><b>Information not validated. Please try again!</b></i></font>";
			}
		}
		if($oiopub_set->alertpay['enable'] == 1) {
			$checked = " checked=\"checked\"";
		}
		echo "<div style='border-top:1px solid #D8D8D8; border-bottom:1px solid #D8D8D8; padding:5px 0 5px 0; margin:5px 0 20px 0;'>\n";
		if(!empty($info)) {
			echo $info . "\n";
		} else {
			echo "<i><b>Integration type:</b> fully automated.</i>\n";
			echo "<br />\n";
			echo "<i><b>Subscriptions Supported:</b> " . ($oiopub_set->alertpay_subscription == 1 ? 'yes' : 'no') . ".</i>\n";		
		}
		echo "</div>\n";
		echo "<form method='post' action='" . $oiopub_set->request_uri . "'>\n";
		echo "<input type='hidden' name='csrf' value='" . oiopub_csrf_token() . "' />\n";
		echo "<b>Enable Payza?</b>\n";
		echo "<br /><br />\n";
		echo "<input type=\"checkbox\" name=\"oiopub_alertpay_enable\" value=\"1\"" . $checked . " />\n";
		echo "&nbsp;&nbsp;<i>enabling this will option will let advertisers pay using <a href='http://www.payza.com' target='_blank'>Payza</a></i>\n";
		echo "<br /><br />\n";
		echo "<b>Payza Email Address:</b>\n";
		echo "<br /><br />\n";
		echo "<input type=\"text\" name=\"oiopub_alertpay_mail\" value=\"" . $oiopub_set->alertpay['mail'] . "\" size=\"30\" />\n";
		echo "&nbsp;&nbsp;<i>email account that payment will be sent to</i>\n";
		echo "<br /><br />\n";
		echo "<b>Payza Security Code:</b>\n";
		echo "<br /><br />\n";
		echo "<input type=\"text\" name=\"oiopub_alertpay_security\" value=\"" . $oiopub_set->alertpay['security'] . "\" size=\"30\" />\n";
		echo "&nbsp;&nbsp;<i>get this code from your <a href='http://www.payza.com' target='_blank'>Payza account</a></i>\n";
		echo "<br /><br />\n";
		echo "<b>Finally, set your <a href='https://dev.payza.com/resources/references/alert-url' target='_blank'>Alert Url</a> in your Payza account to:</b>\n";
		echo "<br /><br />\n";
		echo ">> " . $oiopub_set->alertpay_ipn . "\n";
		echo "<br /><br />\n";
		echo "<input type='submit' value='Update Settings' />\n";
		echo "</form>\n";
	}
	
	//alertpay form
	function form($rand_id) {
		global $oiopub_set;
		echo '<form id="alertpay" action="' . $oiopub_set->alertpay_form . '" method="post">';
		echo '<input type="hidden" name="rand" value="' . $rand_id . '" />';
		echo '<input type="image" src="https://www.payza.com/images/payza-buy-now.png" border="0" name="submit" />';
		echo '</form>';
	}
	
}


//execute class
$oiopub_plugin[$oio_module] = new oiopub_alertpay($oio_version, $oio_name);

?>