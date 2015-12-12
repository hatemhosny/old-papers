<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

//manual upgrade class
class oiopub_upgrade_manual {

	//upgrade init
	function init() {
		global $oiopub_version;
		//1.xx upgrade
		$this->upgrade_1xx();
		//v1 to v2
		$this->upgrade_v1v2();
		//2.xx upgrade
		$this->upgrade_2xx();
		//update version number
		oiopub_update_config('version', $oiopub_version);
		oiopub_update_config('version_status', 0);
		//redirect user
		$redirect = "admin.php?page=oiopub-opts.php&complete=" . $oiopub_version;
		header("Location: " . $redirect);
		exit();
	}

	//1.xx series upgrade
	function upgrade_1xx() {
		global $oiopub_set, $oiopub_db;
		global $wpdb, $table_prefix;
		if(!isset($wpdb) || !isset($oiopub_set->version)) {
			return;
		}
		if($oiopub_set->version <= '1.04') {
			add_option('oiopub_generalset', 'en|USD|0|3|1|1|0|');
			add_option('oiopub_linkhome','0|0|0|0');
			add_option('oiopub_linksite','0|0|0|0');
			add_option('oiopub_linksingle','0|0|0|0');
			add_option('oiopub_linkcontent','0|0|0|0');
			add_option('oiopub_postinfo', '0|0|0|100|2|1|0');
			add_option('oiopub_vidinfo', '1|250|200|0|0|1|2|1|0|title-date|0');
			add_option('oiopub_service_one', '|0|0|0|');
			add_option('oiopub_service_two', '|0|0|0|');
			add_option('oiopub_service_three', '|0|0|0|');
			add_option('widget_oiopub1', '');
			add_option('widget_oiopub2', '');
			delete_option('oiopub_language');
			delete_option('oiopub_payment_email');
			delete_option('oiopub_currency');
			delete_option('oiopub_paysystem');
			delete_option('oiopub_thickbox');
			delete_option('oiopub_family');
			delete_option('oiopub_nofollow');
			delete_option('oiopub_linkprice_home');
			delete_option('oiopub_linkprice_other');
			delete_option('oiopub_linkprice_site');
			delete_option('oiopub_linkprice_content');
			delete_option('oiopub_linktime');
			delete_option('oiopub_linkmax_outside');
			delete_option('oiopub_linkmax_inside');
			delete_option('oiopub_postprice_advertiser');
			delete_option('oiopub_postprice_blogger');
			delete_option('oiopub_postprice_free');
			delete_option('oiopub_postwords_min');
			delete_option('oiopub_postday_max');
			delete_option('oiopub_version_time');
		}
		if($oiopub_set->version <= '1.05') {
			add_option('oiopub_api_key', '');
			add_option('oiopub_api_valid', '');
		}
		if($oiopub_set->version <= '1.11') {
			$posts_table = $table_prefix . "oiopub_posts";
			$oiopub_db->query("UPDATE " . $posts_table . " SET published_status='1' WHERE item_status='1' AND payment_status='1'");
		}
		if($oiopub_set->version <= '1.12') {
			add_option('oiopub_affiliates', '0|0|20|14');
		}
		if($oiopub_set->version <= '1.22') {
			$update = get_option('oiopub_vidinfo');
			$update = $update . "|0|title-date";
			update_option('oiopub_vidinfo', $update);
			$posts_table = $table_prefix . "oiopub_posts";
			$items = $oiopub_db->GetAll("SELECT * FROM " . $posts_table);
			if(!empty($items)) {
				foreach($items as $item) {
					$oiopub_db->query("INSERT INTO " . $oiopub_set->dbtable_purchases . " (item_channel,item_status,post_id,post_author,adv_name,adv_email,submit_time,submit_api,payment_txid,payment_processor,payment_currency,payment_amount,payment_time,payment_status,published_status,affiliate_id,rand_id)
								VALUES ('1','$item->post_status','$item->post_id','$item->post_author','$item->adv_name','$item->adv_email','$item->submit_time','$item->submit_api','$item->payment_txid','$item->payment_processor','$item->payment_currency','$item->payment_amount','$item->payment_time','$item->payment_status','$item->published_status','$item->affiliate_id','$item->rand_id')");
				}
			}
			$links_table = $table_prefix . "oiopub_links";
			$items = $oiopub_db->GetAll("SELECT * FROM " . $links_table);
			if(!empty($items)) {
				foreach($items as $item) {
					$oiopub_db->query("INSERT INTO " . $oiopub_set->dbtable_purchases . " (item_channel,item_type,item_status,item_duration,item_subscription,item_url,item_anchor,item_tooltip,item_page,post_id,post_phrase,adv_name,adv_email,submit_time,payment_txid,payment_processor,payment_currency,payment_amount,payment_time,payment_status,affiliate_id,rand_id)
								VALUES ('2','$item->link_type','$item->link_status','$item->link_duration','$item->link_subscription','$item->link_url','$item->link_anchor','$item->link_tooltip','$item->link_page','$item->post_id','$item->post_phrase','$item->adv_name','$item->adv_email','$item->submit_time','$item->payment_txid','$item->payment_processor','$item->payment_currency','$item->payment_amount','$item->payment_time','$item->payment_status','$item->affiliate_id','$item->rand_id')");
				}
			}
			$videos_table = $table_prefix . "oiopub_videos";
			$items = $oiopub_db->GetAll("SELECT * FROM " . $videos_table);
			if(!empty($items)) {
				foreach($items as $item) {
					$oiopub_db->query("INSERT INTO " . $oiopub_set->dbtable_purchases . " (item_channel,item_type,item_status,item_duration,item_subscription,item_url,post_id,adv_name,adv_email,submit_time,payment_txid,payment_processor,payment_currency,payment_amount,payment_time,payment_status,affiliate_id,rand_id)
								VALUES ('3','$item->video_type','$item->video_status','$item->video_duration','$item->video_subscription','$item->video_url','$item->post_id','$item->adv_name','$item->adv_email','$item->submit_time','$item->payment_txid','$item->payment_processor','$item->payment_currency','$item->payment_amount','$item->payment_time','$item->payment_status','$item->affiliate_id','$item->rand_id')");
				}
			}
			$services_table = $table_prefix . "oiopub_services";
			$items = $oiopub_db->GetAll("SELECT * FROM " . $services_table);
			if(!empty($items)) {
				foreach($items as $item) {
					$oiopub_db->query("INSERT INTO " . $oiopub_set->dbtable_purchases . " (item_channel,item_type,item_status,item_duration,item_subscription,item_notes,adv_name,adv_email,submit_time,payment_txid,payment_processor,payment_currency,payment_amount,payment_time,payment_status,affiliate_id,rand_id)
								VALUES ('4','$item->service_type','$item->service_status','$item->service_duration','$item->service_subscription','$item->service_notes','$item->adv_name','$item->adv_email','$item->submit_time','$item->payment_txid','$item->payment_processor','$item->payment_currency','$item->payment_amount','$item->payment_time','$item->payment_status','$item->affiliate_id','$item->rand_id')");
				}
			}
			$oiopub_db->query("DROP TABLE IF EXISTS " . $posts_table);
			$oiopub_db->query("DROP TABLE IF EXISTS " . $links_table);
			$oiopub_db->query("DROP TABLE IF EXISTS " . $videos_table);
			$oiopub_db->query("DROP TABLE IF EXISTS " . $services_table);
		}
		if($oiopub_set->version <= '1.23') {
			add_option('oiopub_banner_one', '0|0|2|2|125|125|1|');
			$oiopub_db->query("ALTER TABLE ". $oiopub_set->dbtable_purchases . " MODIFY rand_id varchar(32)");
			$oiopub_db->query("ALTER TABLE ". $oiopub_set->dbtable_purchases . " DROP INDEX rand_id");
			$oiopub_db->query("ALTER TABLE ". $oiopub_set->dbtable_purchases . " ADD INDEX rand_id (rand_id)");
		}
		if($oiopub_set->version <= '1.30') {
			$update = get_option('oiopub_banner_one');
			$update = $update . "|1|10|0";
			update_option('oiopub_banner_one', $update);
			delete_option('oiopub_folder');
			$oiopub_db->query("ALTER TABLE ". $oiopub_set->dbtable_purchases . " ADD item_nofollow int(10) NOT NULL default '0'");
		}
		if($oiopub_set->version <= '1.31') {
			$update = get_option('oiopub_linkposition')."|0";
			update_option('oiopub_linkposition', $update);
		}
		if($oiopub_set->version <= '1.32') {
			$exp = explode("|", get_option('oiopub_vidinfo'));
			$oiopub_db->query("UPDATE ". $oiopub_set->dbtable_purchases . " SET item_type='" . $exp[6] . "' WHERE item_channel='3'");
			$update = $exp[6] . "|" . $exp[0] . "|"  .$exp[1] . "|" . $exp[2] . "|" . $exp[3] . "|" . $exp[9] . "|2|1|0|" . $exp[8];
			update_option('oiopub_vidinfo', $update);
		}
		if($oiopub_set->version <= '1.33') {
			$oiopub_db->query("ALTER TABLE ". $oiopub_set->dbtable_purchases . " ADD payment_log text NOT NULL");
		}
		if($oiopub_set->version <= '1.42') {
			$oiopub_db->query("ALTER TABLE ". $oiopub_set->dbtable_purchases . " ADD item_nofollow int(10) NOT NULL default '0'");
			$oiopub_db->query("ALTER TABLE ". $oiopub_set->dbtable_purchases . " ADD payment_log text NOT NULL");
		}
		if($oiopub_set->version <= '1.43') {
			add_option('oiopub_banner_zones', '1');
			add_option('oiopub_custom_num', '3');
			$update = get_option('oiopub_service_one') . "\n" . get_option('oiopub_service_two') . "\n" . get_option('oiopub_service_three');
			update_option('oiopub_service_one', $update);
			delete_option('oiopub_service_two');
			delete_option('oiopub_service_three');
		}
		if($oiopub_set->version <= '1.51') {
			add_option('oiopub_banner_defaults', '');
			add_option('oiopub_inline_defaults', '');
		}
		if($oiopub_set->version <= '1.53') {
			add_option('oiopub_alert_status', '1|0');
		}
	}
		
	//convert v1 to v2
	function upgrade_v1v2() {
		global $oiopub_set, $oiopub_db;
		global $wpdb;
		if(!isset($oiopub_set->version)) {
			return;
		}
		if($oiopub_set->version <= '1.99' && isset($wpdb)) {
			//1.xx to 2.xx (db tables)
			$oiopub_db->query("UPDATE " . $oiopub_set->dbtable_purchases . " SET item_channel='3' WHERE item_channel='2' AND item_type='4'");
			$oiopub_db->query("UPDATE " . $oiopub_set->dbtable_purchases . " SET item_page=item_anchor, item_type='1' WHERE item_channel='2'");
			$oiopub_db->query("ALTER TABLE ". $oiopub_set->dbtable_purchases . " DROP item_anchor");
			//1.xx to 2.xx (config data)
			$generalset_exp = explode("|", get_option('oiopub_generalset'));
			$ilinks_exp = explode("|", get_option('oiopub_linkcontent'));
			$linkpos_exp = explode("|", get_option('oiopub_linkposition'));
			$posts_exp = explode("|", get_option('oiopub_postinfo'));
			$inline_exp = explode("|", get_option('oiopub_vidinfo'));
			$banners_exp = explode("\n", get_option('oiopub_banner_one'));
			$custom_exp = explode("\n", get_option('oiopub_service_one'));
			$banner_defaults = oiopub_unserialize(get_option('oiopub_banner_defaults'));
			$banner_zones = get_option('oiopub_banner_zones');
			$custom_num = get_option('oiopub_custom_num');
			//get alert data
			$alert = oiopub_file_contents("http://api.oiopublisher.com/2.0/alert.txt");
			$alert = intval($alert);
			$alert = (!empty($alert) ? $alert : 1);
			//add new options
			oiopub_add_config('enabled', 1, 1);
			oiopub_add_config('hash', oiopub_rand(16));
			oiopub_add_config('global_pass', $generalset_exp[8]);
			oiopub_add_config('testmode_payment', get_option('oiopub_testmode_payment'));
			oiopub_add_config('version', "2.00", 1);
			oiopub_add_config('version_status', 0);
			oiopub_add_config('alert_current', $alert);
			oiopub_add_config('alert_last', $alert);
			oiopub_add_config('template', 'default');
			oiopub_add_config('cron_jobs', '');
			oiopub_add_config('cron_running', 0);
			oiopub_add_config('api_key', get_option('oiopub_api_key'));
			oiopub_add_config('api_valid', get_option('oiopub_api_valid'));
			oiopub_add_config('plugin_url_saved', '');
			oiopub_add_config('plugin_rewrite', '');
			oiopub_add_config('disclosure', get_option('oiopub_disclosure'));
			oiopub_add_config('rules', get_option('oiopub_rules'));
			oiopub_add_config('feedback', get_option('oiopub_feedback'));
			oiopub_add_config('admin_mail', get_option('admin_email'));
			oiopub_add_config('general_set', array("currency"=>$generalset_exp[1], "paytime"=>$generalset_exp[2], "thickbox"=>$generalset_exp[3], "family"=>$generalset_exp[4], "subscription"=>$generalset_exp[5], "postlinks"=>$generalset_exp[6], "new_window"=>0, "upload"=>1, "buypage"=>$generalset_exp[7]), 1);
			oiopub_add_config('links_zones', 1, 1);
			oiopub_add_config('links_1', array("list"=>0, "price"=>0, "duration"=>0, "width"=>0, "height"=>0, "cols"=>1, "rows"=>5, "desc_length"=>0, "rotator"=>1, "def_text"=>'', "nofollow"=>1, "nfboost"=>0, "title"=>'Zone 1', "def_num"=>0, "queue"=>0, "def_method"=>0, "desc_length"=>0), 1);
			oiopub_add_config('links_1_defaults', '');
			oiopub_add_config('banners_zones', $banner_zones, 1);
			if($banner_zones > 0) {
				for($z=0; $z < $banner_zones; $z++) {
					$y = $z + 1;
					$exp = explode("|", $banners_exp[$z]);
					if(!is_array($banner_defaults[$y])) {
						$banner_defaults[$y] = array();
					}
					oiopub_add_config('banners_'.$y, array("price"=>$exp[0], "duration"=>$exp[1], "cols"=>$exp[2], "rows"=>$exp[3], "width"=>$exp[4], "height"=>$exp[5], "rotator"=>$exp[6], "def_image"=>$exp[7], "nofollow"=>$exp[8], "spacing"=>$exp[9], "nfboost"=>$exp[10], "title"=>(empty($exp[11]) ? "Zone 1" : $exp[11]), "def_num"=>$exp[12], "queue"=>$exp[13], "def_method"=>$exp[14]), 1);
					oiopub_add_config('banners_'.$y.'_defaults', $banner_defaults[$y]);
				}
			} else {
				oiopub_add_config('banners_1', array("price"=>0, "duration"=>0, "cols"=>2, "rows"=>2, "width"=>125, "height"=>125, "rotator"=>1, "def_image"=>'', "nofollow"=>1, "spacing"=>10, "nfboost"=>0, "title"=>'Zone 1', "def_num"=>0, "queue"=>0, "def_method"=>0), 1);
				oiopub_add_config('banners_1_defaults', '');
				oiopub_update_config('banners_zones', 1);
			}
			oiopub_add_config('inline_ads', array("selection"=>$inline_exp[0], "width"=>$inline_exp[1], "height"=>$inline_exp[2], "price"=>$inline_exp[3], "duration"=>$inline_exp[4], "rotator"=>$inline_exp[5], "showposts"=>$inline_exp[6], "nofollow"=>$inline_exp[7], "nfboost"=>$inline_exp[8], "template"=>$inline_exp[9], "defnum"=>$inline_exp[10], "queue"=>$inline_exp[11], "showfeed"=>$inline_exp[12], "reuse"=>$inline_exp[13], "position"=>"right"), 1);
			oiopub_add_config('inline_defaults', get_option('oiopub_inline_defaults'));
			oiopub_add_config('inline_links', array("price"=>$ilinks_exp[0], "duration"=>$ilinks_exp[1], "max"=>$ilinks_exp[2], "nofollow"=>$ilinks_exp[3], "nfboost"=>$linkpos_exp[1]), 1);
			oiopub_add_config('posts', array("price_adv"=>$posts_exp[0], "price_blogger"=>$posts_exp[1], "price_free"=>$posts_exp[2], "min_words"=>$posts_exp[3], "max_posts_num"=>$posts_exp[4], "max_posts_days"=>$posts_exp[5], "tags"=>$posts_exp[6]), 1);
			oiopub_add_config('custom_num', $custom_num, 1);
			if($custom_num > 0) {
				for($z=0; $z < $custom_num; $z++) {
					$y = $z + 1;
					$exp = explode("|", $custom_exp[$z]);
					oiopub_add_config('custom_'.$y, array("title"=>$exp[0], "price"=>$exp[1], "duration"=>$exp[2], "max"=>$exp[3], "info"=>$exp[4], "download"=>$exp[5]), 1);
				}
			} else {
				oiopub_add_config('custom_1', array("title"=>'Custom 1', "price"=>0, "duration"=>0, "max"=>0, "info"=>'', "download"=>''), 1);
				oiopub_update_config('custom_num', 1);
			}
			//affiliates module
			$module = get_option('oiopub_affiliates');
			if(!empty($module)) {
				$exp = explode("|", $module);
				oiopub_update_config('affiliates', array("install"=>0, "enabled"=>$exp[0], "fixed"=>$exp[1], "level"=>$exp[2], "maturity"=>$exp[3], "aff_url"=>$oiopub_set->site_url . "/"));
			}
			//paypal module
			$module = get_option('oiopub_paypal');
			if(!empty($module)) {
				$exp = explode("|", $module);
				if(!empty($exp[1])) {
					$valid = 1;
				} else {
					$valid = 0;
				}
				oiopub_update_config('paypal', array("install"=>0, "enable"=>$valid, "valid"=>$valid, "mail"=>$exp[1]));
			}
			//socialposts module
			$module = get_option('oiopub_conversation');
			if(!empty($module)) {
				$exp = explode("|", $module);
				oiopub_update_config('socialposts', array("install"=>0, "testmode"=>$exp[1], "moderation"=>$exp[2], "nofollow"=>$exp[3], "notice"=>$exp[4]));
				$groups = explode("\n", get_option('oiopub_conversation_groups'));
				$groups_count = count($groups);
				$groups_array = array();
				if($groups_count > 0) {
					for($z=0; $z < $groups_count; $z++) {
						$exp = explode("|", $groups[$z]);
						$groups_array[$exp[0]] = $exp[1];
					}
				}
				oiopub_update_config('socialposts_groups', $groups_array);
				oiopub_update_config('socialposts_search', "");
				$css = "#social-notice{margin-top:20px; margin-bottom:20px;}\n#social-response h2{margin-top:10px; margin-bottom:15px;}\n#social-response{margin-top:30px; margin-bottom:30px;}\n.social-background{padding:10px; border:1px solid #999; background:#F0FFFF;}\n.social-reply{margin-bottom:15px;}\n.social-title{margin-bottom:2px;}\n.social-excerpt{}";
				oiopub_update_config('socialposts_css', $css);
			}
			//tracker module
			$module = get_option('oiopub_tracker');
			if(!empty($module)) {
				$exp = explode("|", $module);
				oiopub_update_config('tracker', array("install"=>0, "enabled"=>$exp[1], "reports"=>$exp[3], "share"=>$exp[4], "ip_filter"=>'blacklist', "agent_filter"=>'blacklist', "referer_filter"=>'blacklist'));
				oiopub_update_config('tracker_cron', get_option('oiopub_tracker_cron'));
				oiopub_add_config('ip_filter_data', array());
				oiopub_add_config('agent_filter_data', array());
				oiopub_add_config('referer_filter_data', array());
			}
			//viralblogads module
			$module = get_option('oiopub_viralblogads');
			if(!empty($module)) {
				$exp = explode("|", $module);
				oiopub_update_config('viralblogads', array("install"=>0, "signup_initial"=>$exp[1], "signup_complete"=>$exp[2], "clientid"=>$exp[3]));
			}
			//reset mail templates
			oiopub_mail_templates(2);
			//delete WP options
			$oiopub_db->query("DELETE FROM $wpdb->options WHERE option_name LIKE 'oiopub%'");
		}
	}

	//2.xx series upgrade
	function upgrade_2xx() {
		global $oiopub_set, $oiopub_db, $oiopub_cron, $oiopub_version;
		if(!isset($oiopub_set->version)) {
			return;
		}
		if($oiopub_set->version <= '2.00.b2') {
			//update version
			oiopub_update_config('version', "2.01");
			//reset mail templates
			oiopub_mail_templates(2);
		}
		if($oiopub_set->version <= '2.01') {
			//update version
			oiopub_update_config('version', "2.02");
			//update purchase tables
			$oiopub_db->query("ALTER TABLE " . $oiopub_set->dbtable_purchases . " MODIFY payment_amount decimal(10,2) NOT NULL default '0.00'");
			$oiopub_db->query("ALTER TABLE " . $oiopub_set->dbtable_purchases_history . " MODIFY amount decimal(10,2) NOT NULL default '0.00'");
			//update affiliate sales table
			if(!empty($oiopub_set->dbtable_affiliates_sales)) {
				$oiopub_db->query("ALTER TABLE " . $oiopub_set->dbtable_affiliates_sales . " MODIFY affiliate_amount decimal(10,2) NOT NULL default '0.00'");
			}
		}
		if($oiopub_set->version <= '2.05') {
			//update version
			oiopub_update_config('version', "2.06");
			//flush old cache dir
			oiopub_flush_cache($oiopub_set->cache_dir);
		}
		if($oiopub_set->version <= '2.07') {
			//update version
			oiopub_update_config('version', "2.08");
			//update link options
			for($z=1; $z <= $oiopub_set->links_zones; $z++) {
				$lz = "links_" . $z;
				if($oiopub_set->{$lz}['cols'] == 1) {
					$oiopub_set->{$lz}['list'] = 1;
					$oiopub_set->{$lz}['height'] = 0;
				} else {
					$oiopub_set->{$lz}['list'] = 0;
					$oiopub_set->{$lz}['height'] = 0;
				}
				oiopub_update_config($lz, $oiopub_set->{$lz});
			}
		}
		if($oiopub_set->version <= '2.08') {
			//update version
			oiopub_update_config('version', "2.10.b1");
			//text ads (multi-pricing)
			for($z=1; $z <= $oiopub_set->links_zones; $z++) {
				$lz = "links_" . $z;
				if($oiopub_set->{$lz}['price'] > 0) {
					$oiopub_set->{$lz}['enabled'] = 1;
				} else {
					$oiopub_set->{$lz}['enabled'] = 0;
				}
				$oiopub_set->{$lz}['price'] = array($oiopub_set->{$lz}['price']);
				$oiopub_set->{$lz}['duration'] = array($oiopub_set->{$lz}['duration']);
				oiopub_update_config($lz, $oiopub_set->{$lz});
			}
			//banner ads (multi-pricing)
			for($z=1; $z <= $oiopub_set->banners_zones; $z++) {
				$bz = "banners_" . $z;
				if($oiopub_set->{$bz}['price'] > 0) {
					$oiopub_set->{$bz}['enabled'] = 1;
				} else {
					$oiopub_set->{$bz}['enabled'] = 0;
				}
				$oiopub_set->{$bz}['price'] = array($oiopub_set->{$bz}['price']);
				$oiopub_set->{$bz}['duration'] = array($oiopub_set->{$bz}['duration']);
				oiopub_update_config($bz, $oiopub_set->{$bz});
			}
			//inline ads (multi-pricing)
			if($oiopub_set->inline_ads['price'] > 0) {
				$oiopub_set->inline_ads['enabled'] = 1;
			} else {
				$oiopub_set->inline_ads['enabled'] = 0;
			}
			$oiopub_set->inline_ads['price'] = array($oiopub_set->inline_ads['price']);
			$oiopub_set->inline_ads['duration'] = array($oiopub_set->inline_ads['duration']);
			oiopub_update_config("inline_ads", $oiopub_set->inline_ads);
			//inline links (multi-pricing)
			if($oiopub_set->inline_links['price'] > 0) {
				$oiopub_set->inline_links['enabled'] = 1;
			} else {
				$oiopub_set->inline_links['enabled'] = 0;
			}
			$oiopub_set->inline_links['price'] = array($oiopub_set->inline_links['price']);
			$oiopub_set->inline_links['duration'] = array($oiopub_set->inline_links['duration']);
			oiopub_update_config("inline_links", $oiopub_set->inline_links);
		}
		if($oiopub_set->version <= '2.10.b1') {
			//update version
			oiopub_update_config('version', "2.10.b2");
			//update cron tasks
			oiopub_update_config('cron_running', 0);
			//text ads (advertise here)
			for($z=1; $z <= $oiopub_set->links_zones; $z++) {
				$lz = "links_" . $z;
				$oiopub_set->{$lz}['advertise_here'] = 1;
				oiopub_update_config($lz, $oiopub_set->{$lz});
			}
		}
		if($oiopub_set->version <= '2.10.b2') {
			//update version
			oiopub_update_config('version', "2.10.b3");
			//add link exchange column
			$oiopub_db->query("ALTER TABLE " . $oiopub_set->dbtable_purchases . " ADD link_exchange varchar(255) NOT NULL default ''");
		}
		if($oiopub_set->version <= '2.11') {
			//update version
			oiopub_update_config('version', "2.20");
			//add extra purchase columns
			$oiopub_db->query("ALTER TABLE " . $oiopub_set->dbtable_purchases . " ADD category_id int(10) NOT NULL default '0'");
			$oiopub_db->query("ALTER TABLE " . $oiopub_set->dbtable_purchases . " ADD direct_link int(10) NOT NULL default '0'");
			$oiopub_db->query("OPTIMIZE TABLE " . $oiopub_set->dbtable_config);
		}
		if($oiopub_set->version <= '2.25') {
			//update version
			oiopub_update_config('version', "2.26");
			//delete old file
			@unlink($oiopub_set->folder_dir . "/fs_test.php");
			//set demographics config
			oiopub_update_config('demographics', array( 'install' => "2.00", 'enabled' => 1 ));
		}
		if($oiopub_set->version <= '2.26') {
			$oiopub_db->query("ALTER TABLE ". $oiopub_set->dbtable_purchases . " ADD item_subid varchar(255) NOT NULL default ''");
		}
		if($oiopub_set->version <= '2.32') {
			//set new options
			oiopub_update_config('coupons', array( 'enabled'=>0 ));
			//add purchase columns
			$oiopub_db->query("ALTER TABLE " . $oiopub_set->dbtable_purchases . " ADD coupon varchar(16) NOT NULL default ''");
			$oiopub_db->query("ALTER TABLE " . $oiopub_set->dbtable_purchases . " ADD coupon_discount decimal(10,2) NOT NULL default '0.00'");
		}
		if($oiopub_set->version <= '2.49') {
			//add purchase columns
			$oiopub_db->query("ALTER TABLE " . $oiopub_set->dbtable_purchases . " ADD item_model varchar(16) NOT NULL default 'days'");
			$oiopub_db->query("ALTER TABLE " . $oiopub_set->dbtable_purchases . " ADD item_duration_left int(10) NOT NULL default '0'");
			//update purchases data
			$oiopub_db->query("UPDATE " . $oiopub_set->dbtable_purchases . " SET item_model='days', item_duration_left=item_duration");
			//update settings
			$oiopub_set->general_set['security_question'] = 1;
			oiopub_update_config('general_set', $oiopub_set->general_set);
			//refresh cron
			$oiopub_cron->refresh_all();
		}
	}

}

//auto upgrade class
class oiopub_upgrade_auto {

	var $fs;
	var $backups_dir;

	//init upgrade
	function init() {
		global $oiopub_set;
		//no gzip
		@ini_set('zlib.output_compression', 'Off');
		//auto-upgrade requested?
		if(!isset($_GET['oiopub-auto-upgrade'])) {
			return;
		}
		//access allowed?
		if(!oiopub_auth_check() || !oiopub_is_admin()) {
			return;
		}
		//backups dir
		if(empty($oiopub_set->backups_folder)) {
			oiopub_update_config('backups_folder', oiopub_rand(8) . "-oiopub-backups");
		}
		//set variables
		$this->backups_dir = $oiopub_set->parent_dir . "/" . $oiopub_set->backups_folder;
		$this->fs = oiopub_filesystem($this->backups_dir . "/");
		if(empty($this->fs->file_system)) {
			return;
		}
		//flush cache
		oiopub_flush_cache();
		//start process
		$res = true;
		$res = $this->backup_script($res);
		$res = $this->download_update($res);
		$res = $this->extract_update($res);
		$res = $this->copy_dirs($res);
		$res = $this->rename_update($res);
		$res = $this->complete($res);
		return $res;
	}
	
	//backup script
	function backup_script($res) {
		global $oiopub_set;
		if(!$res) return false;
		echo "<div id='upgrade-complete'>\n";
		echo "<b>Step One:</b> backup existing script\n";
		echo "<br /><br />\n";
		echo "Zipping up backup into '" . $oiopub_set->backups_folder . "' folder...\n";
		oiopub_flush();
		if(!@is_dir($this->backups_dir)) {
			$this->fs->make_dir($this->backups_dir, 0777);
		}
		if(!@is_writable($this->backups_dir)) {
			//backups dir not writable
			echo "<br /><br /><br />\n";
			echo "<font color='red'><b>Upgrade Aborted</b></font> - '" . $oiopub_set->backups_folder . "' directory not writable. This directory must be created in the same folder that contains the '" . $oiopub_set->folder_name . "' directory.\n";
			$this->fs->delete_file($this->backups_dir . "/oiopub-update.zip");
			return false;
		}
		if(!@file_exists($this->backups_dir . "/.htaccess")) {
			//htaccess file present?
			$this->fs->copy_file($oiopub_set->folder_dir . "/cache/.htaccess", $this->backups_dir . "/.htaccess");
		}
		if(!@file_exists($this->backups_dir . "/.htaccess")) {
			//htaccess not present
			echo "<br /><br /><br />\n";
			echo "<font color='red'><b>Upgrade Aborted</b></font> - '" . $oiopub_set->backups_folder . "' directory does not contain an htaccess file. Please copy the htaccess file from your '" . $oiopub_set->folder_name . "/cache' folder to the '" . $oiopub_set->backups_folder . "' folder to continue.\n";
			$this->fs->delete_file($this->backups_dir . "/oiopub-update.zip");
			return false;
		}
		$zip_file = $this->backups_dir . "/oiopub-backup-v" . $oiopub_set->version . ".zip";
		if(!$this->fs->create_archive($zip_file, $oiopub_set->folder_dir)) {
			//zip not saved
			echo "<br /><br /><br />\n";
			echo "<font color='red'><b>Upgrade Aborted</b></font> - backup zip file not successfully created\n";
			$this->fs->delete_file($this->backups_dir . "/oiopub-update.zip");
			return false;
		} else {
			//success
			echo " <b>Done</b>\n";
			oiopub_flush();
		}
		return true;
	}
	
	//download update
	function download_update($res) {
		global $oiopub_set, $oiopub_api;
		if(!$res) return false;
		echo "<br /><br />\n";
		echo "<b>Step Two:</b> download update\n";
		echo "<br /><br />\n";
		echo "Downloading update into '" . $oiopub_set->backups_folder . "' folder...\n";
		oiopub_flush();
		//get data
		$res = "";
		$data = array();
		$data['type'] = "download";
		//make api call
		$api_call = $oiopub_api->auto_upgrade();
		if($api_call == "DENIED") {
			//permission denied
			echo "<br /><br /><br />\n";
			echo "<font color='red'><b>Upgrade Aborted</b></font> - permission to download denied, have you entered your API Key?\n";
			$this->fs->delete_file($this->backups_dir . "/oiopub-update.zip");
			return false;
		} elseif($api_call == "VERSION") {
			//permission denied
			echo "<br /><br /><br />\n";
			echo "<font color='red'><b>Upgrade Aborted</b></font> - you are currently running the latest version of OIOpublisher!\n";
			$this->fs->delete_file($this->backups_dir . "/oiopub-update.zip");
			return false;
		} elseif($api_call == "") {
			//contact not made
			echo "<br /><br /><br />\n";
			echo "<font color='red'><b>Upgrade Aborted</b></font> - could not make contact with OIOpublisher.com (please try again shortly)\n";
			$this->fs->delete_file($this->backups_dir . "/oiopub-update.zip");
			return false;
		} else {
			//write to file
			if($this->fs->make_file($this->backups_dir . "/oiopub-update.zip", $api_call, "w+")) {
				$res = "<b>Done</b>\n";
			}
			if(empty($res)) {
				//zip not saved
				echo "<br /><br /><br />\n";
				echo "<font color='red'><b>Upgrade Aborted</b></font> - could not make contact with OIOpublisher.com (please try again shortly)\n";
				$this->fs->delete_file($this->backups_dir . "/oiopub-update.zip");
				return false;
			} else {
				//success
				echo " " . $res;
				oiopub_flush();
			}
		}
		return true;
	}
	
	//extract update
	function extract_update($res) {
		global $oiopub_set;
		if(!$res) return false;
		echo "<br /><br />\n";
		echo "<b>Step Three:</b> extract download to temporary folder\n";
		echo "<br /><br />\n";
		echo "Extracting update into 'oiopub-direct-new' folder...\n";
		oiopub_flush();
		$zip_file = $this->backups_dir . "/oiopub-update.zip";
		$extract = $this->fs->extract_archive($zip_file);
		if(!is_array($extract)) {
			//incompatible zip file
			echo "<br /><br /><br />\n";
			echo "<font color='red'><b>Upgrade Aborted</b></font> - zip file cannot be extracted\n";
			$this->fs->delete_file($this->backups_dir . "/oiopub-update.zip");
			return false;
		}
		if(oiopub_count($extract) == 0) {
			//empty zip file
			echo "<br /><br /><br />\n";
			echo "<font color='red'><b>Upgrade Aborted</b></font> - zip file was empty\n";
			$this->fs->delete_file($this->backups_dir . "/oiopub-update.zip");
			return false;
		}
		if(!$this->fs->write_archive($extract, $oiopub_set->parent_dir)) {
			//bad extraction
			echo "<br /><br /><br />\n";
			echo "<font color='red'><b>Upgrade Aborted</b></font> - zip file cannot be extracted\n";
			$this->fs->delete_file($this->backups_dir . "/oiopub-update.zip");
			$this->fs->delete_dir($oiopub_set->parent_dir . "/oiopub-direct-new");
			return false;
		}
		//success
		echo " <b>Done</b>\n";
		oiopub_flush();
		return true;
	}
	
	//copy directories
	function copy_dirs($res) {
		global $oiopub_set;
		if(!$res) return false;
		echo "<br /><br />\n";
		echo "<b>Step Four:</b> copying download / upload directories\n";
		echo "<br /><br />\n";
		echo "Copying download / upload directories to 'oiopub-direct-new' folder...\n";
		oiopub_flush();
		//copy folders
		$source = $oiopub_set->folder_dir . "/downloads";
		$dest = $oiopub_set->parent_dir . "/oiopub-direct-new/downloads";
		$this->fs->copy_dir($source, $dest);
		$source = $oiopub_set->folder_dir . "/uploads";
		$dest = $oiopub_set->parent_dir . "/oiopub-direct-new/uploads";
		$this->fs->copy_dir($source, $dest);
		$source = $oiopub_set->folder_dir . "/config.php";
		$dest = $oiopub_set->parent_dir . "/oiopub-direct-new/config.php";
		$this->fs->copy_file($source, $dest);
		//copy lang folder?
		if(is_dir($oiopub_set->folder_dir . "/lang")) {
			$source = $oiopub_set->folder_dir . "/lang";
			$dest = $oiopub_set->parent_dir . "/oiopub-direct-new/lang";
			$this->fs->copy_dir($source, $dest);
		}
		//copy custom templates folder?
		if(is_dir($oiopub_set->folder_dir . "/templates/core_custom")) {
			$source = $oiopub_set->folder_dir . "/templates/core_custom";
			$dest = $oiopub_set->parent_dir . "/oiopub-direct-new/templates/core_custom";
			$this->fs->copy_dir($source, $dest);
		}
		//success
		echo " <b>Done</b>\n";
		oiopub_flush();
		return true;
	}
	
	//rename update
	function rename_update($res) {
		global $oiopub_set;
		if(!$res) return false;
		echo "<br /><br />\n";
		echo "<b>Step Five:</b> initiate new update\n";
		echo "<br /><br />\n";
		echo "Initiating new update in '" . $oiopub_set->folder_name . "' folder...\n";
		oiopub_flush();
		//rename folders
		$this->fs->rename($oiopub_set->folder_dir, $oiopub_set->folder_dir . "-old");
		$this->fs->rename($oiopub_set->parent_dir . "/oiopub-direct-new", $oiopub_set->folder_dir);
		$this->fs->delete_file($this->backups_dir . "/oiopub-update.zip");
		$this->fs->delete_dir($oiopub_set->folder_dir . "-old");
		//chmod folders
		$this->fs->chmod($oiopub_set->folder_dir . "/cache", 0777);
		$this->fs->chmod($oiopub_set->folder_dir . "/uploads", 0777);
		$this->fs->chmod($oiopub_set->folder_dir, 0755);
		$this->fs->chmod($oiopub_set->parent_dir, 0755);
		//success
		echo " <b>Done</b>\n";
		oiopub_flush();
		return true;
	}
	
	//complete
	function complete($res) {
		global $oiopub_set;
		if(!$res) return false;
		$errors = "";
		echo "</div>\n";
		echo "<style type='text/css'>#upgrade-complete{display:none;}</style>\n";
		echo "<h3>Upgrade complete!</h3>\n";
		echo "A backup of the previous script has been saved in the '" . $oiopub_set->backups_folder . "' folder in case you need to downgrade for any reason.\n";
		if(@is_dir($oiopub_set->parent_dir . "/" . $oiopub_set->folder_name . "-old")) {
			//old directory still present
			$errors .= "<br />\n";
			$errors .= "* Delete the old script folder called '" . $oiopub_set->folder_name . "-old'\n";
		}
		if(!empty($errors)) {
			echo "<br /><br />\n";
			echo "<b>Please do the following to continue:</b>\n";
			echo "<br />\n";
			echo $errors;
		}
		$request = str_replace(array("?oiopub-auto-upgrade=1", "&amp;oiopub-auto-upgrade=1"), array("", ""), $oiopub_set->request_uri);
		echo "<br /><br />\n";
		echo "<a href='" . $request . "'><b>Click here to Finish</b></a>\n";
		return true;
	}

}

?>