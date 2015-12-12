<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

//alertpay class
class oiopub_payment_alertpay extends oiopub_payment {

	//init
	function oiopub_payment_alertpay() {

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
			$form .= '<form action="https://secure.payza.com/checkout" name="form" method="post">';
		} else {
			$form .= '<form action="https://secure.payza.com/checkout" name="form" method="post">';
		}
		if($item['subscription'] == 1) {
			//convert from days to months?
			if($item['duration'] > 90 && $item['duration'] % 30 == 0) {
				$time_unit = 'Month';
				$item['duration'] = $item['duration'] / 30;
			} else {
				$time_unit = 'Day';
			}
			$form .= '<input type="hidden" name="ap_purchasetype" value="subscription" />';
			$form .= '<input type="hidden" name="ap_periodlength" value="' . $item['duration'] . '" />';
			$form .= '<input type="hidden" name="ap_timeunit" value="' . $time_unit . '" />';
		} else {
			$form .= '<input type="hidden" name="ap_purchasetype" value="item" />';
		}
		$form .= '<input type="hidden" name="ap_merchant" value="' . $oiopub_set->alertpay['mail'] . '" />';
		$form .= '<input type="hidden" name="ap_returnurl" value="' . $oiopub_set->alertpay_success . '&rand=' . $item['rand_id'] . '" />';
		$form .= '<input type="hidden" name="ap_cancelurl" value="' . $oiopub_set->alertpay_failed . '" />';
		$form .= '<input type="hidden" name="ap_amount" value="' . $item['cost'] . '" />';		
		$form .= '<input type="hidden" name="ap_itemname" value="' . $item['description'] . '" />';
		$form .= '<input type="hidden" name="ap_currency" value="' . $item['currency'] . '" />';
		$form .= '<input type="hidden" name="ap_itemcode" value="' . $item['rand_id'] . '" />';
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
		//nothing to do
	}

	//verify data
	function _verify_data() {
		global $oiopub_set;
		//set txn status
		$this->txn_payment = true;
		//get purchase data
		$this->_purchase_data($this->ap_itemcode);
		//verify transaction
		if($this->ap_securitycode != $oiopub_set->alertpay['security'] || !$this->data) {
			die();
		}
		//payment completed?
		$completed = array( 'Success', 'Subscription-Payment-Success', 'Subscription-Expired', 'Subscription-Canceled' );
		if(!in_array($this->ap_status, $completed)) {
			$this->log .= "Transaction marked as " . $this->ap_status . ", not Complete\n";
		}
		//transaction ID sent?
		if(empty($this->ap_referencenumber)) {
			$this->log .= "No Transaction ID Recorded\n";
		}
		//seller emails match?
		if($this->ap_merchant != $oiopub_set->alertpay['mail']) {
			$this->log .= "Payza emails recorded did not match\n";
		}
		//amounts paid match?
		if($this->ap_amount != $this->data->payment_amount) {
			$this->log .= "The payment amounts recorded did not match\n";
		}
		//currencies match?
		if($this->ap_currency != $this->data->payment_currency) {
			$this->log .= "The payment currencies recorded did not match\n";
		}
	}
	
	//update db
	function _update_db() {
		//subscription ended
		if($this->ap_status == "Subscription-Expired") {
			if(empty($this->log) && $this->data->item_subscription == 1) {
				$this->_remove_subscription('expire');
			}
			return;
		}
		//subscription cancelled
		if($this->ap_status == "Subscription-Canceled") {
			if(empty($this->log) && $this->data->item_subscription == 1) {
				$this->_remove_subscription('cancel');
			}
			return;
		}
		//payment handler
		if($this->txn_payment && $this->data->payment_status != 1) {
			if(empty($this->log)) {
				//payment success
				$this->_payment_success($this->ap_referencenumber);
			} else {
				//payment failed
				$this->_payment_failed($this->ap_referencenumber);
			}
		}
	}
	
}

?>