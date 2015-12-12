<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

//approvals
class oiopub_approvals {

	var $mail_adv = 1;
	var $mail_admin = 1;

	//purchase submit
	function submit($id) {
		global $oiopub_db, $oiopub_set, $oiopub_hook;
		$id = intval($id);
		$item = $oiopub_db->GetRow("SELECT * FROM " . $oiopub_set->dbtable_purchases . " WHERE item_id='$id' LIMIT 1");
		if($item->item_id > 0) {
			$item_data = oiopub_adtype_info($item);
			$item->item_title = $item_data['type'];
			$item->cost = oiopub_amount($item->payment_amount, $item->payment_currency);
			$item->method = "submit";
			$oiopub_hook->fire('approvals_submit', array($id, $item_data));
			if($item->submit_api != 2) {
				$subject = oiopub_email_placeholder(oiopub_email_readable($oiopub_set->mailsubject_1), $item);
				$message = oiopub_email_placeholder(oiopub_email_readable($oiopub_set->mailmessage_1), $item);
				if($this->mail_adv == 1) {
					oiopub_mail_client($item->adv_email, $subject, $message);
				}
			}
			//send admin email?
			if($this->mail_admin == 1) {
				$subject  = $oiopub_set->site_name . " - New " . $item_data['type'] . " Submitted";
				$message  = "A new purchase has been submitted:\n\n";
				$message .= ">> " . $oiopub_set->admin_url . "/admin.php?page=oiopub-manager.php&opt=" . oiopub_type_check($item->item_channel);
				oiopub_mail_client($oiopub_set->admin_mail, $subject, $message);
			}
		}
	}

	//purchase approve
	function approve($id) {
		global $oiopub_db, $oiopub_set, $oiopub_api, $oiopub_hook;
		$time = time();
		$id = intval($id);
		$item = $oiopub_db->GetRow("SELECT * FROM " . $oiopub_set->dbtable_purchases . " WHERE item_id='$id' LIMIT 1");
		if($item->item_id > 0) {
			$app_time = $item->payment_time;
			if($item->item_status != 1 && $item->item_status != -1) {
				$item_data = oiopub_adtype_info($item);	
				$item->item_title = $item_data['type'];
				if($item->item_status == -2) {
					$app_status = -1;
				} else {
					$app_status = 1;
					$app_time = $app_time > 0 ? $time : 0;
				}
				$oiopub_db->query("UPDATE " . $oiopub_set->dbtable_purchases . " SET item_status='$app_status', payment_time='$app_time' WHERE item_id='$id' LIMIT 1");
				$oiopub_hook->fire('approvals_approve', array($id, $item_data));
				$item->cost = oiopub_amount($item->payment_amount, $item->payment_currency);
				$item->method = "approve";
				if($item->submit_api != 2) {
					$subject = oiopub_email_placeholder(oiopub_email_readable($oiopub_set->mailsubject_2), $item);
					$message = oiopub_email_placeholder(oiopub_email_readable($oiopub_set->mailmessage_2), $item);
					if($this->mail_adv == 1) {
						oiopub_mail_client($item->adv_email, $subject, $message);
					}
				}
				if($item->submit_api > 0) {
					$oiopub_api->status_send($id, "approval", $app_status);
				}
			}
		}
	}
	
	//purchase approve
	function promote($id) {
		global $oiopub_db, $oiopub_set, $oiopub_api, $oiopub_hook;
		$time = time();
		$id = intval($id);
		$item = $oiopub_db->GetRow("SELECT * FROM " . $oiopub_set->dbtable_purchases . " WHERE item_id='$id' LIMIT 1");
		if($item->item_id > 0) {
			if($item->item_status == -1) {
				$item->payment_time = $time;
				$item_data = oiopub_adtype_info($item);	
				$item->item_title = $item_data['type'];
				$oiopub_db->query("UPDATE " . $oiopub_set->dbtable_purchases . " SET item_status='1', payment_time='$time' WHERE item_id='$id' LIMIT 1");
				$oiopub_hook->fire('approvals_promote', array($id, $item_data));
				$item->cost = oiopub_amount($item->payment_amount, $item->payment_currency);
				$item->method = "promote";
				if($item->submit_api != 2) {
					$subject = oiopub_email_placeholder(oiopub_email_readable($oiopub_set->mailsubject_8), $item);
					$message = oiopub_email_placeholder(oiopub_email_readable($oiopub_set->mailmessage_8), $item);
					if($this->mail_adv == 1) {
						oiopub_mail_client($item->adv_email, $subject, $message);
					}
				}
				if($item->submit_api > 0) {
					$oiopub_api->status_send($id, "approval", 1);
				}
			}
		}
	}

	//purchase publish
	function publish($id) {
		global $oiopub_db, $oiopub_set, $oiopub_hook;
		$id = intval($id);
		$item = $oiopub_db->GetRow("SELECT * FROM " . $oiopub_set->dbtable_purchases . " WHERE item_id='$id' LIMIT 1");
		if($item->item_id > 0) {
			$item_data = oiopub_adtype_info($item);
			$oiopub_hook->fire('approvals_publish', array($id, $item_data));
		}
	}

