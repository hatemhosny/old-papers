<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

/* WORDPRESS POSTS CLASS */

class oiopub_posts {

	var $data;

	//init
	function oiopub_posts() {
		if(oiopub_posts) {
			$this->hooks();
		}
	}

	//hooks
	function hooks() {
		global $oiopub_hook;
		$oiopub_hook->add('approvals_publish', array(&$this, 'publish_post'));
		add_action('publish_post', array(&$this, 'publish_correct'), -10);
	}
	
	//publish post
	function publish_post($id, $item) {
		global $oiopub_set, $oiopub_db, $oiopub_api;
		global $wpdb;
		$user = $oiopub_db->GetRow("SELECT p.post_title, p.post_status, p.post_content, o.* FROM " . $oiopub_set->dbtable_purchases . " o INNER JOIN " . $wpdb->posts . " p ON o.post_id=p.ID WHERE o.item_id='$id'");
		$user->item_title = "Paid Review";
		if($user->published_status != 1 && $user->post_id > 0) {
			if(function_exists('wp_publish_post')) {
				$oiopub_db->query("UPDATE " . $oiopub_set->dbtable_purchases . " SET published_status='1' WHERE item_id='$id' LIMIT 1");
				$user->cost = oiopub_amount($user->payment_amount, $user->payment_currency);
				$subject = oiopub_email_placeholder(oiopub_email_readable($oiopub_set->mailsubject_7), $user);
				$message = oiopub_email_placeholder(oiopub_email_readable($oiopub_set->mailmessage_7), $user);
				oiopub_mail_client($user->adv_email, $subject, $message);
				if($user->submit_api > 0) {
					$oiopub_api->status_send($id, "published", 1, $user->post_id);
				}
				//add disclosure?
				if($oiopub_set->disclosure && strpos($user->post_content, $oiopub_set->disclosure) === false) {
					$disclosure = trim($oiopub_set->disclosure);
					if(stripos($disclosure, '<p') === false) {
						$disclosure = '<p>' . $disclosure . '</p>';
					}
					$oiopub_db->query("UPDATE " . $wpdb->posts . " SET post_content='" . $user->post_content . $disclosure . "' WHERE ID=" . $user->post_id);
				}
				//published yet?
				if($user->post_status != "publish") {
					wp_publish_post($user->post_id);
				}
			}
		}
	}

	//correct publishing time
	function publish_correct() {
		global $oiopub_db, $oiopub_set;
		global $wpdb, $post_ID;
		$post_ID = intval($post_ID);
		$check = $oiopub_db->GetRow("SELECT o.item_id, o.item_status, o.payment_status FROM " . $oiopub_set->dbtable_purchases . " o INNER JOIN " . $wpdb->posts . " p ON o.post_id=p.ID WHERE o.post_id='$post_ID' AND item_channel='1'");
		if(!empty($check)) {
			//check status
			if($check->item_status != 1) {
				//publishing = approve
				oiopub_approve("approve", $check->item_id);
			}
			//check payment
			if($check->payment_status == 1) {
				//payment made = publish
				oiopub_approve("publish", $check->item_id);
			} elseif(function_exists('wp_update_post')) {
				//reset post to draft and notify user
				wp_update_post(array('post_status' => 'draft', 'ID' => $post_ID, 'no_filter' => true));
				if($check->item_status != 1) {
					oiopub_approve("approve", $check->item_id);
				}
				header('Location: ' . $oiopub_set->admin_url . '/admin.php?page=oiopub-manager.php&opt=post&pub=' . $post_ID);
				exit();
			}
		}
	}

}

?>