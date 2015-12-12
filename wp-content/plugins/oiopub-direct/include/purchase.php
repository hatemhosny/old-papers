<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();


/* WRAPPER CLASS */

class oiopub_purchase {

	var $item;
	var $input;
	var $channel;
	var $type;
	var $child_class;

	var $redirect;
	var $api_type;
	var $expire_code;

	var $free_item = 0;
	var $auto_approve = 0;
	var $auto_publish = 0;
	var $post_max_words = 0;

	var $obj_type = 'insert';

	var $allowed_exts = array('gif', 'jpg', 'jpeg', 'png', 'swf');

	//constructor
	function oiopub_purchase() {
		global $oiopub_set;
		//demo mode?
		if($oiopub_set->demo) {
			$this->auto_approve = 1;
		}
	}

	//init
	function init($input_data='', $item_channel='', $item_type='') {
		//any input data?
		if(empty($input_data)) {
			return false;
		}
		//get vars
		$this->input = $input_data;
		$this->channel = intval($item_channel);
		$this->type = intval($item_type);
		//get child class
		$type_check = "oiopub_purchase_" . oiopub_type_check($this->channel);
		//does class exist?
		if(class_exists($type_check)) {
			$this->child_class = new $type_check($this);
			return true;
		}
		return false;
	}
	
	//insert purchase
	function insert($redirect=1, $api_type=0) {
		global $oiopub_set, $oiopub_db, $oiopub_hook;
		//get vars
		$this->redirect = intval($redirect);
		$this->api_type = intval($api_type);
		//process data
		$this->data();
		//process errors
		$this->errors();
		//errors found?
		if(!$this->item->misc['error']) {
			//post insert?
			if($this->item->item_channel == 1) {
				if(function_exists('oiopub_insert_post')) {
					$this->item->post_id = oiopub_insert_post($this->item->post);
				}
			}
			//unset vars
			unset($this->item->post, $this->item->misc);
			//pre-purchase hook
			$oiopub_hook->fire('pre_purchase', array(&$this->item));
			//item cost
			$this->item->payment_amount = number_format($this->item->payment_amount, 2, ".", "");
			$this->item->item_duration_left = $this->item->item_duration;
			//item subID
			$this->item->item_subid = oiopub_subid($this->channel, $this->type);
			//build arrays
			$array1 = array();
			$array2 = array();
			foreach($this->item as $key => $val) {
				if(!is_array($val)) {
					$array1[] = $key;
					$array2[] = "'" . $oiopub_db->escape(stripslashes($val)) . "'";
				}
			}
			//sql insert
			$oiopub_db->query("INSERT INTO " . $oiopub_set->dbtable_purchases . " (" . implode(',', $array1) . ") VALUES (" . implode(',', $array2) . ")");
			//get item ID
			$this->item->item_id = intval($oiopub_db->insert_id);
			//db success?
			if($this->item->item_id > 0) {
				//post-purchase hook
				$oiopub_hook->fire('post_purchase', array(&$this->item));
				//log submission
				oiopub_approve("submit", $this->item->item_id);
				//publish submission?
				if($this->auto_publish == 1 && $this->item->item_channel == 1) {
					oiopub_approve("publish", $this->item->item_id);
				}
				//redirect user
				$this->redirect($this->item->rand_id);
				//done
				return true;
			} else {
				//failed
				echo __oio("There was an error making the submission. Please try again!");
				die();
			}
		}
		return $this->item;
	}

	//update purchase
	function update($rand_id, $redirect=1, $api_type=0) {
		global $oiopub_set, $oiopub_db, $oiopub_hook;
		//sanitize ID
		$rand_id = oiopub_clean($rand_id);
		//get purchase data
		if(!$data = $oiopub_db->GetRow("SELECT * FROM " . $oiopub_set->dbtable_purchases . " WHERE rand_id='" . $rand_id . "' LIMIT 1")) {
			return false;
		}
		//set class vars
		$this->redirect = intval($redirect);
		$this->api_type = intval($api_type);
		$this->channel = $data->item_channel;
		$this->type = $data->item_type;
		$this->obj_type = $data->item_status == 3 ? 'renew' : 'edit';
		//process data
		$this->data($data);
		//process errors
		$this->errors();
		//save to database?
		if(!$this->item->misc['error']) {
			//unset vars
			unset($this->item->post, $this->item->misc, $this->item->item_id, $this->item->rand_id);
			//pre-renewal hook?
			if($data->item_status == 3) {
				$oiopub_hook->fire('pre_renew', array(&$this->item));		
			}
			//build sql query
			$set_data = array();
			//loop through data
			foreach($this->item as $key => $val) {
				$set_data[] = $key . "='" . $oiopub_db->escape(stripslashes($val)) . "'";
			}
			//execute query
			$oiopub_db->query("UPDATE " . $oiopub_set->dbtable_purchases . " SET " . implode(', ', $set_data) . " WHERE rand_id='" . $rand_id . "' LIMIT 1");
			//post-renewal hook?
			if($data->item_status == 3) {
				$oiopub_hook->fire('post_renew', array(&$this->item));				
			}
			//redirect user
			$this->redirect($rand_id);
		}
		//return
		return $this->item;
	}

	//renew purchase (legacy)
	function renew($temp_data, $expire_code='', $redirect=1, $api_type=0) {
		return $this->update($expire_code, $redirect, $api_type);
	}

