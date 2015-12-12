<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

//authorize.net class
class oiopub_payment_authorize extends oiopub_payment {

	//init
	function oiopub_payment_authorize() {

	}
	
	//process form
	function process_form($rand_id) {
		//get purchase data
		$purchases = $this->_purchase_form_data($rand_id);
		//form data
		$this->_build_form($purchases);
	}
	
	//build form
	function _build_form($item) {
		global $oiopub_set;
		//get vars
		$time = time();
		$data = $oiopub_set->authorize['login_id'] . "^" . $item['rand_id'] . "^" . $time . "^" . number_format($item['cost'], 2) . "^";
		$hash = oiopub_hmac("md5", $data, $oiopub_set->authorize['transaction_key']);
		//form data
		$form  = '<html>';
		$form .= '<head>';
		$form .= '<title>' . $oiopub_set->site_name . ' - Processing Payment</title>';
		$form .= '</head>';
		$form .= '<body onload="document.form.submit();">';
		$form .= '<h3>Processing Payment...</h3>';
		$form .= '<form action="https://secure.authorize.net/gateway/transact.dll" name="form" method="post">';
		//$form .= '<form action="https://test.authorize.net/gateway/transact.dll" name="form" method="post">';
		if($oiopub_set->sandbox == 1) {
			$form .= '<input type="hidden" name="x_test_request" value="true" />';
		} else {
			$form .= '<input type="hidden" name="x_test_request" value="false" />';
		}
		$form .= '<input type="hidden" name="x_version" value="3.1" />';
		$form .= '<input type="hidden" name="x_login" value="' . $oiopub_set->authorize['login_id'] . '" />';
		$form .= '<input type="hidden" name="x_show_form" value="PAYMENT_FORM" />';
		$form .= '<input type="hidden" name="x_amount" value="' . number_format($item['cost'], 2) . '" />';
		$form .= '<input type="hidden" name="x_invoice_num" value="' . $item['rand_id'] . '" />';
		$form .= '<input type="hidden" name="x_description" value="' . $item['name'] . '" />';
		$form .= '<input type="hidden" name="x_fp_sequence" value="' . $item['rand_id'] . '" />';
		$form .= '<input type="hidden" name="x_fp_timestamp" value="' . $time . '" />';
		$form .= '<input type="hidden" name="x_fp_hash" value="' . $hash . '" />';
		$form .= '<input type="hidden" name="x_receipt_link_method" value="LINK" />';
		$form .= '<input type="hidden" name="x_receipt_link_text" value="Click here to continue" />';
		$form .= '<input type="hidden" name="x_receipt_link_url" value="' . $oiopub_set->authorize_success . '&rand=' . $item['rand_id'] . '" />';
		$form .= '<input type="hidden" name="x_relay_response" value="true" />';
		$form .= '<input type="hidden" name="x_relay_url" value="' . $oiopub_set->authorize_ipn . '" />';
		$form .= '<input type="submit" value="Continue to checkout" id="continue" />';
		$form .= '</form>';
		$form .= '<script type="text/javascript">';
		$form .= 'document.getElementById("continue").style.visibility = "hidden";';
		$form .= '</script>';
		$form .= '</body>';
		$form .= '</html>';
		echo $form;
	}
	
	//verify request
	function _verify_request() {
		global $oiopub_set;
		echo "<b>Finalizing Payment...</b>\n";
		//demo sale?
		if($oiopub_set->sandbox == 1) {
			$this->x_trans_id = 0;
		}
		//get md5 hash
		$check_string = $oiopub_set->authorize['md5_hash'] . $oiopub_set->authorize['login_id'] . $this->x_trans_id . $this->x_amount;
		$this->check_hash = strtoupper(md5($check_string));
	}
	
	//verify data
	function _verify_data() {
		global $oiopub_set;
		//verify transaction?
		if($oiopub_set->authorize['md5_hash']) {
			if(!$this->check_hash || $this->check_hash !== $this->x_MD5_Hash) {
				//redirect user
				echo "<br /><br />\n";
				echo "<a href=\"" . $oiopub_set->authorize_failed . "\">If you are not re-directed automatically, click here.</a>\n";
				echo "<meta http-equiv=\"refresh\" content=\"1;URL=" . $oiopub_set->authorize_failed . "\" />\n";
				exit();
			}
		}
		if($this->x_response_code != 1) {
			$this->log .= "Transaction not approved by Authorize.net\n";
		}
		if(empty($this->x_trans_id) && $oiopub_set->sandbox != 1) {
			$this->log .= "No Transaction ID Recorded\n";
		}
		//get purchase data
		$this->_purchase_data($this->x_invoice_num);
		//complete verification
		if(!$this->data) {
			$this->log .= "No records matched the data sent\n";
		}
		if($this->x_amount != $this->data->payment_amount) {
			$this->log .= "The payment amounts recorded did not match\n";
		}
	}
	
	//update db
	function _update_db() {
		global $oiopub_set;
		if($this->data && $this->data->payment_status != 1) {
			if(empty($this->log)) {
				//payment success
				$this->_payment_success($this->x_trans_id);
			} else {
				//payment failed
				$this->_payment_failed($this->x_trans_id);
			}
		}
		//get redirect url
		if(empty($this->x_invoice_num)) {
			$url = $oiopub_set->twocheckout_failed;
		} else {
			$url = $oiopub_set->twocheckout_success . "&rand=" . $this->x_invoice_num;
		}
		//redirect user
		echo "<br /><br />\n";
		echo "<a href=\"" . $url . "\">If you are not re-directed automatically, click here.</a>\n";
		echo "<meta http-equiv=\"refresh\" content=\"1;URL=" . $url . "\" />\n"; 
		exit();
	}
	
}

?>