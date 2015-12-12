<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

//init
include_once('../../index.php');

if($oiopub_set->enabled != 1) {
	die("Plugin Currently Offline");
}

if($oiopub_set->affiliates['enabled'] != 1) {
	die("Affiliate Program not enabled!");
}

//start session
oiopub_session_start();

//csrf token
//oiopub_csrf_token();

//session vars
$affiliate_id = intval($_SESSION['oiopub_aff']['id']);
$affiliate_status = intval($_SESSION['oiopub_aff']['status']);

//account active?
if($affiliate_id > 0 && $affiliate_status == 0) {
	die("This Affiliate Account has been suspended");
}

//clear vars
$info = '';
$error = false;
$ad_error = '';
$aff_url = '';
$discount_type = '';
$discount_text = '';

//sales vars
$total_hits = 0;
$total_sales = 0;
$verified_commission = 0;
$unverified_commission = 0;
$paid_commission = 0;
$fraud_commission = 0;

//main account
if($_GET['do'] == '') {
	//login
	if(isset($_POST['process']) && $_POST['process'] == "login") {
		$email = strtolower(oiopub_clean($_POST['email']));
		$password = oiopub_clean($_POST['password']);
		$salt = $oiopub_db->GetOne("SELECT salt FROM " . $oiopub_set->dbtable_affiliates . " WHERE LOWER(email)='$email'");
		$md5 = md5($salt . md5($password));
		$aff = $oiopub_db->GetRow("SELECT id,status FROM " . $oiopub_set->dbtable_affiliates . " WHERE LOWER(email)='$email' AND password='$md5'");
		$affiliate_id = intval($aff->id);
		$affiliate_status = intval($aff->status);
		if($affiliate_id > 0) {
			$_SESSION['oiopub_aff'] = array();
			$_SESSION['oiopub_aff']['id'] = $affiliate_id;
			$_SESSION['oiopub_aff']['status'] = $affiliate_status;
			header("Location: account.php");
			exit();
		} else {
			$error = true;
			$info .= "<li class=\"error\">" . __oio("Unable to validate login details") . "</li>";
		}
	}
	//register
	if(isset($_POST['process']) && $_POST['process'] == "register") {
		$level = $oiopub_set->affiliates['level'];
		$name = oiopub_clean($_POST['name']);
		$email = strtolower(oiopub_clean($_POST['email']));
		$paypal = oiopub_clean($_POST['paypal']);
		$password = oiopub_clean($_POST['password']);
		$salt = oiopub_rand(3);
		$md5 = md5($salt . md5($password));
		$security = md5(md5($_POST['security']) . $oiopub_set->hash);
		if(strlen($name) < 4) {
			$error = true;
			$info .= "<li class=\"error\">" . __oio("Please enter a valid name") . "</li>";
		}
		if(!oiopub_validate_email($email)) {
			$error = true;
			$info .= "<li class=\"error\">" . __oio("Email address provided not valid") . "</li>";
		}
		$check_email = $oiopub_db->GetOne("SELECT id FROM " . $oiopub_set->dbtable_affiliates . " WHERE LOWER(email)='$email'");
		if(!empty($check_email)) {
			$error = true;
			$info .= "<li class=\"error\">" . __oio("Email address provided already in use") . "</li>";
		}
		if(!oiopub_validate_email($paypal)) {
			$error = true;
			$info .= "<li class=\"error\">" . __oio("PayPal address provided not valid") . "</li>";
		}
		$check_paypal = $oiopub_db->GetOne("SELECT id FROM " . $oiopub_set->dbtable_affiliates . " WHERE paypal='$paypal'");
		if(!empty($check_paypal)) {
			$error = true;
			$info .= "<li class=\"error\">" . __oio("PayPal address provided already in use") . "</li>";
		}
		if(strlen($password) < 6) {
			$error = true;
			$info .= "<li class=\"error\">" . __oio("Password must be at least %s characters long", array( '6' )) . "</li>";
		}
		if($oiopub_set->general_set['security_question'] == 1) {
			if($security != $_SESSION['next'] || empty($security)) {		
				$error = true;
				$info .= "<li class=\"error\">" . __oio("Security question answer incorrect") . "</li>";
			}
		}
		if(!$error) {
			$oiopub_db->query("INSERT INTO " . $oiopub_set->dbtable_affiliates . " (level,name,email,password,salt,paypal) VALUES ('$level','$name','$email','$md5','$salt','$paypal')");
			$affiliate_id = intval($oiopub_db->insert_id);
			if($affiliate_id > 0) {
				//log affiliate in
				$_SESSION['oiopub_aff'] = array();
				$_SESSION['oiopub_aff']['id'] = $affiliate_id;
				$_SESSION['oiopub_aff']['status'] = 1;
				//affiliate email
				$subject = __oio("%s Affiliate Program - thank you for registering", array( $oiopub_set->site_name ));
				$message  = __oio("Dear") . " " . $name . ",\n\n";
				$message .= __oio("Thank you for registering for the %s affiliate program.", array( $oiopub_set->site_name )) . " ";
				$message .= __oio("Your login details can be found below") . ":\n\n";
				$message .= __oio("Email") . ": " . $email . "\n";
				$message .= __oio("Password") . ": " . $password . "\n\n";
				$message .= ">> " . $oiopub_set->affiliates_url . "\n\n";
				$message .= __oio("Thanks") . ",\n";
				$message .= $oiopub_set->site_name;
				oiopub_mail_client($email, $subject, $message);
				//admin email
				$subject = $oiopub_set->site_name . " Affiliate Program - new affiliate registered";
				$message = "A new affiliate has registered for your affiliate program:\n\nName: " . $name . "\nEmail: " . $email;
				oiopub_mail_client($oiopub_set->admin_mail, $subject, $message);
			}
			//redirect user
			header("Location: account.php");
			exit();
		}
	}
	//logged in
	if($affiliate_id > 0) {
		$aff = $oiopub_db->GetRow("SELECT * FROM " . $oiopub_set->dbtable_affiliates . " WHERE id='$affiliate_id'");
		$total_hits = $oiopub_db->GetOne("SELECT COUNT(*) FROM " . $oiopub_set->dbtable_affiliates_hits . " WHERE ref_id='$affiliate_id'");
		$maturity_time = time() - ($oiopub_set->affiliates['maturity'] * 86400);
		$sales = $oiopub_db->GetAll("SELECT * FROM " . $oiopub_set->dbtable_affiliates_sales . " WHERE affiliate_id='$affiliate_id' AND purchase_payment='1'");
		if(!empty($sales)) {
			foreach($sales as $s) {
				if($s->purchase_time > 0) {
					if($s->purchase_time < $maturity_time && $s->affiliate_paid == 0) {
						$verified_commission += $s->affiliate_amount;
						$total_sales++;
					} elseif($s->purchase_time >= $maturity_time && $s->affiliate_paid == 0) {
						$unverified_commission += $s->affiliate_amount;
						$total_sales++;
					} elseif($s->affiliate_paid == 1) {
						$paid_commission += $s->affiliate_amount;
						$total_sales++;
					} elseif($s->affiliate_paid == 2) {
						$fraud_commission += $s->affiliate_amount;
					}
				}
			}
		}
		//conversion ratio
		$conversion_ratio = (!empty($total_hits) ? ($total_sales / $total_hits) * 100 : '0.00');
		//update details
		if(isset($_POST['process']) && $_POST['process'] == "update_settings") {
			$email = strtolower(oiopub_clean($_POST['email']));
			$paypal = oiopub_clean($_POST['paypal']);
			$password = oiopub_clean($_POST['password']);
			if(!oiopub_validate_email($email)) {
				$error = true;
				$info .= "<li class=\"error\">" . __oio("Email address provided not valid") . "</li>";
			}
			$check_email = $oiopub_db->GetOne("SELECT id FROM " . $oiopub_set->dbtable_affiliates . " WHERE LOWER(email)='$email' AND id!='$affiliate_id'");
			if(!empty($check_email)) {
				$error = true;
				$info .= "<li class=\"error\">" . __oio("Email address provided already in use") . "</li>";
			}
			if(!oiopub_validate_email($paypal)) {
				$error = true;
				$info .= "<li class=\"error\">" . __oio("PayPal address provided not valid") . "</li>";
			}
			$check_paypal = $oiopub_db->GetOne("SELECT id FROM " . $oiopub_set->dbtable_affiliates . " WHERE paypal='$paypal' AND id!='$affiliate_id'");
			if(!empty($check_paypal)) {
				$error = true;
				$info .= "<li class=\"error\">" . __oio("PayPal address provided already in use") . "</li>";
			}
			if(!empty($password) && strlen($password) < 6) {
				$error = true;
				$info .= "<li class=\"error\">" . __oio("Your new password must be at least %s characters long", array( '6' )) . "</li>";
			}
			if(!$error) {
				$oiopub_db->query("UPDATE " . $oiopub_set->dbtable_affiliates . " SET email='$email', paypal='$paypal' WHERE id='$affiliate_id'");
				if(!empty($password)) {
					$salt = oiopub_rand(3);
					$md5 = md5($salt . md5($password));
					$oiopub_db->query("UPDATE " . $oiopub_set->dbtable_affiliates . " SET password='$md5', salt='$salt' WHERE id='$affiliate_id'");
				}
				header("Location: account.php?updated");
				exit();
			}
		}
		//coupon code
		if(isset($_POST['process']) && $_POST['process'] == "update_discount") {
			$discount = intval($_POST['coupon']);
			if($discount > $aff->level) {
				$discount = $aff->level;
			}
			if($discount < 0) {
				$discount = 0;
			}
			$oiopub_db->query("UPDATE " . $oiopub_set->dbtable_affiliates . " SET coupon='$discount' WHERE id='$affiliate_id'");
			header("Location: account.php");
			exit();
		}
		//get aff url
		$aff_url = str_replace("http://", "", $oiopub_set->affiliates['aff_url']);
		$parse = parse_url($aff_url);
		$aff_url = (empty($parse['query']) ? $aff_url . "?ref=" . $affiliate_id : $aff_url . "&ref=" . $affiliate_id);
		//discount level
		$aff_level = $aff->level - $aff->coupon;
		if($oiopub_set->affiliates['fixed'] == 0) {
			$discount_type = "%";
			$discount_text = $aff_level . " " . $discount_type . " " . __oio("of each purchase") . "\n";
		} else {
			$discount_type = $oiopub_set->general_set['currency'];
			$discount_text = $aff_level . " " . $discount_type . " " . __oio("of each purchase") . "\n";
		}
	}
}