	//input data
	function data($data=array()) {
		global $oiopub_set;
		//general data
		$this->item->misc['info'] = '';
		$this->item->misc['error'] = false;
		$this->item->misc['security'] = md5(md5($this->input['oio_security']) . $oiopub_set->hash);
		//loop through data
		foreach($data as $key => $val) {
			$this->item->$key = $val;
		}
		//over-ride data
		$this->item->item_type = $this->type;
		$this->item->item_channel = $this->channel;
		$this->item->submit_api = $this->api_type;
		$this->item->item_page = oiopub_clean($this->input['oio_page']);
		$this->item->item_tooltip = oiopub_clean($this->input['oio_tooltip']);
		$this->item->item_notes = stripslashes(strip_tags($this->input['oio_notes']));
		$this->item->adv_name = oiopub_clean($this->input['oio_name']);
		$this->item->link_exchange = oiopub_clean($this->input['oio_exchange']);
		$this->item->adv_email = strtolower(oiopub_clean($this->input['oio_email']));
		//banner upload?
		if($this->input['oio_url']) {
			$this->item->item_url = oiopub_clean($this->input['oio_url']);
		}
		//insert / renew only
		if($this->obj_type != 'edit') {
			$this->item->payment_time = 0;
			$this->item->payment_status = 0;
			$this->item->category_id = 0;
			$this->item->payment_log = "";
			$this->item->item_model = "days";
			$this->item->submit_time = time();
			//auto-approve item?
			if($this->auto_approve == 1 || $this->api_type == 2) {
				$this->item->item_status = 1;
			} else {
				$this->item->item_status = 0;
			}
		}
		//set initial payment options?
		if($this->item->payment_status != 1) {
			$this->item->payment_processor = oiopub_clean($this->input['oio_paymethod']);
			$this->item->item_nofollow = intval($this->input['oio_nofollow']);
		}
		//child class data
		$this->item = $this->child_class->data($this->item, $this->input);
		//set payment options?
		if($this->item->payment_status != 1) {
			//free item?
			if($this->obj_type == 'renew') {
				//check for test mode
				if($oiopub_set->testmode_payment == 1 || $this->free_item == 1) {
					$this->item->item_status = 1;
					$this->item->payment_status = 1;
					$this->item->payment_time = time();
				} else {
					$this->item->item_status = 3;
					$this->item->payment_status = 0;
					$this->item->payment_time = -1;
				}
			} else {
				//check for test mode
				if($oiopub_set->testmode_payment == 1 || $this->free_item == 1) {
					$this->item->payment_time = time();
					$this->item->payment_status = 1;
				}
			}
			//payment currency
			if($this->free_item == 0) {
				//currency selection method?
				if($this->item->payment_processor && isset($oiopub_set->{$this->item->payment_processor}) && isset($oiopub_set->{$this->item->payment_processor}['currency'])) {
					//payment processor over-ride
					$this->item->payment_currency = $oiopub_set->{$this->item->payment_processor}['currency'];
				} else {
					//default currency
					$this->item->payment_currency = $this->api_type == 2 ? oiopub_clean($this->input['oio_currency']) : $oiopub_set->general_set['currency'];
				}
			}
			//use subscription?
			if($this->input['oio_subscription'] == 1 && $oiopub_set->general_set['subscription'] == 1 && $this->item->item_duration > 0) {
				$this->item->item_subscription = 1;
			} else {
				$this->item->item_subscription = 0;
			}
		}
	}

	//input errors
	function errors() {
		global $oiopub_set;
		//general errors
		if(strlen($this->item->adv_name) < 6) {
			$this->item->misc['error'] = true;
			$this->item->misc['info'] .= "<li class='error'>" . __oio("Please enter your full name") . "</li>";
			$this->item->misc['api'] .= "<error>INVALID-NAME</error>\n";
		}
		if(!oiopub_validate_email($this->item->adv_email)) {
			$this->item->misc['error'] = true;
			$this->item->misc['info'] .= "<li class='error'>" . __oio("Invalid email address entered") . "</li>";
			$this->item->misc['api'] .= "<error>INVALID-EMAIL</error>\n";
		}
		if($this->free_item == 0 && $this->item->payment_amount <= 0 && $this->item->payment_status != 1) {
			$this->item->misc['error'] = true;
			$this->item->misc['info'] .= "<li class='error'>" . __oio("Please select a valid pricing option") . "</li>";
			$this->item->misc['api'] .= "<error>INVALID-PRICE</error>\n";
		}
		if($this->free_item == 0 && $this->item->payment_status != 1) {
			if(empty($this->item->payment_processor) || empty($oiopub_set->arr_payment[strtolower($this->item->payment_processor)])) {
				$this->item->misc['error'] = true;
				$this->item->misc['info'] .= "<li class='error'>" . __oio("Please select a valid payment method") . "</li>";
				$this->item->misc['api'] .= "<error>INVALID-PROCESSOR</error>\n";
			}
		}
		//child class data
		$this->item = $this->child_class->errors($this->item);
		//security question
		if($this->item->submit_api == 0 && $oiopub_set->general_set['security_question'] == 1) {
			if(!$this->item->misc['security'] || $this->item->misc['security'] != $_SESSION['next']) {		
				$this->item->misc['error'] = true;
				$this->item->misc['info'] .= "<li class='error'>" . __oio("You did not answer the security question correctly") . "</li>";
			}
		}
		//reset payment status?
		if($this->item->misc['error'] && $this->free_item == 1 && $this->item->payment_status == 1) {
			$this->item->payment_status = 0;
		}
	}
	
	//image upload
	function image_upload($item, $exts=array()) {
		global $oiopub_set;
		if($oiopub_set->general_set['upload'] == 1 && ($this->obj_type == 'insert' || $_FILES['oio_url']['name'])) {
			$item->item_url = "";
			if($_FILES['oio_url']['tmp_name']) {
				include_once($oiopub_set->folder_dir . "/include/upload.php");
				$rand = oiopub_rand(6) . "_";
				$upload = new oiopub_upload();
				$upload->name = $rand . $_FILES['oio_url']['name'];
				$upload->size = $_FILES['oio_url']['size'];
				//$upload->max_size = 200000;
				$upload->temp_name = $_FILES['oio_url']['tmp_name'];
				$upload->upload_dir = $oiopub_set->folder_dir . "/uploads/";
				$upload->valid_exts = $exts;
				$upload->is_image = true;
				if($item->item_channel == 3) {
					$upload->max_width = $oiopub_set->inline_ads['width'];
					$upload->max_height = $oiopub_set->inline_ads['height'];
				} elseif($item->item_channel == 5) {
					$bz = "banners_" . $item->item_type;
					$upload->max_width = $oiopub_set->{$bz}['width'];
					$upload->max_height = $oiopub_set->{$bz}['height'];
				}
				if($upload->upload()) {
					$item->item_url = $oiopub_set->plugin_url . "/uploads/" . $rand . $_FILES['oio_url']['name'];
				}
			}
		}
		return $item;
	}
	
	//redirect user
	function redirect($rand_id) {
		global $oiopub_set;
		if($this->redirect == 1) {
			if($oiopub_set->testmode_payment == 1 || $this->free_item == 1) {
				//test mode redirect
				header("Location: " . $oiopub_set->plugin_url . "/payment.php?do=success&rand=" . $rand_id);
				exit();
			} elseif($this->item->payment_status == 1) {
				//stats redirect
				header("Location: " . $oiopub_set->plugin_url . "/stats.php?rand=" . $rand_id);
				exit();
			} else {
				//payment redirect
				header("Location: " . $oiopub_set->plugin_url . "/payment.php?rand=" . $rand_id);
				exit();
			}
		}
		return false;
	}
	
	//display chart
	function chart($channel, $color1="#E0EEEE", $color2="#FFFFFF") {
		$channel = intval($channel);
		$type_check = "oiopub_purchase_" . oiopub_type_check($channel);
		if(class_exists($type_check)) {
			$child = new $type_check($this);
			return $child->chart($color1, $color2);
		}
		return false;
	}
	
}


/* POSTS CLASS */


class oiopub_purchase_post {

	var $parent;

	function oiopub_purchase_post(&$parent) {
		$this->parent =& $parent;
	}
		
