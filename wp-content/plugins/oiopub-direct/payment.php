<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

//define vars
define('OIOPUB_PAYMENT', 1);

//init
include_once(str_replace("\\", "/", dirname(__FILE__)) . "/index.php");

//don't show errors
@ini_set('display_errors', 0);

//is plugin enabled?
if($oiopub_set->enabled != 1) {
	die("Plugin Currently Offline");
}

//correct bad uri
if(strpos($oiopub_set->request_uri, "+") !== false) {
	$request = str_replace("+", "", $oiopub_set->request_uri);
	header('Location:' . $request);
	exit();
}

//correct bad query string
if(strpos($oiopub_set->query_string, "?") !== false) {
	$qs = str_replace("?", "&", $oiopub_set->query_string);
	$request = str_replace($oiopub_set->query_string, $qs, $oiopub_set->request_uri);
	header('Location:' . $request);
	exit();
}

//clear vars
$renewal = false;
$subscription = array();
$rand_id = oiopub_var('rand', 'get');

//coupons
$coupon = "";
$coupon_error = "";

//get data
if(!empty($rand_id)) {
	$oio_data = $oiopub_db->GetRow("SELECT * FROM " . $oiopub_set->dbtable_purchases . " WHERE rand_id='$rand_id'");
} else {
	$oio_data = new oiopub_std;
}

//expired purchase?
if(!empty($rand_id) && $oio_data->item_status == 3) {
	$renewal = true;
	if($oio_data->payment_time != -1) {
		header("Location: purchase.php?do=" . oiopub_type_check($oio_data->item_channel) . "&expired=" . $rand_id);
		exit();
	}
}

