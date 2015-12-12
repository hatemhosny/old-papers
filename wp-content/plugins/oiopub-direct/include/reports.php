<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

//reporting
class oiopub_reports {

	var $show_emails = 1;
	
	//constructor
	function oiopub_reports() {
		global $oiopub_set;
		//demo mode?
		if($oiopub_set->demo) {
			$this->show_emails = 0;
		}
	}

	//export purchase data (html)
	function html_purchases($args=array()) {
		global $oiopub_db, $oiopub_set;
		//set vars
		$where = "1=1";
		$array = array();
		$totals_price = 0;
		//set data options
		$date_from = oiopub_clean($args['from']);
		$date_to = oiopub_clean($args['to']);
		$history = intval($args['history']);
		//where clause
		$where .= " AND item_status < '3'";
		//add from date?
		if(!empty($date_from)) {
			$where .= " AND payment_time >= '" . strtotime($date_from) . "'";
		}
		//add to date?
		if(!empty($date_to)) {
			$where .= " AND payment_time <= '" . strtotime($date_to) . "'";
		}
		//headers
		$headers = "<tr><th><b>ID</b></th><th><b>Client</b></th><th><b>Purchase type</b></th><th><b>Amount</b></th><th><b>Processor</b></th><th><b>Subscription</b></th><th><b>Payment date</b></th><th><b>Status</b></th></tr>\n";
		//process sql
		$sql = (array) $oiopub_db->GetAll("SELECT * FROM " . $oiopub_set->dbtable_purchases . " WHERE " . $where . " ORDER BY item_id ASC");
		//loop through data
		foreach($sql as $s) {
			$pdata = oiopub_adtype_info($s);
			$date = date("Y-m-d", $s->payment_time);
			if($s->item_subscription == 1) {
				$subscription = "Yes";
			} else {
				$subscription = "No";
			}
			$ad_type = array_map('trim', explode(',', $pdata['type']));
			$array[] = "<tr><td>" . $s->item_id . "</td><td>" . $s->adv_name . "<br /><i>" . ($this->show_emails == 1 ? $s->adv_email : "email hidden") . "</i></td><td>" . $ad_type[0] . (isset($ad_type[1]) ? "<br /><i>" . $ad_type[1] . "</i>" : "") . "</td><td>" . oiopub_amount($s->payment_amount, $s->payment_currency) . "<br /><i>" . ($s->item_duration > 0 ? "for " . number_format($s->item_duration, 0) . " " . $s->item_model : "permanent") . "</i></td><td>" . strtolower($s->payment_processor) . "</td><td>" . $subscription . "</td><td>" . $date. "</td><td>" . $pdata['istatus'] . "<br /><i>" . $pdata['pstatus'] . "</i></td></tr>\n";
			$totals_price += $s->payment_amount;
		}
		//add history?
		if($history == 1) {
			//clear
			$where = "";
			//add from date?
			if(!empty($date_from)) {
				$where .= " AND h.time >= '" . strtotime($date_from) . "'";
			}
			//add to date?
			if(!empty($date_to)) {
				$where .= " AND h.time <= '" . strtotime($date_to) . "'";
			}
			//process sql
			$sql = (array) $oiopub_db->GetAll("SELECT h.*, p.item_id, p.adv_name, p.adv_email, p.item_url, p.item_page, p.item_notes FROM " . $oiopub_set->dbtable_purchases_history . " h, " . $oiopub_set->dbtable_purchases . " p WHERE h.item=p.item_id " . $where . " ORDER BY p.item_id ASC");
			//loop through data
			foreach($sql as $s) {
				$pdata = oiopub_adtype_info($s);
				$date = date("Y-m-d", $s->time);
				if($s->subscription == 1) {
					$subscription = "Yes";
				} else {
					$subscription = "No";
				}
				$ad_type = array_map('trim', explode(',', $pdata['type']));
				$array[] = "<tr><td>" . $s->item_id . "</td><td>" . $s->adv_name . "<br /><i>" . ($this->show_emails == 1 ? $s->adv_email : "email hidden") . "</i></td><td>" . $ad_type[0] . (isset($ad_type[1]) ? "<br /><i>" . $ad_type[1] . "</i>" : "") . "</td><td>" . oiopub_amount($s->amount, $s->currency) . "<br /><i>" . ($s->item_duration > 0 ? "for " . number_format($s->item_duration, 0) . " " . $s->item_model : "permanent") . "</i></td><td>" . strtolower($s->processor) . "</td><td>" . $subscription . "</td><td>" . $date . "</td><td>Expired<br /><i>Paid</i></td></tr>";
				$totals_price += $s->amount;
			}
		}
		$output  = "<table width='100%' border='0' cellspacing='0' cellpadding='6' class='widefat'>\n";
		$output .= "<thead>\n";
		$output .= $headers;
		$output .= "</thead>\n";
		$output .= "<tbody>\n";
		if(!empty($array)) {
			foreach($array as $a) {
				$output .= $a;
			}
		} else {
			$output .= "<tr><td colspan='8' align='center' style='padding-top:15px;'><i>no data currently available</i></td></tr>\n";
		}
		$output .= "</tbody>\n";
		$output .= "</table>\n";
		//display
		return $output;
	}
	