	function data($item, $input_data) {
		global $oiopub_set;
		//set post data
		$item->post['title'] = oiopub_clean($input_data['oio_title']);
		$item->post['tags'] = oiopub_clean($input_data['oio_tags']);
		$item->post['category'] = intval($input_data['oio_category']);
		$item->post['content'] = $input_data['oio_content'];
		$item->post_author = $item->item_type;
		//generate rand ID?
		if($this->parent->obj_type == 'insert') {
			if($item->submit_api > 0 && strpos($input_data['oio_rand_id'], "p-") !== false) {
				$item->rand_id = oiopub_clean($input_data['oio_rand_id']);
			} else {
				$item->rand_id = 'p-' . oiopub_rand(10);
			}
		}
		//set payment data?
		if($item->payment_status != 1) {
			if($item->submit_api == 2) {
				$item->payment_amount = intval($input_data['oio_price']);
			} else {
				if($item->post_author == 1) {
					$item->payment_amount = $oiopub_set->posts['price_blogger'];
				} elseif($item->post_author == 2) {
					$item->payment_amount = $oiopub_set->posts['price_adv'];
				}
			}
		}
		//is free item?
		if($oiopub_set->posts['price_free'] == 1 && $oiopub_set->posts['price_adv'] == 0 && $item->post_author == 2) {
			$this->parent->free_item = 1;
		}
		return $item;
	}
	
	function errors($item) {
		global $oiopub_set, $oiopub_db;
		$check_rows = 0;
		if($this->parent->obj_type != 'edit') {
			$allowed_time = time() - (86400 * $oiopub_set->posts['max_posts_days']);
			$check_rows = $oiopub_db->GetOne("SELECT COUNT(*) FROM " . $oiopub_set->dbtable_purchases . " WHERE item_channel='$item->item_channel' AND item_status < '2' AND submit_time > $allowed_time");
			if($check_rows >= $oiopub_set->posts['max_posts_num'] && $oiopub_set->posts['max_posts_num'] > 0) {
				$latest_time = $oiopub_db->GetOne("SELECT submit_time FROM " . $oiopub_set->dbtable_purchases . " WHERE item_channel='$item->item_channel' AND item_status < '2' ORDER BY submit_time DESC");
				$new_time = ceil(($latest_time + (86400 * $oiopub_set->posts['max_posts_days']) - time()) / 3600);
				$item->misc['error'] = true;
				$item->misc['info'] .= "<li class='error'>" . __oio("Submission limit active. Next submission opportunity is in %s hour(s)", array( $new_time )) . "</li>\n";
				$item->misc['api'] .= "<error>NOSPACE</error>\n";
			}
		}
		if($item->misc['error'] == false) {
			if($item->post_author < 1 || $item->post_author > 2) {
				$item->misc['error'] = true;
				$item->misc['info'] .= "<li class='error'>" . __oio("Please select a valid post author") . "</li>";
				$item->misc['api'] .= "<error>INVALID-TYPE</error>\n";
			}
		}
		if($item->misc['error'] == false) {
			//get word count
			$word_count = oiopub_count_words($item->post['content']);
			//continue with checks
			if($item->post_author == 1 && $item->payment_amount == 0) {
				$item->misc['error'] = true;
				$item->misc['info'] .= "<li class='error'>" . __oio("Free posts not permitted") . "</li>";
				$item->misc['api'] .= "<error>INVALID-PRICE</error>\n";
			}
			if(strlen($item->post['title']) < 6) {
				$item->misc['error'] = true;
				$item->misc['info'] .= "<li class='error'>" . __oio("Please enter a valid post title") . "</li>";
				$item->misc['api'] .= "<error>INVALID-TITLE</error>\n";
			}
			if($item->post['category'] <= 0) {
				$item->misc['error'] = true;
				$item->misc['info'] .= "<li class='error'>" . __oio("Please select a valid category") . "</li>";
				$item->misc['api'] .= "<error>INVALID-CATEGORY</error>\n";
			}
			if($word_count < $oiopub_set->posts['min_words'] && $item->post_author == 2) {		
				$item->misc['error'] = true;
				$item->misc['info'] .= "<li class='error'>" . __oio("Word Count is only %s, minimum allowed is %s", array( $word_count, $oiopub_set->posts['min_words'] )) . "</li>";
				$item->misc['api'] .= "<error>INVALID-CONTENT</error>\n";
			}
			if($word_count < 20 && $item->post_author == 1) {
				$item->misc['error'] = true;
				$item->misc['info'] .= "<li class='error'>" . __oio("Please enter a proper description of the post you want written") . "</li>";
				$item->misc['api'] .= "<error>INVALID-CONTENT</error>\n";
			}
			if($word_count > $this->parent->post_max_words && $this->parent->post_max_words > 0) {
				$item->misc['error'] = true;
				$item->misc['info'] .= "<li class='error'>" . __oio("The maximum word limit for this post is %s", array( $this->parent->post_max_words )) . "</li>";
				$item->misc['api'] .= "<error>INVALID-CONTENT</error>\n";
			}
		}
		return $item;
	}
	
	function chart($color1, $color2) {
		global $oiopub_set, $oiopub_db;
		$background = $color1;
		$total_slots = __oio("%s every %s days", array( $oiopub_set->posts['max_posts_num'], $oiopub_set->posts['max_posts_days'] ));
		$display = "<tr><td width='200' class='left'><b>" . __oio("Type") . "</b></td><td width='150' class='middle'><b>" . __oio("# Available") . "</b></td><td class='right'><b>" . __oio("Pricing") . "</b></td></tr>\n";
		if($oiopub_set->posts['price_adv'] > 0 || $oiopub_set->posts['price_free'] > 0) {
			$display .= "<tr style='background:$background;'>";
			$display .= "<td><a href='purchase.php?do=post&author=2'>" . __oio("Written by you") . "</a></td>";
			$display .= "<td>" . $total_slots . "</td>";
			if($oiopub_set->posts['price_adv'] > 0) {
				$display .= "<td>" . oiopub_amount($oiopub_set->posts['price_adv']) . "</td>";
			} else {
				$display .= "<td>" . __oio("Free Submission") . "</td>";
			}
			$display .= "</tr>\n";
			if($background == $color1) {
				$background = $color2;
			} else {
				$background = $color1;
			}
		}
		if($oiopub_set->posts['price_blogger'] > 0) {
			$display .= "<tr style='background:$background;'>";
			$display .= "<td><a href='purchase.php?do=post&author=1'>" . __oio("Written by us") . "</a></td>";
			$display .= "<td>" . $total_slots . "</td>";
			$display .= "<td>" . oiopub_amount($oiopub_set->posts['price_blogger']) . "</td>";
			$display .= "</tr>\n";
			if($background == $color1) {
				$background = $color2;
			} else {
				$background = $color1;
			}
		}
		return $display;
	}	
	

}


/* LINKS CLASS */


class oiopub_purchase_link {

	var $parent;

	function oiopub_purchase_link(&$parent) {
		$this->parent =& $parent;
	}
	