//logout
if($_GET['do'] == "logout") {
	unset($_SESSION['oiopub_aff']);
	@session_destroy();
	header("Location: account.php");
	exit();
}

//lost password
if($_GET['do'] == 'lost') {
	if(isset($_POST['process']) && $_POST['process'] == "lost_password") {
		$email = strtolower(oiopub_clean($_POST['email']));
		$security = md5(md5($_POST['security']) . $oiopub_set->hash);
		$check_email = $oiopub_db->GetOne("SELECT name FROM " . $oiopub_set->dbtable_affiliates . " WHERE LOWER(email)='$email'");
		if(empty($check_email)) {
			$error = true;
			$info .= "<li class=\"error\">" . __oio("Email address not on record") . "</li>";
		}
		if($oiopub_set->general_set['security_question'] == 1) {
			if($security != $_SESSION['next'] || empty($security)) {		
				$error = true;
				$info .= "<li class=\"error\">" . __oio("Security Question Answer Incorrect") . "</li>";
			}
		}
		if(!$error) {
			$password = oiopub_rand(8);
			$salt = oiopub_rand(3);
			$md5 = md5($salt . md5($password));
			$oiopub_db->query("UPDATE " . $oiopub_set->dbtable_affiliates . " SET password='$md5', salt='$salt' WHERE LOWER(email)='$email'");
			$subject = __oio("%s Affiliate Program - password reset", array( $oiopub_set->site_name ));
			$message  = __oio("Dear") . " " . $check_email . ",\n\n";
			$message .= __oio("A request to reset your password for the %s affiliate program was recently made." , array( $oiopub_set->site_name )) . " ";
			$message .= __oio("Your login details can be found below") . ":\n\n";
			$message .= __oio("Email") . ": " . $email . "\n";
			$message .= __oio("Password") . ": " . $password . "\n\n";
			$message .= ">> " . $oiopub_set->affiliates_url . "\n\n";
			$message .= __oio("Thanks") . ",\n";
			$message .= $oiopub_set->site_name;
			oiopub_mail_client($email, $subject, $message);
			$error = true;
			$info = "<font color='green'>" . __oio("Password reset, check your email") . "</font>";
		}
	}
}

