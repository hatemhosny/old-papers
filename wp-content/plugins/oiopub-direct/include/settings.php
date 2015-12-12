<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

//settings
class oiopub_settings {
	
	//init
	function oiopub_settings() {
		global $oiopub_db, $oiopub_set;
		//already defined vars
		foreach($oiopub_set as $key => $val) {
			$this->$key = $val;
		}
		//db tables
		$this->dbtable_config = $this->prefix . "oiopub_config";
		$this->dbtable_purchases = $this->prefix . "oiopub_purchases";
		$this->dbtable_purchases_history = $this->prefix . "oiopub_purchases_history";
		$this->dbtable_coupons = $this->prefix . "oiopub_coupons";
		//misc settings
		$this->is_ssl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] && $_SERVER['HTTPS'] != 'off') || ($_SERVER['SERVER_PORT'] == 443);
		$this->plugin_url = rtrim($this->plugin_url, "/");
		$this->host_name = "http" . ($this->is_ssl ? 's' : '') . "://" . oiopub_clean($_SERVER['HTTP_HOST']);
		$this->request_uri = oiopub_clean($_SERVER['REQUEST_URI']);
		$this->query_string = oiopub_clean($_SERVER['QUERY_STRING']);
		$this->client_ip = oiopub_clean($_SERVER['REMOTE_ADDR']);
		$this->page_url = $this->host_name . $this->request_uri;
		$this->modules_dir = $this->folder_dir . "/modules";
		$this->lang_dir = $this->folder_dir . "/lang";
		$this->platform_dir = $this->folder_dir . "/platform/" . $this->platform;
		$this->platform_file = $this->platform_dir . ".php";
		$this->visitor_country = '';
		$this->lang = 'en';
		$this->grand_total = 0;
		$this->banners_total = 0;
		$this->links_total = 0;
		$this->inline_total = 0;
		$this->posts_total = 0;
		$this->custom_total = 0;
		//check IP proxies
		foreach(array( 'HTTP_CLIENT_IP', 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR' ) as $var) {
			if(isset($_SERVER[$var]) && $_SERVER[$var]) {
				$exp = array_map('trim', explode(",", $_SERVER[$var]));
				$this->client_ip = oiopub_clean($exp[0]);
				break;
			}
		}
		//misc arrays
		$this->arr_payment = array();
		$this->arr_currency = array( "AUD", "EUR", "GBP", "USD" );
		$this->arr_status = array( 1=>"Current", 2=>"Pending", 3=>"Queued", 4=>"Rejected", 5=>"Expired", 6=>"All" );
		$this->arr_yesno = array( 0 => "No", "Yes" );
		$this->arr_nofollow = array( 0 => "No", "Yes" , "User Choice" );
		$this->arr_days = array( 1 => "1", "2", "3", "4", "5", "6", "7", "100" );
		//db settings
		$data = $oiopub_db->CacheGetAll("SELECT * FROM " . $this->dbtable_config);
		if(is_array($data) && $oiopub_db->num_rows > 0) {
			foreach($data as $k=>$v) {
				$name = $v->name;
				$v->value = oiopub_unserialize($v->value);
				$this->$name = oiopub_stripslashes($v->value);
				unset($data[$k]);
			}
		}
		//allow theme over-ride
		if(isset($this->template)) {
			$theme = oiopub_var('theme', 'get');
			$this->template = empty($theme) ? $this->template : $theme;
		} else {
			$this->template = "default";
		}
		//template paths
		$this->template_header = $this->folder_dir . "/templates/" . $this->template . "/header.tpl";
		$this->template_footer = $this->folder_dir . "/templates/" . $this->template . "/footer.tpl";
		//oiopub_url
		if(isset($this->affiliate_id)) {
			$this->oiopub_url = !empty($this->affiliate_id) ? "http://www.oiopublisher.com/ref.php?u=" . $this->affiliate_id : "http://www.oiopublisher.com";
		}
		//plugin url checks
		if(!empty($this->plugin_url)) {
			if(!isset($this->plugin_url_saved) || $this->plugin_url != $this->plugin_url_saved) {
				if(!defined('OIOPUB_LOAD_LITE')) {
					if(isset($this->plugin_url_saved)) {
						$oiopub_db->query("UPDATE " . $this->dbtable_config . " SET value='$this->plugin_url' WHERE name='plugin_url_saved'");
					} else {
						$oiopub_db->query("INSERT INTO " . $this->dbtable_config . " (name,value) VALUES ('plugin_url_saved','$this->plugin_url')");
					}
				}
				$this->plugin_url_saved = $this->plugin_url;
			}
		}
		//plugin url org
		$this->plugin_url_org = $this->plugin_url_saved;
		//plugin url rewrite
		$rewrite = isset($this->plugin_rewrite) ? $this->plugin_rewrite : '';
		$this->plugin_url = empty($rewrite) ? $this->plugin_url_saved : $rewrite;
		//set payment url
		$this->pay_url = $this->plugin_url . '/payment.php';
		//links totals
		if(isset($this->links_zones) && $this->links_zones > 0) {
			for($z=0; $z < $this->links_zones; $z++) {
				$zone = $z+1;
				$lz = "links_" . $zone;
				$this->spots[2][$zone] = $this->{$lz}['rows'] * $this->{$lz}['cols'] * $this->{$lz}['rotator'];
				$this->queue[2][$zone] = $this->{$lz}['queue'];
				if($this->{$lz}['enabled'] == 1) {
					if(is_array($this->{$lz}['price'])) {
						$count = count($this->{$lz}['price']);
						for($a=0; $a < $count; $a++) {
							if(is_numeric($this->{$lz}['price'][$a])) {
								$this->links_total += $this->{$lz}['price'][$a];
								$this->grand_total += $this->{$lz}['price'][$a];
							}
						}
					}
					//link exchange?
					if(!empty($this->{$lz}['link_exchange'])) {
						$this->links_total += 1;
						$this->grand_total += 1;
					}
				}
			}
		}
		//banners totals
		if(isset($this->banners_zones) && $this->banners_zones > 0) {
			for($z=0; $z < $this->banners_zones; $z++) {
				$zone = $z+1;
				$bz = "banners_" . $zone;
				$this->spots[5][$zone] = $this->{$bz}['rows'] * $this->{$bz}['cols'] * $this->{$bz}['rotator'];
				$this->queue[5][$zone] = $this->{$bz}['queue'];
				if($this->{$bz}['enabled'] == 1) {
					if(is_array($this->{$bz}['price'])) {
						$count = count($this->{$bz}['price']);
						for($a=0; $a < $count; $a++) {
							if(is_numeric($this->{$bz}['price'][$a])) {
								$this->banners_total += $this->{$bz}['price'][$a];
								$this->grand_total += $this->{$bz}['price'][$a];
							}
						}
					}
					//banner exchange?
					if(!empty($this->{$bz}['link_exchange'])) {
						$this->banners_total += 1;
						$this->grand_total += 1;
					}
				}
			}
		}
		//inline ads totals
		if(isset($this->inline_ads) && is_array($this->inline_ads['price'])) {
			$zone = $this->inline_ads['selection'];
			$this->spots[3][$zone] = $this->inline_ads['rotator'];
			$this->queue[3][$zone] = $this->inline_ads['queue'];
			if($this->inline_ads['enabled'] == 1) {
				$count = count($this->inline_ads['price']);
				for($a=0; $a < $count; $a++) {
					if(is_numeric($this->inline_ads['price'][$a])) {
						$this->inline_total += $this->inline_ads['price'][$a];
						$this->grand_total += $this->inline_ads['price'][$a];
					}
				}
			}
		}
		//inline links totals
		if(isset($this->inline_links) && is_array($this->inline_links['price'])) {
			$this->spots[3][4] = $this->inline_links['max'];
			if($this->inline_links['enabled'] == 1) {
				$count = count($this->inline_links['price']);
				for($a=0; $a < $count; $a++) {
					if(is_numeric($this->inline_links['price'][$a])) {
						$this->inline_total += $this->inline_links['price'][$a];
						$this->grand_total += $this->inline_links['price'][$a];
					}
				}
			}
		}
		//custom totals
		if(isset($this->custom_num) && $this->custom_num > 0) {
			for($z=0; $z < $this->custom_num; $z++) {
				$zone = $z+1;
				$cn = "custom_" . $zone;
				$this->spots[4][$zone] = $this->{$cn}['max'];
				$this->queue[4][$zone] = 0;
				if(is_numeric($this->{$cn}['price'])) {
					$this->custom_total += $this->{$cn}['price'];
					$this->grand_total += $this->{$cn}['price'];
				}
			}
		}
		//post totals
		if(isset($this->posts)) {
			$this->posts_total = $this->posts['price_adv'] + $this->posts['price_blogger'] + $this->posts['price_free'];
			$this->grand_total += $this->posts_total;
		}
	}

}

?>