	function data($item, $input_data) {
		global $oiopub_set;
		$lz = "links_" . $item->item_type;
		//generate rand ID?
		if($this->parent->obj_type == 'insert') {
			if($item->submit_api > 0 && strpos($input_data['oio_rand_id'], "l-") !== false) {
				$item->rand_id = oiopub_clean($input_data['oio_rand_id']);
			} else {
				$item->rand_id = 'l-' . oiopub_rand(10);
			}
		}
		//set payment data?
		if($item->payment_status != 1) {
			$item->payment_amount = $oiopub_set->{$lz}['price'][($input_data['oio_pricing']-1)];
			$item->item_duration = $oiopub_set->{$lz}['duration'][($input_data['oio_pricing']-1)];
			if($oiopub_set->{$lz}['nofollow'] == 2 && $item->item_nofollow == 0) {
				$boost = 1 + ($oiopub_set->{$lz}['nfboost'] / 100);
				$item->payment_amount = $item->payment_amount * $boost;
			}
			if(isset($oiopub_set->{$lz}['model'])) {
				$item->item_model = $oiopub_set->{$lz}['model'];
			}
		}
		//set notes?
		if($oiopub_set->{$lz}['desc_length'] <= 0) {
			$item->item_notes = "";
		}
		//is free item?
		if(!empty($oiopub_set->{$lz}['link_exchange']) && $item->payment_amount == 0) {
			$this->parent->free_item = 1;
		}
		//set category?
		if($oiopub_set->{$lz}['cats'] == 1) {
			$item->category_id = intval($input_data['oio_category']);
		}
		return $item;
	}
	
	function errors($item) {
		global $oiopub_set, $oiopub_db;
		$check_rows = 0;
		$lz = "links_" . $item->item_type;
		if($this->parent->obj_type != 'edit') {
			if($oiopub_set->{$lz}['price'][0] > 0) {
				$allowed = $oiopub_set->{$lz}['rows'] * $oiopub_set->{$lz}['cols'] * $oiopub_set->{$lz}['rotator'];
				$allowed_queue = $allowed + $oiopub_set->{$lz}['queue'];
				$check_rows = $oiopub_db->GetOne("SELECT COUNT(*) FROM " . $oiopub_set->dbtable_purchases . " WHERE item_channel='$item->item_channel' AND item_type='$item->item_type' AND item_status < '2'");
			}
			if($check_rows >= $allowed && $check_rows > 0) {
				if($this->auto_approve == 1) {
					$item->item_status = -1;
				} else {
					$item->item_status = -2;
				}
			}
			if($check_rows > 0 && $check_rows >= $allowed_queue) {
				$item->misc['error'] = true;
				$item->misc['info'] .= "<li class='error'>" . __oio("No space currently available") . "</li>";
				$item->misc['api'] .= "<error>NOSPACE</error>\n";
			}
		}
		if($item->misc['error'] === false) {
			if($item->item_type < 1 || $item->item_type > $oiopub_set->links_zones) {
				$item->misc['error'] = true;
				$item->misc['info'] .= "<li class='error'>" . __oio("Please select a valid ad zone") . "</li>";
				$item->misc['api'] .= "<error>INVALID-TYPE</error>\n";
			}
		}
		if($item->misc['error'] === false) {
			if($this->parent->free_item == 1 && !empty($oiopub_set->{$lz}['link_exchange'])) {
				$page_check = @oiopub_file_contents($item->link_exchange);
				$page_check = str_replace('&amp;', '&', $page_check);
				$exchange_url = rtrim($oiopub_set->{$lz}['link_exchange'], '/');
				$exchange_url = str_replace('&amp;', '&', $exchange_url);
				if(empty($page_check) || !preg_match('/href\=[\"\']' . preg_quote($exchange_url, '/') . '/Ui', $page_check)) {
					$item->misc['error'] = true;
					$item->misc['info'] .= "<li class='error'>" . __oio("Unable to find 'My Link' when checking the URL on 'your page'") . "</li>";
					$item->misc['api'] .= "<error>INVALID-EXCHANGE</error>\n";
				}
			}
			if(!oiopub_validate_url($item->item_url)) {
				$item->misc['error'] = true;
				$item->misc['info'] .= "<li class='error'>" . __oio("Please enter a valid URL") . "</li>";
				$item->misc['api'] .= "<error>INVALID-URL</error>\n";
			}
			if(empty($item->item_page)) {
				$item->misc['error'] = true;
				$item->misc['info'] .= "<li class='error'>" . __oio("Please enter some valid anchor text") . "</li>";
				$item->misc['api'] .= "<error>INVALID-PAGE</error>\n";
			}
			if($oiopub_set->{$lz}['desc_length'] > 0 && strlen($item->item_notes) > $oiopub_set->{$lz}['desc_length']) {
				$item->misc['error'] = true;
				$item->misc['info'] .= "<li class='error'>" . __oio("Description text should be no longer than %s characters", array( $oiopub_set->{$lz}['desc_length'] )) . "</li>";		
				$item->misc['api'] .= "<error>INVALID-DESCRIPTION</error>\n";
			}
			if($oiopub_set->{$lz}['cats'] == 1 && $item->category_id <= 0) {
				$item->misc['error'] = true;
				$item->misc['info'] .= "<li class='error'>" . __oio("Please select a valid ad category") . "</li>";
				$item->misc['api'] .= "<error>INVALID-CATEGORY</error>\n";
			}
		}
		return $item;
	}
	