	//export purchase data (excel)
	function excel_purchases($args=array()) {
		global $oiopub_db, $oiopub_set;
		//set vars
		$where = "1=1";
		$array = array();
		$totals_price = 0;
		$filename = "oiopub-purchases-" . date('Ymd') . ".xls";
		//set data options
		$date_from = oiopub_clean($args['from']);
		$date_to = oiopub_clean($args['to']);
		$history = intval($args['history']);
		//where clause
		$where .= " AND item_status < '3'";
		//add from date?
		if(!empty($date_from)) {
			$where .= " AND payment_time >= '" . strtotime($date_from) . "'";
		}
		//add to date?
		if(!empty($date_to)) {
			$where .= " AND payment_time <= '" . strtotime($date_to) . "'";
		}
		//headers
		$array[] = "Purchase ID\tName\tEmail\tAd type\tAd zone\tTarget URL\tImage/Anchor text\tNotes\tModel\tDuration\tAmount\tCurrency\tProcessor\tSubscription\tPayment Date\tApproval status\tPayment status";
		//process sql
		$sql = (array) $oiopub_db->GetAll("SELECT * FROM " . $oiopub_set->dbtable_purchases . " WHERE " . $where . " ORDER BY item_id ASC");
		//loop through data
		foreach($sql as $s) {
			$pdata = oiopub_adtype_info($s);
			$date = date("Y-m-d", $s->payment_time);
			if($s->item_subscription == 1) {
				$subscription = "Yes";
			} else {
				$subscription = "No";
			}
			$target_url = $anchor_image = $notes = '';
			$ad_type = array_map('trim', explode(',', $pdata['type']));
			if(!$s->item_notes || strip_tags($s->item_notes) === $s->item_notes) {
				if($s->item_type == 1) {
					$target_url = $s->item_url;
					$anchor_image = $s->item_page;
					$notes = str_replace(array("\r\n", "\n"), " ", $s->item_notes);
				} else {
					$target_url = $s->item_page;
					$anchor_image = $s->item_url;
					$notes = str_replace(array("\r\n", "\n"), " ", $s->item_notes);
				}
			}
			$array[] = $s->item_id . "\t" . $s->adv_name . "\t" . ($this->show_emails == 1 ? $s->adv_email : "*****") . "\t" . $ad_type[0] . "\t" . (isset($ad_type[1]) ? $ad_type[1] : "") . "\t" . $target_url . "\t" . $anchor_image . "\t" . $notes . "\t" . ($s->item_duration > 0 ? number_format($s->item_duration, 0) : "permanent") . "\t" . $s->item_model . "\t" . $s->payment_amount . "\t" . strtoupper($s->payment_currency) . "\t" . strtolower($s->payment_processor) . "\t" . $subscription . "\t" . $date . "\t" . $pdata['istatus'] . "\t" . $pdata['pstatus'];
			$totals_price += $s->payment_amount;
		}
		//add history?
		if($history == 1) {
			//clear
			$where = "";
			//add from date?
			if(!empty($date_from)) {
				$where .= " AND h.time>='" . strtotime($date_from) . "'";
			}
			//add to date?
			if(!empty($date_to)) {
				$where .= " AND h.time<='" . strtotime($date_to) . "'";
			}
			//process sql
			$sql = (array) $oiopub_db->GetAll("SELECT h.*, p.item_id, p.adv_name, p.adv_email, p.item_url, p.item_page, p.item_notes FROM " . $oiopub_set->dbtable_purchases_history . " h, " . $oiopub_set->dbtable_purchases . " p WHERE h.item=p.item_id " . $where . " ORDER BY p.item_id ASC");
			//loop through data
			foreach($sql as $s) {
				$pdata = oiopub_adtype_info($s);
				$date = date("Y-m-d", $s->time);
				if($s->subscription == 1) {
					$subscription = "Yes";
				} else {
					$subscription = "No";
				}
				$target_url = $anchor_image = $notes = '';
				$ad_type = array_map('trim', explode(',', $pdata['type']));
				if(!$s->item_notes || strip_tags($s->item_notes) === $s->item_notes) {
					if($s->item_type == 1) {
						$target_url = $s->item_url;
						$anchor_image = $s->item_page;
						$notes = str_replace(array("\r\n", "\n"), " ", $s->item_notes);
					} else {
						$target_url = $s->item_page;
						$anchor_image = $s->item_url;
						$notes = str_replace(array("\r\n", "\n"), " ", $s->item_notes);
					}
				}
				$array[] = $s->item_id . "\t" . $s->adv_name . "\t" . ($this->show_emails == 1 ? $s->adv_email : "*****") . "\t" . $ad_type[0] . "\t" . (isset($ad_type[1]) ? $ad_type[1] : "") . "\t" . $target_url . "\t" . $image_anchor . "\t" . $notes . "\t" . ($s->item_duration > 0 ? number_format($s->item_duration, 0) : "permanent") . "\t" . $s->item_model . "\t" . $s->amount . "\t" . strtoupper($s->currency) . "\t" . strtolower($s->processor) . "\t" . $subscription . "\t" . $date . "\tExpired\tPaid";					
				$totals_price += $s->amount;
			}
		}
		//headers
		header("Content-type: application/octet-stream");
		header("Content-Disposition: attachment; filename=$filename");
		header("Pragma: no-cache");
		header("Expires: 0");
		//display
		return implode("\n", $array);
	}
	