//security question
$captcha = oiopub_captcha();
$_SESSION['next'] = $captcha['answer'];
session_write_close();

//get errors
if($error) {
	$ad_error .= "<table align='center' border='0' style='margin-bottom:20px;'>\n";
	$ad_error .= "<tr><td align='left'>\n";
	$ad_error .= "<ul style='margin:0px; padding:0px;'>\n";
	$ad_error .= $info."\n";
	$ad_error .= "</ul>\n";
	$ad_error .= "</td></tr>\n";
	$ad_error .= "</table>\n";
}

//template vars
$templates = array();
$templates['page'] = "affiliate_account";
$templates['title'] = $oiopub_set->site_name . " " . __oio("Affiliate Account");

//set error messages
if(isset($_POST['process']) && $_POST['process'] == "login") {
	$templates['failed_login'] = $ad_error;
} else {
	$templates['error'] = !empty($ad_error) ? $ad_error : (isset($_GET['updated']) ? "<b>" . __oio("Your details have been updated") . "</b>" : "");
}
//specific vars
$templates['aff_url'] = $aff_url;
$templates['discount_text'] = $discount_text;
$templates['discount_type'] = $discount_type;

//path
$templates['path'] = str_replace('\\', '/', dirname(__FILE__)) . "/templates";

//load template
include_once($oiopub_set->folder_dir . "/templates/core/main.tpl");

?>