	function chart($color1, $color2) {
		global $oiopub_set, $oiopub_db;
		//set vars
		$background = $color1;
		$title_filter = oiopub_var('filter', 'get');
		$display = "<tr><td width='200' class='left'><b>" . __oio("Zone") . "</b></td><td width='150' class='middle'><b>" . __oio("# Available") . "</b></td><td class='right'><b>" . __oio("Pricing") . "</b></td></tr>\n";
		//get items
		$items = array();
		for($z=1; $z <= $oiopub_set->links_zones; $z++) {
			$zn = "links_" . $z;
			$items[$z] = $oiopub_set->{$zn}['title'];
		}
		//sort
		asort($items);
		//loop through items
		foreach(array_keys($items) as $z) {		
			$lz = "links_" . $z;
			if($oiopub_set->{$lz}['enabled'] == 1) {
				//title filter?
				if($title_filter && stripos($oiopub_set->{$lz}['title'], $title_filter) === false) {
					continue;
				}
				//valid price?
				if($oiopub_set->{$lz}['price'][0] > 0 || !empty($oiopub_set->{$lz}['link_exchange'])) {
					$pricing = array();
					$price_count = count($oiopub_set->{$lz}['price']);
					for($p=0; $p < $price_count; $p++) {
						if($oiopub_set->{$lz}['price'][$p] > 0 || !empty($oiopub_set->{$lz}['link_exchange'])) {
							$amount = empty($oiopub_set->{$lz}['price'][$p]) ? __oio("Link Exchange") : oiopub_amount($oiopub_set->{$lz}['price'][$p]);
							$model = isset($oiopub_set->{$lz}['model']) ? $oiopub_set->{$lz}['model'] : "days";
							$pricing[] = $amount . ($oiopub_set->{$lz}['duration'][$p] == 0 ? "" : "<br /><i>" . __oio("for") . " " . number_format($oiopub_set->{$lz}['duration'][$p], 0) . " " . __oio($model) . "</i>");
						}
					}
					$display .= "<tr style='background:$background;'>";
					$display .= "<td><a href='purchase.php?do=link&zone=" . $z . "'>" . $oiopub_set->{$lz}['title'] . "</a></td>";
					//set vars
					$channel = 2;
					$available_now = oiopub_spots_available($channel, $z, false);
					$available_queue = $available_now + $oiopub_set->queue[$channel][$z];
					$display .= "<td>";
					//can buy now?
					if($available_now > 0) {
						//slots available
						$display .= $available_now . " " . __oio("slot(s)") . "<br /><i>" . __oio("available now") . "</i>";
					} elseif($available_queue > 0) {
						$display .= $available_queue . " " . __oio("slot(s)") . "<br /><i>" . __oio("available in queue") . "</i>";
					} else {
						//sold ut
						$display .= __oio("Sold out");
					}
					$display .= "</td>";
					$display .= "<td>" . @implode("<br />", $pricing) . "</td>";
					$display .= "</tr>\n";
					if($background == $color1) {
						$background = $color2;
					} else {
						$background = $color1;
					}
				}
			}
		}
		return $display;
	}

}


/* INLINE CLASS */


class oiopub_purchase_inline {

	var $parent;

	function oiopub_purchase_inline(&$parent) {
		$this->parent =& $parent;
	}
		
	function data($item, $input_data) {
		global $oiopub_set;
		//generate rand ID?
		if($this->parent->obj_type == 'insert') {
			if($item->submit_api > 0 && strpos($input_data['oio_rand_id'], "v-") !== false) {
				$item->rand_id = oiopub_clean($input_data['oio_rand_id']);
			} else {
				$item->rand_id = 'v-' . oiopub_rand(10);
			}
		}
		//set payment data?
		if($item->payment_status != 1) {
			if($item->item_type == 4) {
				$item->payment_amount = $oiopub_set->inline_links['price'][($input_data['oio_pricing']-1)];
				$item->item_duration = $oiopub_set->inline_links['duration'][($input_data['oio_pricing']-1)];
				if($oiopub_set->inline_links['nofollow'] == 2 && $item->item_nofollow == 0) {
					$boost = 1 + ($oiopub_set->inline_links['nfboost'] / 100);
					$item->payment_amount = $item->payment_amount * $boost;
				}
				if(isset($oiopub_set->inline_links['model'])) {
					$item->item_model = $oiopub_set->inline_links['model'];
				}
			} else {
				$item->payment_amount = $oiopub_set->inline_ads['price'][($input_data['oio_pricing']-1)];
				$item->item_duration = $oiopub_set->inline_ads['duration'][($input_data['oio_pricing']-1)];
				if($oiopub_set->inline_ads['nofollow'] == 2 && $item->item_nofollow == 0) {
					$boost = 1 + ($oiopub_set->inline_ads['nfboost'] / 100);
					$item->payment_amount = $item->payment_amount * $boost;
				}
				if(isset($oiopub_set->inline_ads['model'])) {
					$item->item_model = $oiopub_set->inline_ads['model'];
				}
			}
		}
		//set post data?
		if($item->item_type == 4) {
			$item->post_id = intval($input_data['oio_postid']);
			$item->post_phrase = oiopub_clean($input_data['oio_postphrase']);		
		}
		return $item;
	}
	