	//export affiliate data (excel)
	function excel_affiliates($args=array()) {
		global $oiopub_set, $oiopub_db;
		//set vars
		$array = array();
		$filename = "oiopub-affiliates-" . date('Ymd') . ".xls";
		$maturity_time = time() - ($oiopub_set->affiliates['maturity'] * 86400);
		//get data
		$data = (array) $oiopub_db->GetAll("SELECT s.affiliate_id, SUM(s.affiliate_amount) as total, s.affiliate_currency, a.paypal FROM " . $oiopub_set->dbtable_affiliates_sales . " s LEFT JOIN " . $oiopub_set->dbtable_affiliates . " a ON s.affiliate_id=a.id WHERE s.affiliate_paid='0' AND s.purchase_payment='1' AND s.purchase_time < '$maturity_time' GROUP BY s.affiliate_id, s.affiliate_currency");
		//loop through data
		foreach($data as $d) {
			if(!empty($d->paypal) && $d->total > '0.00') {
				$array[] = $d->paypal . "\t" . number_format($d->total, 2) . "\t" . $d->affiliate_currency . "\n";
			}
		}
		//headers
		header("Content-type: application/octet-stream");
		header("Content-Disposition: attachment; filename=$filename");
		header("Pragma: no-cache");
		header("Expires: 0");
		//return
		return implode("\n", $array);
	}

}

?>