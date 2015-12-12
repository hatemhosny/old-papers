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
$oio_name = __oio("2Checkout");
$oio_module = "2checkout";
$oio_menu = "payment";

//min plugin version
$oio_min_version = "2.10.b1";

//check minimum version
oiopub_module_vcheck($oio_module, $oio_min_version);


//twocheckout class
class oiopub_2checkout {

	//global vars
	var $name;
	var $folder;
	var $version;
	
	//init
	function oiopub_2checkout($version='', $name='') {
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
		$oiopub_set->twocheckout_folder = $this->folder;
		$oiopub_set->twocheckout_ipn = $oiopub_set->plugin_url . '/modules/' . $this->folder . '/2checkout-ipn.php';
		$oiopub_set->twocheckout_form = $oiopub_set->plugin_url . '/modules/' . $this->folder . '/2checkout-form.php';
		$oiopub_set->twocheckout_success = $oiopub_set->plugin_url . '/payment.php?do=success';
		$oiopub_set->twocheckout_failed = $oiopub_set->plugin_url . '/payment.php?do=failed';
		$oiopub_set->twocheckout_subscription = 0;
		//payment arrays
		if(isset($oiopub_set->twocheckout)) {
			if($oiopub_set->twocheckout['enable'] == 1 && $oiopub_set->twocheckout['valid'] == 1) {
				$oiopub_set->arr_payment['2checkout'] = $this->name;
			}
		}
	}

	//install
	function install() {
		global $oiopub_set;
		if(!isset($oiopub_set->twocheckout) || $oiopub_set->twocheckout['install'] < $this->version) {
			if(empty($oiopub_set->twocheckout['install'])) {
				include_once($oiopub_set->modules_dir . '/' . $this->folder . '/install/install.php');
			} else {
				include_once($oiopub_set->modules_dir . '/' . $this->folder . '/install/upgrade.php');
			}
			$oiopub_set->twocheckout['install'] = $this->version;
			oiopub_update_config('twocheckout', $oiopub_set->twocheckout);
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
			if(isset($_GET['page']) && $_GET['page'] == 'oiopub-2checkout.php') {
				header("Location: admin.php?page=oiopub-opts.php&popup=2checkout");
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
				if(isset($_GET['popup']) && $_GET['popup'] == "2checkout") {
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
		if($oiopub_set->twocheckout['valid'] == 1) {
			if($oiopub_set->twocheckout['enable'] == 1) {
				echo "<font color='green'><b>2Checkout Payments Active</b></font> - ";
			} else {
				echo "<font color='blue'><b>2Checkout Payments Disabled</b></font> - ";
			}
		} else {
			echo "<font color='red'><b>2Checkout Setup Incomplete</b></font> - ";
		}
		echo "[<a href='admin.php?page=oiopub-opts.php&popup=2checkout'>edit settings</a>]\n";
		echo "<br /><br />\n";
	}
	
	//admin popup
	function admin_popup() {
		global $oiopub_set;
		$info = ""; $checked = "";
		if(isset($_POST['oiopub_2checkout_seller_id'])) {
			$enable = intval($_POST['oiopub_2checkout_enable']);
			$seller_id = oiopub_clean($_POST['oiopub_2checkout_seller_id']);
			$secret_key = oiopub_clean($_POST['oiopub_2checkout_secret']);
			if(!empty($seller_id)) {
				$oiopub_set->twocheckout['enable'] = $enable;
				$oiopub_set->twocheckout['valid'] = 1;
				$oiopub_set->twocheckout['seller_id'] = $seller_id;
				$oiopub_set->twocheckout['secret_key'] = $secret_key;
				oiopub_update_config('twocheckout', $oiopub_set->twocheckout);
				
				$info = "<font color='green'><i><b>Information successfully updated!</b></i></font>";
			} else {
				$oiopub_set->twocheckout['valid'] = 0;
				oiopub_update_config('twocheckout', $oiopub_set->twocheckout);
				$info = "<font color='red'><i><b>Information not validated. Please try again!</b></i></font>";
			}
		}
		if($oiopub_set->twocheckout['enable'] == 1) {
			$checked = " checked=\"checked\"";
		}
		echo "<div style='border-top:1px solid #D8D8D8; border-bottom:1px solid #D8D8D8; padding:5px 0 5px 0; margin:5px 0 20px 0;'>\n";
		if(!empty($info)) {
			echo $info . "\n";
		} else {
			echo "<i><b>Integration type:</b> fully automated.</i>\n";
			echo "<br />\n";
			echo "<i><b>Subscriptions Supported:</b> " . ($oiopub_set->twocheckout_subscription == 1 ? 'yes' : 'no') . ".</i>\n";
		}
		echo "</div>\n";
		echo "<form method='post' action='" . $oiopub_set->request_uri . "'>\n";
		echo "<input type='hidden' name='csrf' value='" . oiopub_csrf_token() . "' />\n";
		echo "<b>Enable 2Checkout?</b>\n";
		echo "<br /><br />\n";
		echo "<input type=\"checkbox\" name=\"oiopub_2checkout_enable\" value=\"1\"" . $checked . " />\n";
		echo "&nbsp;&nbsp;<i>enabling this will option will let advertisers pay using <a href='http://www.2checkout.com' target='_blank'>2checkout</a></i>\n";
		echo "<br /><br />\n";
		echo "<b>Account number:</b>\n";
		echo "<br /><br />\n";
		echo "<input type=\"text\" name=\"oiopub_2checkout_seller_id\" value=\"" . $oiopub_set->twocheckout['seller_id'] . "\" size=\"30\" />\n";
		echo "&nbsp;&nbsp;<i>your 2checkout <a href='https://www.2checkout.com/va/acct/detail_company_info' target='_blank'>find number</a></i>\n";
		echo "<br /><br />\n";
		echo "<b>Secret word:</b>\n";
		echo "<br /><br />\n";
		echo "<input type=\"text\" name=\"oiopub_2checkout_secret\" value=\"" . $oiopub_set->twocheckout['secret_key'] . "\" size=\"30\" />\n";
		echo "&nbsp;&nbsp;<i>your 2checkout <a href='https://www.2checkout.com/va/acct/detail_company_info' target='_blank'>find secret</a></i>\n";
		echo "<br /><br />\n";
		echo "<input type='submit' value='Update Settings' />\n";
		echo "</form>\n";
	}
	
	//paypal form
	function form($rand_id) {
		global $oiopub_set;
		echo '<form id="twocheckout" action="' . $oiopub_set->twocheckout_form . '" method="post">';
		echo '<input type="hidden" name="rand" value="' . $rand_id . '" />';
		echo '<input type="image" name="Checkout" alt="Checkout" src="http://www.2checkout.com/images/2cocc03.gif" height="54"  width="182" />';
		echo '</form>';
	}
	
}


//execute class
$oiopub_plugin[$oio_module] = new oiopub_2checkout($oio_version, $oio_name);

?>