	function errors($item) {
		global $oiopub_set, $oiopub_db;
		$check_rows = 0;
		if($item->item_type == 4) {
			if($this->parent->obj_type != 'edit') {
				$check_rows = $oiopub_db->GetOne("SELECT COUNT(*) FROM " . $oiopub_set->dbtable_purchases . " WHERE item_channel='$item->item_channel' AND item_type='$item->item_type' AND item_status < '2' AND post_id='$item->post_id'");
				$max_value = $oiopub_set->inline_links['max'];
				$max_value_queue = $max_value + $oiopub_set->inline_links['queue'];
				if($check_rows >= $max_value && $check_rows > 0) {
					if($this->auto_approve == 1) {
						$item->item_status = -1;
					} else {
						$item->item_status = -2;
					}
				}				
				if($check_rows > 0 && $check_rows >= $max_value_queue) {
					$item->misc['error'] = true;
					$item->misc['info'] .= "<li class='error'>" . __oio("No space currently available") . "</li>";
					$item->misc['api'] .= "<error>NOSPACE</error>\n";	
				}
			}
			if($item->misc['error'] === false) {
				if(!oiopub_validate_url($item->item_url)) {
					$item->misc['error'] = true;
					$item->misc['info'] .= "<li class='error'>" . __oio("Please enter a valid intext link URL") . "</li>";
					$item->misc['api'] .= "<error>INVALID-URL</error>\n";
				}
				if(strlen($item->post_phrase) < 3 || $item->post_phrase == 'keyword') {
					$item->misc['error'] = true;
					$item->misc['info'] .= "<li class='error'>" . __oio("Please enter a valid keyword") . "</li>";
					$item->misc['api'] .= "<error>INVALID-KEYWORD</error>\n";
				} else {
					if(function_exists('oiopub_post_content')) {
						$post_content = oiopub_post_content($item->post_id);
					} else {
						$post_content = array();
					}
					if(empty($post_content)) {
						$item->misc['error'] = true;
						$item->misc['info'] .= "<li class='error'>" . __oio("Please enter a valid post ID") . "</li>";
						$item->misc['api'] .= "<error>INVALID-POST</error>\n";
					} else {
						$num = $oiopub_db->GetOne("SELECT COUNT(*) FROM " . $oiopub_set->dbtable_purchases . " WHERE item_channel='$item->item_channel' AND item_type='$item->item_type' AND item_status < '2' AND post_id='$item->post_id' AND post_phrase='$item->post_phrase'");
						if($num > 0) {
							$item->misc['error'] = true;
							$item->misc['info'] .= "<li class='error'>" . __oio("This keyword has already been linked to in the selected post ID") . "</li>";
							$item->misc['api'] .= "<error>INVALID-KEYWORD</error>\n";
						} else {
							if(!preg_match("/(?!(?:[^<\[]+[>\]]|[^>\]]+<\/a>))\b" . preg_quote($item->post_phrase, '/') . "\b/imsU", $post_content)) {
								$item->misc['error'] = true;
								$item->misc['info'] .= "<li class='error'>" . __oio("The keyword entered does not exist in the selected post ID") . "</li>";
								$item->misc['api'] .= "<error>INVALID-KEYWORD</error>\n";
							}
						}
					}
				}
			}		
		} else {
			if($this->parent->obj_type != 'edit') {
				if($oiopub_set->inline_ads['price'][0] > 0) {
					$check_rows = $oiopub_db->GetOne("SELECT COUNT(*) FROM " . $oiopub_set->dbtable_purchases . " WHERE item_channel='$item->item_channel' AND item_type='" . $oiopub_set->inline_ads['selection'] . "' AND item_status < '2'");
					$allowed_queue = $oiopub_set->inline_ads['rotator'] + $oiopub_set->inline_ads['queue'];
				}
				if($check_rows >= $oiopub_set->inline_ads['rotator'] && $check_rows > 0) {
					if($this->auto_approve == 1) {
						$item->item_status = -1;
					} else {
						$item->item_status = -2;
					}
				}
				if($check_rows > 0 && $check_rows >= $allowed_queue) {
					$item->misc['error'] = true;
					$item->misc['info'] .= "<li class='error'>" . __oio("No space currently available") . "</li>";
					$item->misc['api'] .= "<error>NOSPACE</error>\n";
				}
			}
			if($item->misc['error'] === false) {
				if($item->item_type != $oiopub_set->inline_ads['selection']) {
					$item->misc['error'] = true;
					$item->misc['info'] .= "<li class='error'>" . __oio("Please select a valid ad type") . "</li>";
					$item->misc['api'] .= "<error>INVALID-TYPE</error>\n";
				}
			}
			if($item->misc['error'] === false) {
				if($oiopub_set->inline_ads['selection'] == 2) {
					if($oiopub_set->general_set['upload'] == 0) {
						if(!oiopub_image_url($item->item_url)) {
							$item->misc['error'] = true;
							$item->misc['info'] .= "<li class='error'>" . __oio("Please use a valid %s banner image", array( $oiopub_set->inline_ads['width'] . "x" . $oiopub_set->inline_ads['height'] )) . "</i></li>";
							$item->misc['api'] .= "<error>INVALID-URL</error>\n";
						}
					} else {
						$item = $this->parent->image_upload($item, $this->parent->allowed_exts);
						if(empty($item->item_url)) {
							$item->misc['error'] = true;
							$item->misc['info'] .= "<li class='error'>" . __oio("Please use a valid %s banner image", array( $oiopub_set->inline_ads['width'] . "x" . $oiopub_set->inline_ads['height'] )) . "</i></li>";
							$item->misc['api'] .= "<error>INVALID-URL</error>\n";
						}
					}
				}
				if($oiopub_set->inline_ads['selection'] == 1) {
					if(!$item->item_url = oiopub_video_url($item->item_url)) {
						$item->misc['error'] = true;
						$item->misc['info'] .= "<li class='error'>" . __oio("Please enter a valid youtube.com video URL") . "</li>";
						$item->misc['api'] .= "<error>INVALID-URL</error>\n";
					}
				} elseif($oiopub_set->inline_ads['selection'] == 2) {
					if(!oiopub_validate_url($item->item_page)) {
						$item->misc['error'] = true;
						$item->misc['info'] .= "<li class='error'>" . __oio("Please enter a valid Website URL") . "</li>";
						$item->misc['api'] .= "<error>INVALID-PAGE</error>\n";
					}
				} elseif($oiopub_set->inline_ads['selection'] == 3) {
					if(!oiopub_validate_url($item->item_url)) {
						$item->misc['error'] = true;
						$item->misc['info'] .= "<li class='error'>" . __oio("Please enter a valid RSS Feed URL") . "</li>";
						$item->misc['api'] .= "<error>INVALID-URL</error>\n";
					}
				}
			}
		}
		return $item;
	}
	
	function chart($color1, $color2) {
		global $oiopub_set, $oiopub_db;
		$background = $color1;
		$display = "<tr><td width='200' class='left'><b>" . __oio("Type") . "</b></td><td width='150' class='middle'><b>" . __oio("# Available") . "</b></td><td class='right'><b>" . __oio("Pricing") . "</b></td></tr>\n";
		if($oiopub_set->inline_ads['enabled'] == 1) {
			if($oiopub_set->inline_ads['price'][0] > 0) {
				$pricing = array();
				$price_count = count($oiopub_set->inline_ads['price']);
				for($p=0; $p < $price_count; $p++) {
					$model = isset($oiopub_set->inline_ads['model']) ? $oiopub_set->inline_ads['model'] : "days";
					$pricing[] = oiopub_amount($oiopub_set->inline_ads['price'][$p]) . ($oiopub_set->inline_ads['duration'][$p] == 0 ? "" : "<br /><i>" . __oio("for") . " " . number_format($oiopub_set->inline_ads['duration'][$p], 0) . " " . __oio($model) . "</i>");
				}
				$type = $oiopub_set->inline_ads['selection'];
				if($type == 1) $ad_type = __oio("Inline Video Ad");
				if($type == 2) $ad_type = __oio("Inline Banner Ad");
				if($type == 3) $ad_type = __oio("Inline RSS Feed");
				$display .= "<tr style='background:$background;'>";
				$display .= "<td><a href='purchase.php?do=inline&type=" . $type . "'>" . $ad_type . "</a></td>";
				$display .= "<td>" . oiopub_spots_available(3, $type) . " " . __oio("slot(s)") . "</td>";
				$display .= "<td>" . @implode("<br />", $pricing) . "</td>";
				$display .= "</tr>\n";
				if($background == $color1) {
					$background = $color2;
				} else {
					$background = $color1;
				}
			}
		}
		if($oiopub_set->inline_links['enabled'] == 1) {
			if($oiopub_set->inline_links['price'][0] > 0) {
				$pricing = array();
				$price_count = count($oiopub_set->inline_links['price']);
				for($p=0; $p < $price_count; $p++) {
					$model = isset($oiopub_set->inline_links['model']) ? $oiopub_set->inline_links['model'] : "days";
					$pricing[] = oiopub_amount($oiopub_set->inline_links['price'][$p]) . ($oiopub_set->inline_links['duration'][$p] == 0 ? "" : "<br /><i>" . __oio("for") . " " . number_format($oiopub_set->inline_links['duration'][$p], 0) . " " . __oio($model) . "</i>");
				}
				if($oiopub_set->inline_links['max'] > 0) {
					$total_slots = $oiopub_set->inline_links['max'] . " " . __oio("per post");
				} else {
					$total_slots = "Unlimited";
				}
				$display .= "<tr style='background:$background;'>";
				$display .= "<td><a href='purchase.php?do=inline&type=4'>" . __oio("Intext Link") . "</a></td>";
				$display .= "<td>" . $total_slots . "</td>";
				$display .= "<td>" . @implode("<br />", $pricing) . "</td>";
				$display .= "</tr>\n";
				if($background == $color1) {
					$background = $color2;
				} else {
					$background = $color1;
				}
			}
		}
		return $display;
	}

}