	//purchase reject
	function reject($id) {
		global $oiopub_db, $oiopub_set, $oiopub_api, $oiopub_hook;
		$id = intval($id);
		$item = $oiopub_db->GetRow("SELECT * FROM " . $oiopub_set->dbtable_purchases . " WHERE item_id='$id' LIMIT 1");
		if($item->item_id > 0) {
			if($item->item_status != 2) {
				$item_data = oiopub_adtype_info($item);
				$item->item_title = $item_data['type'];
				$item->cost = oiopub_amount($item->payment_amount, $item->payment_currency);
				$item->method = "reject";
				$oiopub_db->query("UPDATE " . $oiopub_set->dbtable_purchases . " SET item_status='2' WHERE item_id='$id' LIMIT 1");
				$oiopub_hook->fire('approvals_reject', array($id, $item_data));
				if($item->submit_api != 2) {
					$subject = oiopub_email_placeholder(oiopub_email_readable($oiopub_set->mailsubject_3), $item);
					$message = oiopub_email_placeholder(oiopub_email_readable($oiopub_set->mailmessage_3), $item);
					if($this->mail_adv == 1) {
						oiopub_mail_client($item->adv_email, $subject, $message);
					}
				}
				if($item->submit_api > 0) {
					$oiopub_api->status_send($id, "approval", 2);
				}
			}
		}
	}

	//purchase remind
	function remind($id) {
		global $oiopub_db, $oiopub_set, $oiopub_hook;
		$id = intval($id);
		$item = $oiopub_db->GetRow("SELECT * FROM " . $oiopub_set->dbtable_purchases . " WHERE item_id='$id' LIMIT 1");
		if($item->item_id > 0) {
			if($item->payment_status == 0 || $item->payment_status == 2) {
				$item_data = oiopub_adtype_info($item);
				$item->item_title = $item_data['type'];
				$item->cost = oiopub_amount($item->payment_amount, $item->payment_currency);
				$item->method = "remind";
				if($item->item_status == -1) {
					$app_status = -1;
				} else {
					$app_status = 1;
				}
				if($item->payment_status == 2) {
					$oiopub_db->query("UPDATE " . $oiopub_set->dbtable_purchases . " SET item_status='$app_status', payment_status='0' WHERE item_id='$id' LIMIT 1");
				}
				$oiopub_hook->fire('approvals_remind', array($id, $item_data));
				$subject = oiopub_email_placeholder(oiopub_email_readable($oiopub_set->mailsubject_4), $item);
				$message = oiopub_email_placeholder(oiopub_email_readable($oiopub_set->mailmessage_4), $item);
				if($this->mail_adv == 1) {
					oiopub_mail_client($item->adv_email, $subject, $message);
				}
			}
		}
	}

	//purchase expire
	function expire($id) {
		global $oiopub_db, $oiopub_set, $oiopub_api, $oiopub_hook;
		$id = intval($id);
		$nofollow = 0;
		$item = $oiopub_db->GetRow("SELECT * FROM " . $oiopub_set->dbtable_purchases . " WHERE item_id='$id' LIMIT 1");
		if($item->item_id > 0) {
			if($item->item_status != 3) {
				$this->history($item);
				$item_data = oiopub_adtype_info($item);
				$item->item_title = $item_data['type'];
				$item->cost = oiopub_amount($item->payment_amount, $item->payment_currency);
				$item->method = "expire";
				$oiopub_db->query("UPDATE " . $oiopub_set->dbtable_purchases . " SET item_status='3', payment_status='0', coupon='', coupon_discount='0.00' WHERE item_id='$id' LIMIT 1");
				$oiopub_hook->fire('approvals_expire', array($id, $item_data));
				$subject = oiopub_email_placeholder(oiopub_email_readable($oiopub_set->mailsubject_5), $item);
				$message = oiopub_email_placeholder(oiopub_email_readable($oiopub_set->mailmessage_5), $item);
				if($this->mail_adv == 1) {
					oiopub_mail_client($item->adv_email, $subject, $message);
				}
				if($item->item_channel == 4) {
					//send admin email?
					if($this->mail_admin == 1) {
						$subject = $oiopub_set->site_name . " - Custom Service expired!";
						$message = "A custom service (ID #".$id.") has just expired on " . $oiopub_set->site_url . ". Since these services must be dealt with manually, you may need to remove the service from your site, unless the advertiser renews the purchase.";
						oiopub_mail_client($oiopub_set->admin_mail, $subject, $message);
					}
				}
				if($item->submit_api > 0) {
					$oiopub_api->status_send($id, "approval", 3);
				}
				$promote_id = $oiopub_db->GetOne("SELECT item_id FROM " . $oiopub_set->dbtable_purchases . " WHERE item_channel='$item->item_channel' AND item_status='-1' AND payment_status='1' AND item_type='$item->item_type' ORDER BY payment_time LIMIT 1");
				if($promote_id > 0) {
					$this->promote($promote_id);
				}
			}
		}
	}
	
