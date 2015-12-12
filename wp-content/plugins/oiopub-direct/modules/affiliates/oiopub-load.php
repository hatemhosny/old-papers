<?php

/*
Module: Affiliate Program v2.55
Developer: http://www.simonemery.co.uk

Module constructed for OIOpublisher Direct
http://www.oiopublisher.com

Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

//module vars
$oio_enabled = isset($oiopub_set->affiliates) ? $oiopub_set->affiliates['enabled'] : 0;
$oio_version = "2.55";
$oio_module = "affiliates";
$oio_name = __oio("Affiliates");
$oio_menu = "main";

//min plugin version
$oio_min_version = "2.55";

//check minimum version
oiopub_module_vcheck($oio_module, $oio_min_version);

//affiliates class
class oiopub_affiliates {

	//global vars
	var $version;
	
	//init
	function oiopub_affiliates($oio_version='') {
		$this->version = $oio_version;
		$this->settings();
		$this->install();
		$this->hooks();
		$this->cookie();
	}
	
	//settings
	function settings() {
		global $oiopub_set;
		//get folder name
		$dir = trim(str_replace('\\', '/', dirname(__FILE__)));
		$exp = explode('/', $dir);
		$folder = $exp[count($exp)-1];
		//misc settings
		$oiopub_set->affiliates_folder = $folder;
		$oiopub_set->affiliates_url = $oiopub_set->plugin_url . '/modules/' . $folder . '/account.php';
		$oiopub_set->dbtable_affiliates = $oiopub_set->prefix . "oiopub_affiliates";
		$oiopub_set->dbtable_affiliates_hits = $oiopub_set->prefix . "oiopub_affiliates_hits";
		$oiopub_set->dbtable_affiliates_sales = $oiopub_set->prefix . "oiopub_affiliates_sales";
	}
	
	//install
	function install() {
		global $oiopub_set;
		if(!isset($oiopub_set->affiliates) || $oiopub_set->affiliates['install'] < $this->version) {
			if(empty($oiopub_set->affiliates['install'])) {
				include_once($oiopub_set->modules_dir . '/' . $oiopub_set->affiliates_folder . '/install/install.php');
			} else {
				include_once($oiopub_set->modules_dir . '/' . $oiopub_set->affiliates_folder . '/install/upgrade.php');
			}
			$oiopub_set->affiliates['install'] = $this->version;
			oiopub_update_config('affiliates', $oiopub_set->affiliates);
		}
	}

	//uninstall
	function uninstall() {
		global $oiopub_set;
		include_once($oiopub_set->modules_dir . '/' . $oiopub_set->affiliates_folder . '/install/uninstall.php');
	}

	//add actions
	function hooks() {
		global $oiopub_hook;
		if(defined('OIOPUB_PURCHASE')) {
			$oiopub_hook->add('pre_purchase', array(&$this, 'pre_purchase'));
			$oiopub_hook->add('post_purchase', array(&$this, 'post_purchase'));
			$oiopub_hook->add('post_renew', array(&$this, 'post_renew'));
		}
		if(defined('OIOPUB_APPROVALS')) {
			$oiopub_hook->add('approvals_validate', array(&$this, 'validate_item'));
			//$oiopub_hook->add('approvals_renew', array(&$this, 'renew_item'));
			$oiopub_hook->add('approvals_reject', array(&$this, 'reject_item'));
			$oiopub_hook->add('approvals_void', array(&$this, 'void_item'));
			$oiopub_hook->add('approvals_delete', array(&$this, 'delete_item'));
		}
		if(oiopub_is_admin()) {
			if(isset($_GET['page']) && $_GET['page'] == "oiopub-affiliates.php") {
				$oiopub_hook->add('my_modules', array(&$this, 'admin_options'));
				$oiopub_hook->add('help_desk', array(&$this, 'help'));
			}
			if(isset($_REQUEST['do']) && $_REQUEST['do'] == "oiopub-remove") {
				$oiopub_hook->add('delete_modules', array(&$this, 'uninstall'));
			}
		}
		$oiopub_hook->add('approvals_history', array(&$this, 'renew_subscription'));
	}

	//affiliate cookie
	function cookie() {
		global $oiopub_db, $oiopub_set;
		//anything to process?
		if(oiopub_is_admin() || $oiopub_set->affiliates['enabled'] != 1) {
			return;
		}
		//get referal ID
		$ref_id = (int) oiopub_var('ref', 'get');
		$ref_cookie = (int) oiopub_var('oiopub_refid', 'cookie');
		//continue?
		if($ref_id > 0) {
			//format request
			$qs = strip_tags($_SERVER['QUERY_STRING']);
			$request = oiopub_clean($_SERVER['REQUEST_URI']);
			$request = str_replace("amp;", "", $request);
			$request = str_replace("?ref=" . $ref_id, "", $request);
			$request = str_replace("&ref=" . $ref_id, "", $request);
			//check database
			$check_id = $oiopub_db->GetOne("SELECT id FROM " . $oiopub_set->dbtable_affiliates . " WHERE id='$ref_id' AND status='1' LIMIT 1");
			//valid affiliate?
			if($ref_id != $ref_cookie && $ref_id == $check_id) {
				//set vars
				$time = time();
				$date = date("Y-m-d", $time);
				$ip = ip2long($_SERVER['REMOTE_ADDR']);
				//insert hit into database
				$oiopub_db->query("INSERT INTO " . $oiopub_set->dbtable_affiliates_hits . " (ref_id,time,date,ip,page) VALUES ('$ref_id','$time','$date','$ip','$request')");
				//set cookie
				setcookie('oiopub_refid', $ref_id, strtotime("+30 days"), '/');
			}
			//redirect user?
			if(!defined('OIOPUB_JS')) {
				header("Location: " . $request);
				exit();
			}
		}
	}
	
	//help
	function help() {
		global $oiopub_set;
		echo "<b>Section Covered:</b> Affiliates Module\n";
		echo "<br /><br />\n";
		echo "The affiliates module lets you reward users for helping you sell ad space.\n";
		echo "<br /><br />\n";
		echo "<b>Affiliate Registration URL:</b>\n";
		echo "<br /><br />\n";
		echo "<font color='blue'>" . $oiopub_set->affiliates_url . "</font>\n";
		echo "<br /><br />\n";
		echo "<b>Can I edit the affiliate account page?</b>\n";
		echo "<br /><br />\n";
		echo "Yes, <a href='" . $oiopub_set->plugin_url_org . "/admin.php?page=oiopub-opts.php&opt=templates' target='_parent'>click here</a>.\n";
		echo "<br /><br />\n";
		echo "<b>Making Mass Payments:</b>\n";
		echo "<br /><br />\n";
		echo "You can download the list of users who need paying to an excel spreadsheet, and use it to make a mass payment in processors such as Paypal.\n";
	}
	
	//validate item
	function validate_item($id) {
		global $oiopub_db, $oiopub_set;
		$time = time();
		$oiopub_db->query("UPDATE " . $oiopub_set->dbtable_affiliates_sales . " SET purchase_payment='1', purchase_time='$time' WHERE purchase_id='$id' AND purchase_payment='0' ORDER BY id DESC LIMIT 1");
	}
	
	//renew item
	function renew_item($id) {
		global $oiopub_set, $oiopub_db;
		$time = time();
		$item = $oiopub_db->GetRow("SELECT * FROM " . $oiopub_set->dbtable_purchases . " WHERE item_id='$id' LIMIT 1");
		$exp = explode("|", $item->affiliate_id);
		if($oiopub_set->affiliates['renew'] == 1 && $exp[0] > 0) {
			$oiopub_db->query("UPDATE " . $oiopub_set->dbtable_affiliates_sales . " SET purchase_payment='1', purchase_time='$time' WHERE purchase_id='$id' AND purchase_payment='0' ORDER BY id DESC LIMIT 1");
			if($oiopub_db->rows_affected != 1) {
				$oiopub_db->query("INSERT INTO " . $oiopub_set->dbtable_affiliates_sales . " (affiliate_id,affiliate_amount,affiliate_currency,purchase_id,purchase_type,purchase_time,purchase_payment) VALUES ('$exp[0]','$exp[1]','$item->payment_currency','$item->item_id','$item->item_channel','$time','1')");
			}
		}
	}
	
	//reject item
	function reject_item($id) {
		global $oiopub_db, $oiopub_set;
		$oiopub_db->query("UPDATE " . $oiopub_set->dbtable_affiliates_sales . " SET affiliate_paid='2' WHERE purchase_id='$id' AND affiliate_paid='0'");
	}

	//void item
	function void_item($id) {
		global $oiopub_db, $oiopub_set;
		$oiopub_db->query("UPDATE " . $oiopub_set->dbtable_affiliates_sales . " SET affiliate_paid='2' WHERE purchase_id='$id' AND affiliate_paid='0'");
	}
	
	//delete item
	function delete_item($id) {
		global $oiopub_db, $oiopub_set;
		$oiopub_db->query("DELETE FROM " . $oiopub_set->dbtable_affiliates_sales . " WHERE purchase_id='$id' AND affiliate_paid='0'");
	}

	//pre-purchase
	function pre_purchase($item) {
		global $oiopub_db, $oiopub_set;
		//check cookie
		$affiliate_id = (int) oiopub_var('oiopub_refid', 'cookie');
		//check database?
		if($oiopub_set->affiliates['attach'] == 1) {
			$res = $oiopub_db->GetOne("SELECT affiliate_id FROM " . $oiopub_set->dbtable_purchases . " WHERE adv_email='" . $item->adv_email . "' AND affiliate_id!='' ORDER BY item_id DESC LIMIT 1");
			if(strpos($res, "|") !== false) {
				$res = explode("|", $res);
				$affiliate_id = (int) $res[0];
			}
		}
		//affiliate ID found?
		if($affiliate_id > 0) {
			$aff = $oiopub_db->GetRow("SELECT level,coupon FROM " . $oiopub_set->dbtable_affiliates . " WHERE id='$affiliate_id' AND status=1");
			if($aff->level > 0) {
				if($oiopub_set->affiliates['fixed'] == 0) {
					$aff_cost = number_format($item->payment_amount * (($aff->level - $aff->coupon) / 100), 2, ".", "");
					$item->payment_amount = $item->payment_amount * ((100 - $aff->coupon) / 100);
				} else {
					$aff_cost = number_format($aff->level, 2, ".", "");
					$item->payment_amount = ($item->payment_amount - $aff->coupon);
				}
				$item->affiliate_id = $affiliate_id . "|" . $aff_cost;
			}
		}
	}
	
	//post-purchase
	function post_purchase($item) {
		global $oiopub_set, $oiopub_db;
		$exp = explode("|", $item->affiliate_id);
		if($exp[0] > 0) {
			$oiopub_db->query("INSERT INTO " . $oiopub_set->dbtable_affiliates_sales . " (affiliate_id,affiliate_amount,affiliate_currency,purchase_id,purchase_type) VALUES ('$exp[0]','$exp[1]','$item->payment_currency','$item->item_id','$item->item_channel')");
		}
	}

	//post-renew
	function post_renew($item) {
		global $oiopub_set;
		if($oiopub_set->affiliates['renew'] == 1) {
			$this->post_purchase($item);
		}
	}
	
	//renew subscription
	function renew_subscription($item) {
		global $oiopub_set, $oiopub_db;
		$time = time();
		$exp = explode("|", $item->affiliate_id);
		if($oiopub_set->affiliates['renew'] == 1 && $exp[0] > 0 && $item->subscription) {
			$oiopub_db->query("INSERT INTO " . $oiopub_set->dbtable_affiliates_sales . " (affiliate_id,affiliate_amount,affiliate_currency,purchase_id,purchase_type,purchase_time,purchase_payment) VALUES ('$exp[0]','$exp[1]','$item->payment_currency','$item->item_id','$item->item_channel','$time','1')");
		}
	}

	//admin options
	function admin_options() {
		global $oiopub_set;
		$page = oiopub_var('page', 'get');
		$type = (int) oiopub_var('type', 'get');
		$pay_array = array( 1 => "Fees to be paid", "Fees not yet matured", "Fees paid out", "Show all affiliates" );
		if($page == "oiopub-affiliates.php") {
			echo "<script language=\"javascript\">function alert1(url){var msg=confirm('Confirm Payment Made?');if(msg){window.location=url;}}</script>\n";
			echo "<h2>Affiliate Settings</h2>\n";
			echo "The affiliate module enables you to enlist your readers as salesmen for your blog ads and services. When a reader signs up as an affiliate, they get a unique URL that they can use to direct people to your blog, and so get commission on sales made via that URL.\n";
			echo "<br /><br />\n";
			echo "<table width='100%' style='background:#CCFFCC; border:1px solid #9AFF9A; padding:10px; margin-top:10px;'>\n";
			echo "<tr><td>\n";
			echo "<b>Affiliate Registration URL:</b> &nbsp;<font color='blue'>" . $oiopub_set->affiliates_url . "</font>\n";
			echo "</td></tr>\n";
			echo "</table>\n";
			echo "<br /><br />\n";
			$this->admin_config();
			echo "<br /><br />\n";
			echo "<table width=\"100%\" border=\"0\" id=\"payments\">\n";
			echo "<tr>\n";
			echo "<td align=\"left\"><h2>Affiliate details & payments</h2></td>\n";
			echo "<td align=\"right\" valign=\"bottom\" style=\"padding-right:15px;\">\n";
			echo "<form method=\"get\" action=\"" . oiopub_clean($_SERVER['REQUEST_URI']) . "#payments\">\n";
			echo "<input type=\"hidden\" name=\"page\" value=\"" . $page . "\" />\n";
			echo "<input type=\"hidden\" name=\"module\" value=\"affiliates\" />\n";
			echo "View: " . oiopub_dropmenu_kv($pay_array, "type", $type, 200) . " <input type=\"submit\" value=\"Show\" />\n";
			echo "</form>\n";
			echo "</td>\n";
			echo "</tr>\n";
			echo "</table>\n";
			echo "<br />\n";
			$this->admin_payments($type ? $type : 1);
			echo "<br /><br /><br />\n";
			echo "<h2 id=\"edit\">Edit Affiliate Details [<a href=\"admin.php?page=oiopub-affiliates.php&module=affiliates&type=4#payments\">show all</a>]</h2>\n";
			echo "Edit individual settings below, based on their Affiliate ID or email address\n";
			$this->admin_editor();
			echo "<br /><br />\n";
		}
	}

	//admin config
	function admin_config() {
		global $oiopub_set;
		if(isset($_POST['oiopub_affiliates_enabled'])) {
			$array = array();
			$array['enabled'] = intval($_POST['oiopub_affiliates_enabled']);
			$array['fixed'] = intval($_POST['oiopub_affiliates_fixed']);
			$array['level'] = intval($_POST['oiopub_affiliates_level']);
			$array['maturity'] = intval($_POST['oiopub_affiliates_maturity']);
			$array['renew'] = intval($_POST['oiopub_affiliates_renew']);
			$array['attach'] = intval($_POST['oiopub_affiliates_attach']);
			$array['terms'] = $_POST['oiopub_affiliates_terms'];
			$array['help'] = $_POST['oiopub_affiliates_help'];
			$array['aff_url'] = (!empty($_POST['oiopub_affiliates_url']) ? oiopub_clean($_POST['oiopub_affiliates_url']) : $oiopub_set->site_url . "/");
			oiopub_update_config('affiliates', $array);
			unset($array);
		}
		$this->admin_approve_payment();
		echo "<form action=\"" . oiopub_clean($_SERVER["REQUEST_URI"]) . "\" method=\"post\">\n";
		echo "<input type='hidden' name='csrf' value='" . oiopub_csrf_token() . "' />\n";
		echo "<b>Enable Affiliate Program?</b>";
		echo "<br /><br />\n";
		echo oiopub_dropmenu_kv($oiopub_set->arr_yesno, "oiopub_affiliates_enabled", $oiopub_set->affiliates['enabled']);
		echo "&nbsp;&nbsp;<i>do you wish to enable your sales affiliate program?</i>\n";
		echo "<br /><br />\n";
		echo "<b>Default Affiliate URL?</b>";
		echo "<br /><br />\n";
		echo "<input type=\"text\" name=\"oiopub_affiliates_url\" value=\"".$oiopub_set->affiliates['aff_url']."\" size=\"30\" />\n";
		echo "&nbsp;&nbsp;<i>the link users will see when viewing their account (don't include a ref ID)</i>\n";
		echo "<br /><br />\n";
		echo "<b>Affiliate Fixed Payments?</b>";
		echo "<br /><br />\n";
		echo oiopub_dropmenu_kv($oiopub_set->arr_yesno, "oiopub_affiliates_fixed", $oiopub_set->affiliates['fixed']);
		echo "&nbsp;&nbsp;<i>set to <b>no</b> to give affiliates a percentage of a sale, or to <b>yes</b> to make it a fixed sum per sale</i>\n";
		echo "<br /><br />\n";
		echo "<b>Affiliate Level:</b>";
		echo "<br /><br />\n";
		echo "<input type=\"text\" name=\"oiopub_affiliates_level\" value=\"".$oiopub_set->affiliates['level']."\" />\n";
		echo "&nbsp;&nbsp;<i>set this value to the percentage or fixed sum size you want to offer affiliates</i>\n";
		echo "<br /><br />\n";
		echo "<b>Sales Maturity time</b>";
		echo "<br /><br />\n";
		echo "<input type=\"text\" name=\"oiopub_affiliates_maturity\" value=\"".$oiopub_set->affiliates['maturity']."\" /> days\n";
		echo "&nbsp;&nbsp;<i>this value indicates the number of days before sales commission can be eligible for payment</i>\n";
		echo "<br /><br />\n";
		echo "<b>Give commission for renewals?</b>";
		echo "<br /><br />\n";
		echo oiopub_dropmenu_kv($oiopub_set->arr_yesno, "oiopub_affiliates_renew", $oiopub_set->affiliates['renew']);
		echo "&nbsp;&nbsp;<i>selecting 'yes' will credit an affiliate with commission if an expired purchase or subscription is renewed</i>\n";		
		echo "<br /><br />\n";
		echo "<b>Attach affiliate to advertiser?</b>";
		echo "<br /><br />\n";
		echo oiopub_dropmenu_kv($oiopub_set->arr_yesno, "oiopub_affiliates_attach", $oiopub_set->affiliates['attach']);
		echo "&nbsp;&nbsp;<i>selecting 'yes' will credit an affiliate with any new purchases that use the same email address</i>\n";
		echo "<br /><br />\n";
		echo "<b>Offer help to new affiliates</b>\n";
		echo "<br />\n";
		echo "<i>Any text you enter below will be displayed to affiliates when they are signed in (html allowed).</i>\n";
		echo "<br /><br />\n";
		echo "<textarea name=\"oiopub_affiliates_help\" cols=\"80\" rows=\"6\">".stripslashes($oiopub_set->affiliates['help'])."</textarea>\n";
		echo "<br /><br />\n";
		echo "<b>Affiliate Terms &amp; Conditions</b>\n";
		echo "<br />\n";
		echo "<i>Any terms you enter below will be displayed to new affiliates when they register (html allowed).</i>\n";
		echo "<br /><br />\n";
		echo "<textarea name=\"oiopub_affiliates_terms\" cols=\"80\" rows=\"6\">".stripslashes($oiopub_set->affiliates['terms'])."</textarea>\n";
		echo "<br />\n";
		echo "<div class=\"submit\"><input type=\"submit\" value=\"Update Settings\" /></div>\n";
		echo "</form>\n";
	}

	//admin payments
	function admin_payments($type=1) {
		global $oiopub_db, $oiopub_set;
		echo "<table class=\"widefat\" width=\"100%\">\n";
		echo "<thead>\n";
		echo "<tr>\n";
		echo "<th scope=\"col\">Affiliate Details</th>\n";
		echo "<th scope=\"col\">PayPal Address</th>\n";
		echo "<th scope=\"col\">Fees Total</th>\n";
		echo "<th scope=\"col\">Actions</th>\n";
		echo "</tr>\n";
		echo "</thead>\n";
		echo "<tbody id=\"the-list\">\n";
		$maturity_time = time() - ($oiopub_set->affiliates['maturity'] * 86400);
		if($type == 1) $where = "s.affiliate_paid='0' AND s.purchase_time < '$maturity_time'";
		if($type == 2) $where = "s.affiliate_paid='0' AND s.purchase_time >= '$maturity_time'";
		if($type == 3) $where = "s.affiliate_paid='1'";
		if($type == 4) $where = "1=1";
		$sql = "SELECT a.id, a.name, a.email, a.paypal, SUM(s.affiliate_amount) as total, s.affiliate_currency FROM " . $oiopub_set->dbtable_affiliates . " a LEFT JOIN " . $oiopub_set->dbtable_affiliates_sales . " s ON a.id=s.affiliate_id AND s.purchase_payment='1' WHERE " . $where . " GROUP BY a.id, s.affiliate_currency";
		$sales = $oiopub_db->GetAll($sql);
		if(!empty($sales)) {
			foreach($sales as $s) {
				if($type == 1) {
					$my_url = "admin.php?page=oiopub-affiliates.php&do=affpaid&id=" . $s->id;
					$action = "<a href=\"" . $my_url . "\" onclick=\"alert1('" . $my_url . "'); return false;\">Mark Paid</a>";
				} else {
					$action = "n/a";
				}
				echo "<tr id='payment-" . $s->id . "' $class>\n";
				echo "<td>" . $s->name . " &nbsp;(<a href='admin.php?page=oiopub-affiliates.php&type=" . $type . "&affid=" . $s->id . "#edit'>edit</a>)<br /><a href=\"mailto:" . $s->email . "\">" . $s->email . "</a></td>\n";
				echo "<td>" . $s->paypal . "</td>\n";
				echo "<td>" . ($s->total > 0 ? $s->total . " " . $s->affiliate_currency : "n/a") . "</td>\n";
				echo "<td>" . $action . "</td>\n";
				echo "</tr>\n";
				if($bgcolor == "") {
					$bgcolor = "class='alternate'";
				} else {
					$bgcolor = "";
				}
			}
		}
		echo "</tbody>\n";
		echo "</table>\n";
		if($type == 1) {
			echo "<br />\n";
			echo "<a href='" . $oiopub_set->plugin_url_org . "/export.php?do=excel&type=affiliates'>Export Payment Data for Mass Pay</a>\n";
		}
	}

	//admin editor
	function admin_editor() {
		global $oiopub_db, $oiopub_set;
		$type = oiopub_var('type', 'get');
		if(isset($_GET['affid'])) {
			if(is_numeric($_GET['affid'])) {
				$userid = intval($_GET['affid']);
				$sql_where = "id='$userid'";
			} else {
				$userid = oiopub_clean($_GET['affid']);
				$sql_where = "email='$userid'";
			}
		}
		if(isset($_POST['process']) && $_POST['process'] == 'yes') {
			$aff->name = oiopub_clean($_POST['name']);
			$aff->email = oiopub_clean($_POST['email']);
			$aff->paypal = oiopub_clean($_POST['paypal']);
			$aff->level = intval($_POST['level']);
			$aff->status = intval($_POST['status']);
			$oiopub_db->query("UPDATE " . $oiopub_set->dbtable_affiliates . " SET name='$aff->name', email='$aff->email', paypal='$aff->paypal', level='$aff->level', status='$aff->status' WHERE $sql_where");
		}
		if(empty($userid)) {
			echo "<br /><br />\n";
			echo "<form action='#edit' method='get'>\n";
			echo "<input type='hidden' name='page' value='oiopub-affiliates.php' />\n";
			echo "<input type='hidden' name='module' value='affiliates' />\n";
			echo "<input type='hidden' name='type' value='$type' />\n";
			echo "<b>ID / Email:</b> <input type='text' name='affid' />\n";
			echo "<input type='submit' value='Go' />\n";
			echo "</form>\n";
		} else {
			$aff = $oiopub_db->GetRow("SELECT id,name,email,paypal,level,status FROM " . $oiopub_set->dbtable_affiliates . " WHERE $sql_where");
			echo "<br /><br />\n";
			echo "<form action='" . oiopub_clean($_SERVER['REQUEST_URI']) . "#edit' method='post'>\n";
			echo "<input type='hidden' name='csrf' value='" . oiopub_csrf_token() . "' />\n";
			echo "<input type='hidden' name='process' value='yes' />\n";
			echo "<table width='100%' border='0' cellspacing='4' cellpadding='4'>\n";
			if($aff->id > 0) {
				echo "<tr><td width='80'><b>ID</b>:</td><td>" . $aff->id . "</td></tr>\n";
				echo "<tr><td><b>Name</b>:</td><td><input type='text' name='name' size='30' value='$aff->name' /></td></tr>\n";
				echo "<tr><td><b>Email</b>:</td><td><input type='text' name='email' size='30' value='$aff->email' /></td></tr>\n";
				echo "<tr><td><b>PayPal</b>:</td><td><input type='text' name='paypal' size='30' value='$aff->paypal' /></td></tr>\n";
				echo "<tr><td><b>Level</b>:</td><td><input type='text' name='level' size='30' value='$aff->level' /></td></tr>\n";
				echo "<tr><td><b>Status</b>:</td><td><input type='text' name='status' size='30' value='$aff->status' /></td></tr>\n";
				echo "<tr><td></td><td><input type='submit' value='Update' /></td></tr>\n";
			} else {
				echo "<tr><td colspan='2' align='center' style='color:red;'><i>No affiliate exists with that ID / email address!</i></td></tr>\n";
			}
			echo "</table>\n";
			echo "</form>\n";
		}
	}
	
	//approve payment
	function admin_approve_payment() {
		global $oiopub_db, $oiopub_set;
		if(oiopub_auth_check()) {
			if(isset($_GET['page']) && $_GET['page'] == "oiopub-affiliates.php") {
				if(isset($_GET['do']) && $_GET['do'] == "affpaid") {
					$id = intval(oiopub_var('id', 'get'));
					$maturity_time = time() - ($oiopub_set->affiliates['maturity'] * 86400);
					$oiopub_db->query("UPDATE " . $oiopub_set->dbtable_affiliates_sales . " SET affiliate_paid='1' WHERE affiliate_id='$id' AND affiliate_paid='0' AND purchase_payment='1' AND purchase_time < '$maturity_time'");
					header("Location: admin.php?page=oiopub-affiliates.php");
					exit();
				}
			}
		}
	}
	
}

//execute class
$oiopub_plugin[$oio_module] = new oiopub_affiliates($oio_version);

?>