/* CUSTOM CLASS */


class oiopub_purchase_custom {

	var $parent;

	function oiopub_purchase_custom(&$parent) {
		$this->parent =& $parent;
	}

	function data($item, $input_data) {
		global $oiopub_set;
		$cn = "custom_" . $item->item_type;
		//generate rand ID?
		if($this->parent->obj_type == 'insert') {
			if($item->submit_api > 0 && strpos($input_data['oio_rand_id'], "s-") !== false) {
				$item->rand_id = oiopub_clean($input_data['oio_rand_id']);
			} else {
				$item->rand_id = 's-' . oiopub_rand(10);
			}
		}
		//set payment data?
		if($item->payment_status != 1) {
			$item->payment_amount = $oiopub_set->{$cn}['price'];
			$item->item_duration = $oiopub_set->{$cn}['duration'];
		}
		//is download?
		if(!empty($oiopub_set->{$cn}['download'])) {
			$item->item_status = 1;
		}
		return $item;
	}
	
	function errors($item) {
		global $oiopub_set, $oiopub_db;
		$check_rows = 0;
		$cn = "custom_" . $item->item_type;
		if($this->parent->obj_type != 'edit') {
			if($oiopub_set->{$cn}['price'] > 0 && $oiopub_set->{$cn}['max'] > 0) {
				$check_rows = $oiopub_db->GetOne("SELECT COUNT(*) FROM " . $oiopub_set->dbtable_purchases . " WHERE item_channel='$item->item_channel' AND item_type='$item->item_type' AND item_status < '2'");
			}
			if($check_rows > 0 && $check_rows >= $oiopub_set->{$cn}['max']) {
				$item->misc['error'] = true;
				$item->misc['info'] .= "<li class='error'>" . __oio("This item is currently not available") . "</li>";
				$item->misc['api'] .= "<error>NOSPACE</error>\n";
			}
		}
		if($item->misc['error'] === false) {
			if($item->item_type < 1 || $item->item_type > $oiopub_set->custom_num) {
				$item->misc['error'] = true;
				$item->misc['info'] .= "<li class='error'>" . __oio("Please select a valid item") . "</li>";
				$item->misc['api'] .= "<error>INVALID-TYPE</error>\n";
			}
		}
		return $item;
	}
	
	function chart($color1, $color2) {
		global $oiopub_set, $oiopub_db;
		//set vars
		$background = $color1;
		$title_filter = oiopub_var('filter', 'get');
		$display = "<tr><td width='200' class='left'><b>" . __oio("Item") . "</b></td><td width='150' class='middle'><b>" . __oio("# Available") . "</b></td><td class='right'><b>" . __oio("Pricing") . "</b></td></tr>\n";
		//get items
		$items = array();
		for($z=1; $z <= $oiopub_set->custom_num; $z++) {
			$zn = "custom_" . $z;
			$items[$z] = $oiopub_set->{$zn}['title'];
		}
		//sort
		asort($items);
		//loop through items
		foreach(array_keys($items) as $z) {	
			$cn = "custom_" . $z;
			//title filter?
			if($title_filter && stripos($oiopub_set->{$cn}['title'], $title_filter) === false) {
				continue;
			}
			//valid price?
			if($oiopub_set->{$cn}['price'] > 0) {
				if($oiopub_set->{$cn}['max'] > 0) {
					$total_slots = oiopub_spots_available(4, $z);
				} else {
					$total_slots = __oio("Unlimited");
				}
				$display .= "<tr style='background:$background;'>";
				$display .= "<td><a href='purchase.php?do=custom&item=" . $z . "'>" . $oiopub_set->{$cn}['title'] . "</a></td>";
				$display .= "<td>" . $total_slots . "</td>";
				$display .= "<td>" . oiopub_amount($oiopub_set->{$cn}['price']) . ($oiopub_set->{$cn}['duration'] == 0 ? "" : "<br /><i>" . __oio("for") . " " . number_format($oiopub_set->{$cn}['duration'], 0) . " " . __oio("days") . "</i>") . "</td>";
				$display .= "</tr>\n";
				if(!empty($oiopub_set->{$cn}['info'])) {
					if($background == $color1) {
						$background = $color2;
					} else {
						$background = $color1;
					}
					$display .= "<tr style='background:$background;'>";
					$display .= "<td colspan='4'><b>" . __oio("Details") . ":</b> <i>" . stripslashes($oiopub_set->{$cn}['info']) . "</i></td>";
					$display .= "</tr>\n";
				}
				if($background == $color1) {
					$background = $color2;
				} else {
					$background = $color1;
				}
			}
		}
		return $display;
	}

}


/* BANNERS CLASS */


class oiopub_purchase_banner {

	var $parent;

	function oiopub_purchase_banner(&$parent) {
		$this->parent =& $parent;
	}
		
	function data($item, $input_data) {
		global $oiopub_set;
		$bz = "banners_" . $item->item_type;
		//generate rand ID?
		if($this->parent->obj_type == 'insert') {
			if($item->submit_api > 0 && strpos($input_data['oio_rand_id'], "b-") !== false) {
				$item->rand_id = oiopub_clean($input_data['oio_rand_id']);
			} else {
				$item->rand_id = 'b-' . oiopub_rand(10);
			}
		}
		//set payment data?
		if($item->payment_status != 1) {
			$item->payment_amount = $oiopub_set->{$bz}['price'][($input_data['oio_pricing']-1)];
			$item->item_duration = $oiopub_set->{$bz}['duration'][($input_data['oio_pricing']-1)];
			if($oiopub_set->{$bz}['nofollow'] == 2 && $item->item_nofollow == 0) {
				$boost = 1 + ($oiopub_set->{$bz}['nfboost'] / 100);
				$item->payment_amount = $item->payment_amount * $boost;
			}
			if(isset($oiopub_set->{$bz}['model'])) {
				$item->item_model = $oiopub_set->{$bz}['model'];
			}
		}
		//set category?
		if($oiopub_set->{$bz}['cats'] == 1) {
			$item->category_id = intval($input_data['oio_category']);
		}
		//is free item?
		if(!empty($oiopub_set->{$bz}['link_exchange']) && $item->payment_amount == 0) {
			$this->parent->free_item = 1;
		}
		return $item;
	}
	