//process
if($_GET['do'] == '') {
	if($oio_data && $oio_data->rand_id) {
		$type_check = explode("-", $rand_id);
		$ad_info = oiopub_adtype_info($oio_data);
		if($type_check[0] == 'p') {
			//paid review
			$item_title = $ad_info['type'];
		} elseif($type_check[0] == 'l') {
			//text ad
			$item_title = $ad_info['type'];
			$mylink = TRUE;
			if($oio_data->item_status == 3) {
				$free_space = FALSE;
				$type = $oio_data->item_type;
				$lz = "links_" . $type;
				if($oiopub_set->{$lz}['price'] > 0) {
					$oiopub_db->query("SELECT item_id FROM " . $oiopub_set->dbtable_purchases . " WHERE item_channel='2' AND item_type='$type' AND item_status < '2' LIMIT 5");
					$allowed = $oiopub_set->{$lz}['rows'] * $oiopub_set->{$lz}['cols'] * $oiopub_set->{$lz}['rotator'];
					if($oiopub_db->num_rows < $allowed) $free_space = TRUE;
				}
			}
		} elseif($type_check[0] == 'b') {
			//banner ad
			$item_title = $ad_info['type'];
			if($oio_data->item_status == 3) {
				$free_space = FALSE;
				$type = $oio_data->item_type;
				$bz = "banners_" . $type;
				if($oiopub_set->{$bz}['price'] > 0) {
					$oiopub_db->query("SELECT item_id FROM " . $oiopub_set->dbtable_purchases . " WHERE item_channel='2' AND item_type='$type' AND item_status < '2' LIMIT 5");
					$allowed = $oiopub_set->{$bz}['rows'] * $oiopub_set->{$bz}['cols'] * $oiopub_set->{$bz}['rotator'];
					if($oiopub_db->num_rows < $allowed) $free_space = TRUE;
				}
			}
		} elseif($type_check[0] == 'v') {
			//inline ad
			$item_title = $ad_info['type'];
			if($oio_data->item_status == 3) {
				$free_space = FALSE;
				$post_id = $oio_data->post_id;
				$type = $oio_data->item_type;
				if($type != 4 && $oiopub_set->inline_ads['price'] > 0) {
					$oiopub_db->query("SELECT item_id FROM " . $oiopub_set->dbtable_purchases . " WHERE item_channel='3' AND item_type='$type' AND item_status < '2' AND post_id='$post_id' LIMIT 5");
					if($oiopub_db->num_rows < $oiopub_set->inline_ads['rotator']) $free_space = TRUE;
				}
				if($type == 4 && $oiopub_set->inline_links['price'] > 0) {
					$oiopub_db->query("SELECT item_id FROM " . $oiopub_set->dbtable_purchases . " WHERE item_channel='3' AND item_type='$type' AND item_status < '2' AND post_id='$post_id' LIMIT 5");
					if($oiopub_db->num_rows < $oiopub_set->inline_links['rotator']) $free_space = TRUE;
				}
			}
		} elseif($type_check[0] == 's') {
			//custom purchases
			$type = $oio_data->item_type;
			$cn = "custom_" . $type;
			$item_title = $ad_info['type'];
			if($oio_data->item_status == 3) {
				$free_space = FALSE;
				if($oiopub_set->{$cn}['price'] > 0) {
					$oiopub_db->query("SELECT item_id FROM ".$oiopub_set->dbtable_purchases." WHERE item_channel='4' AND item_type='$type' AND item_status < '2' LIMIT 1");
					if($oiopub_db->num_rows < $oiopub_set->{$cn}['max'] || $oiopub_set->{$cn}['max'] == 0) $free_space = TRUE;
				}
			}
		}
		//subscription stuff
		if($oio_data->item_subscription == 1 && $oio_data->item_duration > 0) {
			$subscription['status'] = 1;
			$subscription['duration'] = $oio_data->item_duration;
		} else {
			$subscription['status'] = 0;
		}
		//coupon code?
		if($oiopub_set->coupons['enabled'] == 1 && isset($_POST['process']) && $_POST['process'] == "coupon_code") {
			//set vars
			$coupon = oiopub_clean($_POST['coupon']);
			$coupon_data = $oiopub_db->GetRow("SELECT * FROM " . $oiopub_set->dbtable_coupons . " WHERE code='" . $coupon . "' AND code!='' ORDER BY status DESC, id DESC LIMIT 1");
			//code not yet used?
			if(!$oio_data->coupon || $oio_data->coupon != $coupon) {
				//remove previous coupon?
				if($oio_data->coupon_discount > 0) {
					$oio_data->payment_amount += $oio_data->coupon_discount;
					$oiopub_db->query("UPDATE " . $oiopub_set->dbtable_purchases . " SET coupon='', coupon_discount='0.00', payment_amount='$oio_data->payment_amount' WHERE item_id='$oio_data->item_id' LIMIT 1");
					$oiopub_db->query("UPDATE " . $oiopub_set->dbtable_coupons . " SET times_used=times_used-1 WHERE code='$oio_data->coupon' LIMIT 1");
					$oio_data->coupon_discount = "0.00";
					$oio_data->coupon = "";				
				}
				//valid code?
				if(empty($coupon_data) || $coupon_data->status != 1) {
					$coupon_error = __oio("Invalid or expired coupon code");
				}
				//expired coupon?
				if(!$coupon_error && $coupon_data->expiry_date > 0 && $coupon_data->expiry_date < time()) {
					$oiopub_db->query("UPDATE " . $oiopub_set->dbtable_coupons . " SET status='0' WHERE id='$coupon_data->id' LIMIT 1");
					$coupon_error = __oio("This coupon code has now expired");
				}
				//used too many times?
				if(!$coupon_error && $coupon_data->max_usage > 0 && $coupon_data->times_used >= $coupon_data->max_usage) {
					$oiopub_db->query("UPDATE " . $oiopub_set->dbtable_coupons . " SET status='0' WHERE id='$coupon_data->id' LIMIT 1");
					$coupon_error = __oio("This coupon code has now expired");
				}
				//limited code?
				if(!$coupon_error && $coupon_data->type > 0) {
					if($coupon_data->type != $oio_data->item_channel) {
						$coupon_error = __oio("This coupon does not apply to your purchase");
					} elseif($coupon_data->type_sub && !in_array($oio_data->item_type, explode(",", $coupon_data->type_sub))) {
						$coupon_error = __oio("This coupon does not apply to your purchase");
					}
				}
				//save code?
				if(!$coupon_error) {
					//get discount amount
					if($coupon_data->percentage == 1) {
						$coupon_discount = $oio_data->payment_amount * ($coupon_data->discount / 100);
						$coupon_discount = number_format($coupon_discount, 2, ".", "");
					} else {
						$coupon_discount = $coupon_data->discount; 
					}
					//new payment amount
					$payment_amount = $oio_data->payment_amount - $coupon_discount;
					//update database
					$oiopub_db->query("UPDATE " . $oiopub_set->dbtable_purchases . " SET coupon='$coupon', coupon_discount='$coupon_discount', payment_amount='$payment_amount', payment_time='" . time() . "' WHERE item_id='$oio_data->item_id' LIMIT 1");
					$oiopub_db->query("UPDATE " . $oiopub_set->dbtable_coupons . " SET times_used=times_used+1 WHERE id='$coupon_data->id' LIMIT 1");
					//free item now?
					if($payment_amount <= "0.00") {
						//update db
						$oiopub_db->query("UPDATE " . $oiopub_set->dbtable_purchases . " SET item_status='" . ($oio_data->item_status == 3 ? 1 : $oio_data->item_status) . "', payment_status='1', payment_time='" . time() . "' WHERE item_id='$oio_data->item_id' LIMIT 1");
						//send confirmation email?
						if(isset($oiopub_set->mailmessage_9) && $oiopub_set->mailmessage_9) {
							$oio_data->method = 'approve';
							$subject = oiopub_email_placeholder(oiopub_email_readable($oiopub_set->mailsubject_9), $oio_data);
							$message = oiopub_email_placeholder(oiopub_email_readable($oiopub_set->mailmessage_9), $oio_data);
							oiopub_mail_client($oio_data->adv_email, $subject, $message);
						}
						//redirect user to confirmation
						header("Location: payment.php?do=success&rand=" . $rand_id);
						exit();
					}
					//set local vars
					$oio_data->coupon = $coupon;
					$oio_data->coupon_discount = $coupon_discount;
					$oio_data->payment_amount = $payment_amount;
				}
			}
		}
	}
}

//template vars
$templates = array();
$templates['page'] = "purchase_payment";
$templates['title'] = __oio("Make A Payment");

//load template
include_once($oiopub_set->folder_dir . "/templates/core/main.tpl");

?>