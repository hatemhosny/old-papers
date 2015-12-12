<?php

/*
Module: PayPal Checkout v2.03
Developer: http://www.simonemery.co.uk

Module constructed for OIOpublisher Direct
http://www.oiopublisher.com

Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();


//module vars
$oio_enabled = 1;
$oio_version = "2.04";
$oio_name = __oio("PayPal");
$oio_module = "paypal";
$oio_menu = "payment";

//min plugin version
$oio_min_version = "2.10.b1";

//check minimum version
oiopub_module_vcheck($oio_module, $oio_min_version);


//paypal class
class oiopub_paypal {

	//global vars
	var $name;
	var $folder;
	var $version;
	
	//init
	function oiopub_paypal($version='', $name='') {
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
		$oiopub_set->paypal_folder = $this->folder;
		$oiopub_set->paypal_ipn = $oiopub_set->plugin_url . '/modules/' . $this->folder . '/paypal-ipn.php';
		$oiopub_set->paypal_form = $oiopub_set->plugin_url . '/modules/' . $this->folder . '/paypal-form.php';
		$oiopub_set->paypal_success = $oiopub_set->plugin_url . '/payment.php?do=success';
		$oiopub_set->paypal_failed = $oiopub_set->plugin_url . '/payment.php?do=failed';
		$oiopub_set->paypal_subscription = 1;
		//payment arrays
		if(isset($oiopub_set->paypal)) {
			if($oiopub_set->paypal['enable'] == 1 && $oiopub_set->paypal['valid'] == 1) {
				$oiopub_set->arr_payment['paypal'] = $this->name;
			}
		}
	}

	//install
	function install() {
		global $oiopub_set;
		if(!isset($oiopub_set->paypal) || $oiopub_set->paypal['install'] < $this->version) {
			if(empty($oiopub_set->paypal['install'])) {
				include_once($oiopub_set->modules_dir . '/' . $this->folder . '/install/install.php');
			} else {
				include_once($oiopub_set->modules_dir . '/' . $this->folder . '/install/upgrade.php');
			}
			$oiopub_set->paypal['install'] = $this->version;
			oiopub_update_config('paypal', $oiopub_set->paypal);
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
			if(isset($_GET['page']) && $_GET['page'] == 'oiopub-paypal.php') {
				header("Location: admin.php?page=oiopub-opts.php&popup=paypal");
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
				if(isset($_GET['popup']) && $_GET['popup'] == "paypal") {
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
		if($oiopub_set->paypal['valid'] == 1) {
			if($oiopub_set->paypal['enable'] == 1) {
				echo "<font color='green'><b>PayPal Payments Active</b></font> - ";
			} else {
				echo "<font color='blue'><b>PayPal Payments Disabled</b></font> - ";
			}
		} else {
			echo "<font color='red'><b>PayPal Setup Incomplete</b></font> - ";
		}
		echo "[<a href='admin.php?page=oiopub-opts.php&popup=paypal'>edit settings</a>]\n";
		echo "<br /><br />\n";
	}
	
	//admin popup
	function admin_popup() {
		global $oiopub_set;
		$info = ""; $checked = "";
		if(isset($_POST['oiopub_paypal_mail'])) {
			$enable = intval($_POST['oiopub_paypal_enable']);
			$email = oiopub_clean($_POST['oiopub_paypal_mail']);
			$page_style = oiopub_clean($_POST['oiopub_paypal_page_style']);
			if(oiopub_validate_email($email)) {
				$oiopub_set->paypal['enable'] = $enable;
				$oiopub_set->paypal['valid'] = 1;
				$oiopub_set->paypal['mail'] = $email;
				$oiopub_set->paypal['page_style'] = $page_style;
				oiopub_update_config('paypal', $oiopub_set->paypal);
				$info = "<font color='green'><i><b>Information successfully updated!</b></i></font>";
			} else {
				$oiopub_set->paypal['valid'] = 0;
				oiopub_update_config('paypal', $oiopub_set->paypal);
				$info = "<font color='red'><i><b>Information not validated. Please try again!</b></i></font>";
			}
		}
		if($oiopub_set->paypal['enable'] == 1) {
			$checked = " checked=\"checked\"";
		}
		echo "<div style='border-top:1px solid #D8D8D8; border-bottom:1px solid #D8D8D8; padding:5px 0 5px 0; margin:5px 0 20px 0;'>\n";
		if(!empty($info)) {
			echo $info . "\n";
		} else {
			echo "<i><b>Integration type:</b> fully automated.</i>\n";
			echo "<br />\n";
			echo "<i><b>Subscriptions Supported:</b> " . ($oiopub_set->paypal_subscription == 1 ? 'yes' : 'no') . ".</i>\n";		
		}
		echo "</div>\n";
		echo "<form method='post' action='" . $oiopub_set->request_uri . "'>\n";
		echo "<input type='hidden' name='csrf' value='" . oiopub_csrf_token() . "' />\n";
		echo "<b>Enable PayPal?</b>\n";
		echo "<br /><br />\n";
		echo "<input type=\"checkbox\" name=\"oiopub_paypal_enable\" value=\"1\"" . $checked . " />\n";
		echo "&nbsp;&nbsp;<i>enabling this will option will let advertisers pay using <a href='http://www.paypal.com' target='_blank'>paypal</a></i>\n";
		echo "<br /><br />\n";
		echo "<b>PayPal Email Address:</b>\n";
		echo "<br /><br />\n";
		echo "<input type=\"text\" name=\"oiopub_paypal_mail\" value=\"" . $oiopub_set->paypal['mail'] . "\" size=\"30\" />\n";
		echo "&nbsp;&nbsp;<i>email account that payment will be sent to</i>\n";
		echo "<br /><br />\n";
		echo "<b>Custom Page Style:</b>\n";
		echo "<br /><br />\n";
		echo "<input type=\"text\" name=\"oiopub_paypal_page_style\" value=\"" . $oiopub_set->paypal['page_style'] . "\" size=\"30\" />\n";
		echo "&nbsp;&nbsp;<i><a href='https://www.paypal.com/cgi-bin/webscr?cmd=p/mer/cowp_summary-outside' target='_blank'>click here</a> for more details on custom paypal pages</i>\n";
		echo "<br /><br />\n";
		echo "<input type='submit' value='Update Settings' />\n";
		echo "</form>\n";
	}
	
	//paypal form
	function form($rand_id) {
		global $oiopub_set;
		echo '<form id="paypal" name="paypal" action="' . $oiopub_set->paypal_form . '" method="post">';
		echo '<input type="hidden" name="rand" value="' . $rand_id . '" />';
		echo '<input type="image" src="https://www.paypal.com/en_US/i/bnr/horizontal_solution_PPeCheck.gif" border="0" name="submit" />';
		echo '<p><a href="javascript://" onClick="document.paypal.submit();">' . __oio("Click here to make payment") . '</a></p>';
		echo '</form>';
	}
	
}


//execute class
$oiopub_plugin[$oio_module] = new oiopub_paypal($oio_version, $oio_name);

?>