	function errors($item) {
		global $oiopub_set, $oiopub_db;
		$check_rows = 0;
		$bz = "banners_" . $item->item_type;
		if($this->parent->obj_type != 'edit') {
			if($oiopub_set->{$bz}['price'][0] > 0) {
				$allowed = $oiopub_set->{$bz}['rows'] * $oiopub_set->{$bz}['cols'] * $oiopub_set->{$bz}['rotator'];
				$allowed_queue = $allowed + $oiopub_set->{$bz}['queue'];
				$check_rows = $oiopub_db->GetOne("SELECT COUNT(*) FROM " . $oiopub_set->dbtable_purchases . " WHERE item_channel='$item->item_channel' AND item_type='$item->item_type' AND item_status < '2'");
			}
			if($check_rows > 0 && $check_rows >= $allowed) {
				if($this->auto_approve == 1) {
					$item->item_status = -1;
				} else {
					$item->item_status = -2;
				}
			}
			if($check_rows > 0 && $check_rows >= $allowed_queue) {
				$item->misc['error'] = true;
				$item->misc['info'] .= "<li class='error'>" . __oio("No space currently available") . "</li>";
				$item->misc['api'] .= "<error>NOSPACE</error>\n";
			}
		}
		if($item->misc['error'] === false) {
			if($item->item_type < 1 || $item->item_type > $oiopub_set->banners_zones) {
				$item->misc['error'] = true;
				$item->misc['info'] .= "<li class='error'>" . __oio("Please select a valid ad zone") . "</li>";
				$item->misc['api'] .= "<error>INVALID-TYPE</error>\n";
			}
		}
		if($item->misc['error'] === false) {
			if($this->parent->free_item == 1 && !empty($oiopub_set->{$bz}['link_exchange'])) {
				$page_check = @oiopub_file_contents($item->link_exchange);
				$page_check = str_replace('&amp;', '&', $page_check);
				$exchange_url = rtrim($oiopub_set->{$bz}['link_exchange'], '/');
				$exchange_url = str_replace('&amp;', '&', $exchange_url);
				if(empty($page_check) || !preg_match('/href\=[\"\']' . preg_quote($exchange_url, '/') . '/Ui', $page_check)) {
					$item->misc['error'] = true;
					$item->misc['info'] .= "<li class='error'>" . __oio("Unable to find 'My Link' when checking the URL on 'your page'") . "</li>";
					$item->misc['api'] .= "<error>INVALID-EXCHANGE</error>\n";
				}
			}
			if($oiopub_set->general_set['upload'] == 0) {
				if(!oiopub_image_url($item->item_url)) {
					$item->misc['error'] = true;
					$item->misc['info'] .= "<li class='error'>" . __oio("Please use a valid %s banner image", array( $oiopub_set->{$bz}['width'] . "x" . $oiopub_set->{$bz}['height'] )) . "</i></li>";
					$item->misc['api'] .= "<error>INVALID-URL</error>\n";
				}
			} else {
				$item = $this->parent->image_upload($item, $this->parent->allowed_exts);
				if(empty($item->item_url)) {
					$item->misc['error'] = true;
					$item->misc['info'] .= "<li class='error'>" . __oio("Please use a valid %s banner image", array( $oiopub_set->{$bz}['width'] . "x" . $oiopub_set->{$bz}['height'] )) . "</i></li>";
					$item->misc['api'] .= "<error>INVALID-URL</error>\n";
				}
			}
			if(!oiopub_validate_url($item->item_page)) {
				$item->misc['error'] = true;
				$item->misc['info'] .= "<li class='error'>" . __oio("Please enter a valid Website URL") . "</li>";
				$item->misc['api'] .= "<error>INVALID-PAGE</error>\n";
			}
			if($oiopub_set->{$bz}['cats'] == 1 && $item->category_id <= 0) {
				$item->misc['error'] = true;
				$item->misc['info'] .= "<li class='error'>" . __oio("Please select a valid ad category") . "</li>";
				$item->misc['api'] .= "<error>INVALID-CATEGORY</error>\n";
			}
		}
		return $item;
	}
	
	function chart($color1, $color2) {
		global $oiopub_set, $oiopub_db;
		//set vars
		$background = $color1;
		$title_filter = oiopub_var('filter', 'get');
		$display = "<tr><td width='200' class='left'><b>" . __oio("Zone") . "</b></td><td width='150' class='middle'><b>" . __oio("# Available") . "</b></td><td class='right'><b>" . __oio("Pricing") . "</b></td></tr>\n";
		//get items
		$items = array();
		for($z=1; $z <= $oiopub_set->banners_zones; $z++) {
			$zn = "banners_" . $z;
			$items[$z] = $oiopub_set->{$zn}['title'];
		}
		//sort
		asort($items);
		//loop through items
		foreach(array_keys($items) as $z) {
			$bz = "banners_" . $z;
			if($oiopub_set->{$bz}['enabled'] == 1) {
				//title filter?
				if($title_filter && stripos($oiopub_set->{$bz}['title'], $title_filter) === false) {
					continue;
				}
				//valid price?
				if($oiopub_set->{$bz}['price'][0] > 0 || !empty($oiopub_set->{$bz}['link_exchange'])) {
					$pricing = array();
					$price_count = count($oiopub_set->{$bz}['price']);
					for($p=0; $p < $price_count; $p++) {
						if($oiopub_set->{$bz}['price'][$p] > 0 || !empty($oiopub_set->{$bz}['link_exchange'])) {
							$amount = empty($oiopub_set->{$bz}['price'][$p]) ? __oio("Banner Exchange") : oiopub_amount($oiopub_set->{$bz}['price'][$p]);
							$model = isset($oiopub_set->{$bz}['model']) ? $oiopub_set->{$bz}['model'] : "days";
							$pricing[] = $amount . ($oiopub_set->{$bz}['duration'][$p] == 0 ? "" : "<br /><i>" . __oio("for") . " " . number_format($oiopub_set->{$bz}['duration'][$p], 0) . " " . __oio($model) . "</i>");
						}
					}
					$display .= "<tr style='background:$background;'>";
					$display .= "<td><a href='purchase.php?do=banner&zone=" . $z . "'>" . $oiopub_set->{$bz}['title'] . "</a><br /><i>" . $oiopub_set->{$bz}['width'] . "x" . $oiopub_set->{$bz}['height'] . " " . __oio("size") . "</i></td>";
					//set vars
					$channel = 5;
					$available_now = oiopub_spots_available($channel, $z, false);
					$available_queue = $available_now + $oiopub_set->queue[$channel][$z];
					$display .= "<td>";
					//can buy now?
					if($available_now > 0) {
						//slots available
						$display .= $available_now . " " . __oio("slot(s)") . "<br /><i>" . __oio("available now") . "</i>";
					} elseif($available_queue > 0) {
						$display .= $available_queue . " " . __oio("slot(s)") . "<br /><i>" . __oio("available in queue") . "</i>";
					} else {
						//sold ut
						$display .= __oio("Sold out");
					}
					$display .= "</td>";
					$display .= "<td>" . @implode("<br />", $pricing) . "</td>";
					$display .= "</tr>\n";
					if($background == $color1) {
						$background = $color2;
					} else {
						$background = $color1;
					}
				}
			}
		}
		return $display;
	}

}