	//log history
	function history($item) {
		global $oiopub_db, $oiopub_set, $oiopub_hook;
		if($item->item_id > 0 && $item->item_status == 1 && $item->payment_status == 1) {
			$oiopub_db->query("INSERT INTO " . $oiopub_set->dbtable_purchases_history . " (item,processor,currency,amount,time,subscription) VALUES ('$item->item_id','$item->payment_processor','$item->payment_currency','$item->payment_amount','$item->payment_time','$item->item_subscription')");
			$oiopub_hook->fire('approvals_history', array($item));
			return true;
		}
		return false;
	}
	
	//purchase renew
	function renew($id) {
		global $oiopub_db, $oiopub_set, $oiopub_api, $oiopub_hook;
		$time = time();
		$id = intval($id);
		$new_status = 3;
		$item = $oiopub_db->GetRow("SELECT * FROM " . $oiopub_set->dbtable_purchases . " WHERE item_id='$id' LIMIT 1");
		if($item->item_id > 0) {
			if($item->item_status == 3) {
				$available = oiopub_spots_available($item->item_channel, $item->item_type);
				if($available > 0) {
					$new_status = 1;
					$item->item_status = 0;
				} else {
					$new_status = -1;
					$item->item_status = -2;
				}
				$item_data = oiopub_adtype_info($item);
				$item->item_title = $item_data['type'];
				$item->cost = oiopub_amount($item->payment_amount, $item->payment_currency);
				$item->method = "renew";
				$oiopub_db->query("UPDATE " . $oiopub_set->dbtable_purchases . " SET item_status='$new_status', item_duration_left=item_duration, payment_status='1', payment_time='$time' WHERE item_id='$id' LIMIT 1");
				$oiopub_hook->fire('approvals_renew', array($id, $item_data));
				$subject = oiopub_email_placeholder(oiopub_email_readable($oiopub_set->mailsubject_6), $item);
				$message = oiopub_email_placeholder(oiopub_email_readable($oiopub_set->mailmessage_6), $item);
				if($this->mail_adv == 1) {
					oiopub_mail_client($item->adv_email, $subject, $message);
				}
				if($item->submit_api > 0) {
					$oiopub_api->status_send($id, "approval", 1);
				}
				//send admin email?
				if($this->mail_admin == 1 && $new_status == 1) {
					$subject  = $oiopub_set->site_name . " - " . $item_data['type'] . " Renewed";
					$message  = "A purchase has been renewed:\n\n";
					$message .= ">> " . $oiopub_set->plugin_url_org . "/edit.php?type=" . oiopub_type_check($item->item_channel) . "&id=" . $item->item_id;
					oiopub_mail_client($oiopub_set->admin_mail, $subject, $message);				
				}
			}
		}
	}

	//purchase validate
	function validate($id) {
		global $oiopub_db, $oiopub_set, $oiopub_api, $oiopub_hook;
		$id = intval($id);
		$time = time();
		$item = $oiopub_db->GetRow("SELECT * FROM " . $oiopub_set->dbtable_purchases . " WHERE item_id='$id' LIMIT 1");
		if($item->item_id > 0) {
			if($item->payment_status != 1) {
				$item_data = oiopub_adtype_info($item);
				$oiopub_db->query("UPDATE " . $oiopub_set->dbtable_purchases . " SET payment_status='1', payment_time='$time' WHERE item_id='$id' LIMIT 1");
				$oiopub_hook->fire('approvals_validate', array($id, $item_data));
				if($item->submit_api > 0) {
					$oiopub_api->status_send($id, "payment", 1);
				}
			}
		}
	}

	//purchase void
	function void($id) {
		global $oiopub_db, $oiopub_set, $oiopub_api, $oiopub_hook;
		$id = intval($id);
		$time = time();
		$item = $oiopub_db->GetRow("SELECT * FROM " . $oiopub_set->dbtable_purchases . " WHERE item_id='$id' LIMIT 1");
		if($item->item_id > 0) {
			if($item->payment_status != 2) {
				$item_data = oiopub_adtype_info($item);
				$oiopub_db->query("UPDATE " . $oiopub_set->dbtable_purchases . " SET payment_status='2' WHERE item_id='$id' LIMIT 1");
				$oiopub_hook->fire('approvals_void', array($id, $item_data));
				if($item->submit_api > 0) {
					$oiopub_api->status_send($id, "approval", 2);
				}
			}
		}
	}
	
	//delete purchase
	function delete($id) {
		global $oiopub_db, $oiopub_set, $oiopub_hook;
		$id = intval($id);
		$item = $oiopub_db->GetRow("SELECT * FROM " . $oiopub_set->dbtable_purchases . " WHERE item_id='$id' LIMIT 1");
		if($item->item_id > 0) {
			$item_data = oiopub_adtype_info($item);
			$oiopub_db->query("DELETE FROM " . $oiopub_set->dbtable_purchases . " WHERE item_id='$id' LIMIT 1");
			$oiopub_db->query("DELETE FROM " . $oiopub_set->dbtable_purchases_history . " WHERE item='$id'");
			$oiopub_hook->fire('approvals_delete', array($id, $item_data));
		}
	}
	
}

?>