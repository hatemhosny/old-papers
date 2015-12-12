<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

//paypal class
class oiopub_payment_paypal extends oiopub_payment {

	//init
	function oiopub_payment_paypal() {

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
		//form data
		$form  = '<html>';
		$form .= '<head>';
		$form .= '<title>' . $oiopub_set->site_name . ' - Processing Payment</title>';
		$form .= '</head>';
		$form .= '<body onload="document.form.submit();">';
		$form .= '<h3>Processing Payment...</h3>';
		if($oiopub_set->sandbox == 1) {
			$form .= '<form action="https://www.sandbox.paypal.com/cgi-bin/webscr" name="form" method="post">';
		} else {
			$form .= '<form action="https://ipnpb.paypal.com/cgi-bin/webscr" name="form" method="post">';
		}
		if($item['subscription'] == 1) {
			//convert from days to months?
			if($item['duration'] > 90 && $item['duration'] % 30 == 0) {
				$t3 = 'M';
				$item['duration'] = $item['duration'] / 30;
			} else {
				$t3 = 'D';
			}
			$form .= '<input type="hidden" name="cmd" value="_xclick-subscriptions" />';
			$form .= '<input type="hidden" name="a3" value="' . $item['cost'] . '" />';
			$form .= '<input type="hidden" name="p3" value="' . $item['duration'] . '" />';
			$form .= '<input type="hidden" name="t3" value="' . $t3 . '" />';
			$form .= '<input type="hidden" name="src" value="1" />';
			$form .= '<input type="hidden" name="sra" value="1" />';
			//$form .= '<input type="hidden" name="srt" value="3" />';
		} else {
			$form .= '<input type="hidden" name="cmd" value="_xclick" />';
		}
		$form .= '<input type="hidden" name="business" value="' . $oiopub_set->paypal['mail'] . '" />';
		$form .= '<input type="hidden" name="rm" value="1" />';
		$form .= '<input type="hidden" name="return" value="' . $oiopub_set->paypal_success . '&rand=' . $item['rand_id'] . '" />';
		$form .= '<input type="hidden" name="cancel_return" value="' . $oiopub_set->paypal_failed . '" />';
		$form .= '<input type="hidden" name="notify_url" value="' . $oiopub_set->paypal_ipn . '" />';
		$form .= '<input type="hidden" name="item_name" value="' . $item['description'] . '" />';
		$form .= '<input type="hidden" name="quantity" value="' . $item['quantity'] . '" />';
		$form .= '<input type="hidden" name="no_shipping" value="1" />';
		$form .= '<input type="hidden" name="amount" value="' . $item['cost'] . '" />';
		$form .= '<input type="hidden" name="currency_code" value="' . $item['currency'] . '" />';
		$form .= '<input type="hidden" name="custom" value="' . $item['rand_id'] . '" />';
		if(!empty($oiopub_set->paypal['page_style'])) {
			$form .= '<input type="hidden" name="page_style" value="' . $oiopub_set->paypal['page_style'] . '" />';
		}
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
		//format request
		$req = 'cmd=_notify-validate';
		foreach($_POST as $key=>$value) {
			$value = urlencode(stripslashes($value));
			$req .= "&$key=$value";
		}
		//set host
		if($oiopub_set->sandbox == 1) {
			$host = "www.sandbox.paypal.com";
		} else {
			$host = "ipnpb.paypal.com";
		}
		//set port
		if(extension_loaded('openssl')) {
			$protocol = "ssl://";
			$port = 443;
		} else {
			$protocol = "";
			$port = 80;
		}
		//set headers
		$header  = "POST /cgi-bin/webscr HTTP/1.0\r\n";
		$header .= "Host: " . $host . "\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
		//call paypal
		if($fp = fsockopen($protocol.$host, $port, $errno, $errstr, 30)) {
			fputs($fp, $header . $req);
			while(!feof($fp)) {
				$this->res = fgets($fp, 1024);
			}
			fclose($fp);
		} else {
			//http problem
			echo "There was a problem connecting to the IPN server, check your port status.";
			exit();
		}
	}

	//verify data
	function _verify_data() {
		global $oiopub_set;
		//set txn status
		$this->txn_payment = true;
		//get purchase data
		$this->_purchase_data($this->custom);
		//verify transaction
		if(strcmp($this->res, "VERIFIED") != 0 || empty($this->txn_type) || !$this->data) {
			die();
		}
		//payment checks only from this point
		if(strpos($this->txn_type, "subscr") !== false && $this->txn_type != "subscr_payment") {
			$this->txn_payment = false;
			return;
		}
		//payment completed?
		if(strcmp($this->payment_status, "Completed") != 0) {
			$this->log .= "Transaction marked as " . $this->payment_status . ", not Complete\n";
		}
		//transaction ID sent?
		if(empty($this->txn_id)) {
			$this->log .= "No Transaction ID Recorded\n";
		}
		//seller emails match?
		if($this->business != $oiopub_set->paypal['mail']) {
			$this->log .= "PayPal emails recorded did not match\n";
		}
		//amounts paid match?
		if($this->mc_gross != $this->data->payment_amount) {
			$this->log .= "The payment amounts recorded did not match\n";
		}
		//currencies match?
		if($this->mc_currency != $this->data->payment_currency) {
			$this->log .= "The payment currencies recorded did not match\n";
		}
	}
	
	//update db
	function _update_db() {
		//subscription ended
		if($this->txn_type == "subscr_eot") {
			if(empty($this->log) && $this->data->item_subscription == 1) {
				$this->_remove_subscription('expire');
			}
			return;
		}
		//subscription cancelled
		if($this->txn_type == "subscr_cancel") {
			if(empty($this->log) && $this->data->item_subscription == 1) {
				$this->_remove_subscription('cancel');
			}
			return;
		}
		//payment handler
		if($this->txn_payment && $this->data->payment_status != 1) {
			if(empty($this->log)) {
				//payment success
				$this->_payment_success($this->txn_id);
			} else {
				//payment failed
				$this->_payment_failed($this->txn_id);
			}
		}
	}
	
}

?>