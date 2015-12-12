<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

//install
class oiopub_install {

	//database tables
	function install_db() {
		global $oiopub_db, $oiopub_set, $oiopub_tables;
		//table structures
		$sql1 = "CREATE TABLE IF NOT EXISTS `" . $oiopub_set->dbtable_config . "` (
		`id` int(10) NOT NULL auto_increment,
		`name` varchar(64) NOT NULL default '',
		`value` longtext NOT NULL,
		`api_load` int(10) NOT NULL default '0',
		PRIMARY KEY (`id`),
		KEY `api_load` (`api_load`)
		) ENGINE=MyISAM" . $oiopub_db->default_charset() . ";";
		$sql2 = "CREATE TABLE IF NOT EXISTS `" . $oiopub_set->dbtable_purchases . "` (
		`item_id` int(10) NOT NULL auto_increment,
		`item_channel` int(10) NOT NULL default '0',
		`item_type` int(10) NOT NULL default '0',
		`item_status` int(10) NOT NULL default '0',
		`item_model` varchar(16) NOT NULL default 'days',
		`item_duration` int(10) NOT NULL default '0',
		`item_duration_left` int(10) NOT NULL default '0',
		`item_subscription` int(10) NOT NULL default '0',
		`item_nofollow` int(10) NOT NULL default '0',
		`item_url` varchar(255) NOT NULL default '',
		`item_tooltip` varchar(128) NOT NULL default '',
		`item_page` varchar(255) NOT NULL default '',
		`item_subid` varchar(255) NOT NULL default '',
		`item_notes` text NOT NULL,
		`link_exchange` varchar(255) NOT NULL default '',
		`category_id` int(10) NOT NULL default '0',
		`direct_link` int(10) NOT NULL default '0',
		`post_id` int(10) NOT NULL default '0',
		`post_author` int(10) NOT NULL default '0',
		`post_phrase` varchar(64) NOT NULL default '',
		`adv_name` varchar(128) NOT NULL default '',
		`adv_email` varchar(128) NOT NULL default '',
		`submit_time` bigint(20) NOT NULL default '0',
		`submit_api` int(10) NOT NULL default '0',
		`payment_txid` varchar(32) NOT NULL default '',
		`payment_processor` varchar(32) NOT NULL default '',
		`payment_currency` varchar(8) NOT NULL default '',
		`payment_amount` decimal(10,2) NOT NULL default '0.00',
		`payment_time` bigint(20) NOT NULL default '0',
		`payment_next` bigint(20) NOT NULL default '0',
		`payment_status` int(10) NOT NULL default '0',
		`payment_log` text NOT NULL,
		`coupon` varchar(32) NOT NULL default '',
		`coupon_discount` decimal(10,2) NOT NULL default '0.00',
		`published_status` int(10) NOT NULL default '0',
		`affiliate_id` varchar(16) NOT NULL default '',
		`rand_id` varchar(64) NOT NULL default '',
		PRIMARY KEY (`item_id`),
		KEY `item_channel` (`item_channel`,`item_status`,`payment_status`),
		KEY `rand_id` (`rand_id`)
		) ENGINE=MyISAM" . $oiopub_db->default_charset() . ";";
		$sql3 = "CREATE TABLE IF NOT EXISTS `" . $oiopub_set->dbtable_purchases_history . "` (
		`ID` int(10) NOT NULL auto_increment,
		`item` int(10) NOT NULL default '0',
		`processor` varchar(16) NOT NULL default '',
		`currency` varchar(8) NOT NULL default '',
		`amount` decimal(10,2) NOT NULL default '0.00',
		`time` bigint(20) NOT NULL default '0',
		`subscription` int(10) NOT NULL default '0',
		PRIMARY KEY (`ID`),
		KEY `item` (`item`),
		KEY `time` (`time`)
		) ENGINE=MyISAM" . $oiopub_db->default_charset() . ";";
		$sql4 = "CREATE TABLE IF NOT EXISTS `" . $oiopub_set->dbtable_coupons . "` (
		`id` int(10) NOT NULL auto_increment,
		`code` varchar(32) NOT NULL default '',
		`discount` decimal(10,2) NOT NULL default '0.00',
		`percentage` int(10) NOT NULL default '0',
		`expiry_date` bigint(20) NOT NULL default '0',
		`max_usage` int(10) NOT NULL default '0',
		`times_used` int(10) NOT NULL default '0',
		`type` int(10) NOT NULL default '0',
		`type_sub` varchar(32) NOT NULL default '',
		`status` int(10) NOT NULL default '0',
		PRIMARY KEY (`id`),
		KEY `code` (`code`)
		) ENGINE=MyISAM" . $oiopub_db->default_charset() . ";";
		//execute queries
		$oiopub_db->query($sql1);
		$oiopub_db->query($sql2);
		$oiopub_db->query($sql3);
		$oiopub_db->query($sql4);
		//save table array
		$oiopub_tables = array();
		$oiopub_tables[] = $sql1;
		$oiopub_tables[] = $sql2;
		$oiopub_tables[] = $sql3;
		$oiopub_tables[] = $sql4;
	}

	//config options
	function install_options() {
		global $oiopub_version;
		//get alert data
		$alert = oiopub_file_contents("http://api.oiopublisher.com/2.0/alert.txt");
		$alert = intval($alert);
		$alert = (!empty($alert) ? $alert : 1);
		//add options
		oiopub_add_config('enabled', 1, 1);
		oiopub_add_config('hash', oiopub_rand(16));
		oiopub_add_config('admin_mail', '');
		oiopub_add_config('affiliate_id', 0);
		oiopub_add_config('global_pass', '');
		oiopub_add_config('testmode_payment', 0);
		oiopub_add_config('version', $oiopub_version, 1);
		oiopub_add_config('version_status', 0);
		oiopub_add_config('alert_current', $alert);
		oiopub_add_config('alert_last', $alert);
		oiopub_add_config('template', 'default');
		oiopub_add_config('cron_jobs', '');
		oiopub_add_config('cron_running', 0);
		oiopub_add_config('api_key', '');
		oiopub_add_config('api_valid', '');
		oiopub_add_config('plugin_url_saved', '');
		oiopub_add_config('plugin_rewrite', '');
		oiopub_add_config('disclosure', '');
		oiopub_add_config('rules', '');
		oiopub_add_config('feedback', '');
		oiopub_add_config('theme_mode', 0);
		oiopub_add_config('general_set', array( "currency"=>'USD', "currency_symbol"=>'', "paytime"=>0, "thickbox"=>3, "subscription"=>0, "postlinks"=>0, "new_window"=>0, "upload"=>1, "buypage"=>'', "security_question"=>1, "edit_ads"=>0 ), 1);
		oiopub_add_config('posts', array( "price_adv"=>0, "price_blogger"=>0, "price_free"=>0, "min_words"=>100, "max_posts_num"=>2, "max_posts_days"=>1, "tags"=> 0 ), 1);
		oiopub_add_config('inline_ads', array( "enabled"=>0, "selection"=>2, "price"=>array(0), "duration"=>array(0), "width"=>250, "height"=>200, "rotator"=>1, "queue"=>0, "showposts"=>2, "position"=>"right", "reuse"=>0, "showfeed"=>0, "nofollow"=>1, "nfboost"=>0, "template"=>'title-date', "defnum"=>1 ), 1);
		oiopub_add_config('inline_defaults', array());
		oiopub_add_config('inline_links', array( "enabled"=>0, "price"=>array(0), "duration"=>array(0), "max"=>3, "nofollow"=>1, "nfboost"=>0 ), 1);		
		oiopub_add_config('links_zones', 1, 1);
		oiopub_add_config('banners_zones', 1, 1);
		oiopub_add_config('custom_num', 1, 1);
		oiopub_add_config('coupons', array( 'enabled'=>0 ));
		oiopub_add_config('demographics', array( 'enabled'=>0 ));
		$this->install_zone('link', 1);
		$this->install_zone('banner', 1);
		$this->install_zone('custom', 1);
	}

	//install ad zone
	function install_zone($type, $id) {
		if($type == "link") {
			oiopub_add_config('links_'.$id, array( 'enabled'=>1, 'title'=>"Zone $id", 'list'=>1, 'price'=>array(0), 'duration'=>array(0), 'cols'=>1, 'rows'=>5, 'width'=>0, 'height'=>0, 'desc_length'=>0, 'rotator'=>1, 'queue'=>0, 'nofollow'=>1, 'nfboost'=>0, 'def_text'=>"", 'advertise_here'=>1, 'def_num'=>1, 'def_method'=>0, 'link_exchange'=>"", 'cats'=>0 ), 1);
			oiopub_add_config('links_'.$id.'_defaults', array());
		}
		if($type == "banner") {
			oiopub_add_config('banners_'.$id, array( 'enabled'=>1, 'title'=>"Zone $id", 'price'=>array(0), 'duration'=>array(0), 'cols'=>2, 'rows'=>2, 'width'=>125, 'height'=>125, 'rotator'=>1, "queue"=>0, 'def_image'=>"", 'nofollow'=>1, 'nfboost'=>0, 'spacing'=>10, 'def_num'=>1, 'def_method'=>0, 'link_exchange'=>"", 'cats'=>0 ), 1);
			oiopub_add_config('banners_'.$id.'_defaults', array());
		}
		if($type == "custom") {
			oiopub_add_config('custom_'.$id, array( 'title'=>"Custom Purchase $id", 'price'=>0, 'duration'=>0, 'max'=>0, 'info'=>"", 'download'=>"" ), 1);
		}
	}

	//delete ad zone
	function delete_zone($type, $id) {
		if($type == "link") {
			oiopub_delete_config('links_'.$id);
			oiopub_delete_config('links_'.$id.'_defaults');
		}
		if($type == "banner") {
			oiopub_delete_config('banners_'.$id);
			oiopub_delete_config('banners_'.$id.'_defaults');
		}
		if($type == "custom") {
			oiopub_delete_config('custom_'.$id);
		}
	}

	//cron jobs
	function install_cron() {
		global $oiopub_cron;
		$oiopub_cron->add_job(array(&$oiopub_cron, 'version_check'), time()+28800, 86400);
		$oiopub_cron->add_job(array(&$oiopub_cron, 'purchase_expire'), time()+3600, 7200);
	}

	//mail data
	function mail_data() {
		$this->submit_subject = "%item% request received at %site_name%";
		$this->submit_message = "Dear %username%,||Your request for a %item% at %site_name% has been received.||Title: %item_title%|Cost: %item_cost%||Your ad dashboard can be accessed using the link below, allowing you to make payment (required before the %item% can be published) and view any available stats:||%stats_url%||%extras%||Thanks,|%site_name%";
		$this->approval_subject = "%item% request approved at %site_name%";
		$this->approval_message = "Dear %username%,||Your request for a %item% at %site_name% has been approved.||Title: %item_title%|Cost: %item_cost%||Your ad dashboard can be accessed using the link below, allowing you to make payment (required before the %item% can be published) and view any available stats:||%stats_url%||%extras%||Thanks,|%site_name%||%feedback%";
		$this->rejection_subject = "%item% request rejected at %site_name%";
		$this->rejection_message = "Dear %username%,||Your request for a %item% at %site_name% has been rejected.||Title: %item_title%|Cost: %item_cost%||If you have already sent payment, you will receive a full refund, minus any transfer costs. For further information, please reply to this e-mail.||%extras%||Thanks,|%site_name%";
		$this->reminder_subject = "%item% at %site_name% - payment required";
		$this->reminder_message = "Dear %username%,||You recently requested a %item% at %site_name%, but payment has yet to be received.||Title: %item_title%|Cost: %item_cost%||You must make payment before your %item% can be published, which you can do using the link below:||%payment_url%||%extras%||Thanks,|%site_name%";
		$this->expiring_subject = "%item% at %site_name% - purchase expiring";
		$this->expiring_message = "Dear %username%,||Your %item% purchase at %site_name% is soon to expire, or has already done so.||Title: %item_title%|Cost: %item_cost%||You can renew your %item% using the link below, as well as view any available stats:||%stats_url%||%extras%||Thanks,|%site_name%";
		$this->renewal_subject = "%item% at %site_name% - purchase renewed";
		$this->renewal_message = "Dear %username%,||You have successfully renewed your %item% at %site_name%.||Title: %item_title%|Cost: %item_cost%||%extras%||Thanks,|%site_name%||%feedback%";	
		$this->published_subject = "%item% at %site_name% - purchase published";
		$this->published_message = "Dear %username%,||The %item% you recently requested at %site_name% has been published, which you can view using the link below:||%preview_url%||%extras%||Thanks,|%site_name%||%feedback%";
		$this->queue_subject = "%item% at %site_name% - purchase now active";
		$this->queue_message = "Dear %username%,||You recently requested a %item% at %site_name%, which was put in the queue for a future opening. The ad is active on the site.||Title: %item_title%|Cost: %item_cost%||%extras%||Thanks,|%site_name%||%feedback%";
		$this->payment_subject = "Payment received for %item% at %site_name%";
		$this->payment_message = "Dear %username%,||Your payment for %item% at %site_name% has been received.||Title: %item_title%|Cost: %item_cost%||%extras%||Thanks,|%site_name%||%feedback%";						
	}
	
	//mail templates
	function mail_templates($request=1) {
		oiopub_update_config('mailsubject_1', $this->submit_subject);
		oiopub_update_config('mailmessage_1', $this->submit_message);
		oiopub_update_config('mailsubject_2', $this->approval_subject);
		oiopub_update_config('mailmessage_2', $this->approval_message);
		oiopub_update_config('mailsubject_3', $this->rejection_subject);
		oiopub_update_config('mailmessage_3', $this->rejection_message);
		oiopub_update_config('mailsubject_4', $this->reminder_subject);
		oiopub_update_config('mailmessage_4', $this->reminder_message);
		oiopub_update_config('mailsubject_5', $this->expiring_subject);
		oiopub_update_config('mailmessage_5', $this->expiring_message);
		oiopub_update_config('mailsubject_6', $this->renewal_subject);
		oiopub_update_config('mailmessage_6', $this->renewal_message);
		oiopub_update_config('mailsubject_7', $this->published_subject);
		oiopub_update_config('mailmessage_7', $this->published_message);
		oiopub_update_config('mailsubject_8', $this->queue_subject);
		oiopub_update_config('mailmessage_8', $this->queue_message);
		oiopub_update_config('mailsubject_9', $this->payment_subject);
		oiopub_update_config('mailmessage_9', $this->payment_message);
	}
	
	//install
	function install() {
		global $oiopub_set;
		//define install
		if(!defined('OIOPUB_INSTALL')) {
			define('OIOPUB_INSTALL', 1);		
		}
		//flush cache
		oiopub_flush_cache();
		//install data
		$this->install_db();
		$this->install_options();
		$this->install_cron();
		$this->mail_data();
		$this->mail_templates(1);
	}
	
	//uninstall
	function uninstall() {
		global $oiopub_set, $oiopub_db, $oiopub_hook;
		//define install
		if(!defined('OIOPUB_UNINSTALL')) {
			define('OIOPUB_UNINSTALL', 1);		
		}
		//flush cache
		oiopub_flush_cache();
		//delete database
		$oiopub_db->query("DROP TABLE IF EXISTS `" . $oiopub_set->dbtable_config . "`");
		$oiopub_db->query("DROP TABLE IF EXISTS `" . $oiopub_set->dbtable_purchases . "`");
		$oiopub_db->query("DROP TABLE IF EXISTS `" . $oiopub_set->dbtable_purchases_history . "`");
		$oiopub_db->query("DROP TABLE IF EXISTS `" . $oiopub_set->dbtable_coupons . "`");
		//delete modules
		$oiopub_hook->fire('delete_modules');
		//deactivate script?
		if(function_exists('oiopub_script_deactivate')) {
			oiopub_script_deactivate();
		}
	}
	
	//upgrade
	function upgrade() {
		global $oiopub_db, $oiopub_set, $oiopub_alerts, $oiopub_version;
		//define upgrade
		if(!defined('OIOPUB_UPGRADE')) {
			define('OIOPUB_UPGRADE', 1);		
		}
		//check version
		if($oiopub_version != "2.60" || !oiopub_is_admin()) {
			return $oiopub_set->version;
		}
		//pre-upgrade screen
		if(!isset($_GET['oio-upgrade'])) {
			$oiopub_alerts->upgrade();
			return;
		}
		//flush cache
		oiopub_flush_cache();
		//install funcs
		$this->install_db();
		$this->install_cron();
		//upgrade me!
		oiopub_upgrade_script("manual");
	}

}

?>