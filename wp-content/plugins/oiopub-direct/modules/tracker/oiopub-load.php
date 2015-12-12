<?php

/*
Module: Stats Trackng 2.55
Developer: http://www.simonemery.co.uk

Module constructed for OIOpublisher Direct
http://www.oiopublisher.com

Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();


//module vars
$oio_enabled = isset($oiopub_set->tracker) ? $oiopub_set->tracker['enabled'] : 0;
$oio_version = "2.55";
$oio_name = __oio("Ad Stats");
$oio_module = "tracker";
$oio_menu = "main";

//min plugin version
$oio_min_version = "2.55";

//check minimum version
oiopub_module_vcheck($oio_module, $oio_min_version);

//tracker class
class oiopub_tracker {

	//global vars
	var $version;
	var $debug = false;
	var $admin_filter = false;

	//init
	function oiopub_tracker($oio_version='') {
		$this->version = $oio_version;
		$this->settings();
		$this->install();
		$this->platform();
		$this->hooks();
		$this->archive();
	}
	
	//settings
	function settings() {
		global $oiopub_set;
		//get folder name
		$dir = trim(str_replace('\\', '/', dirname(__FILE__)));
		$exp = explode('/', $dir);
		$folder = $exp[count($exp)-1];
		//misc settings
		$oiopub_set->tracker_folder = $folder;
		$oiopub_set->tracker_url = $oiopub_set->plugin_url . "/modules/" . $folder;
		$oiopub_set->dbtable_tracker_clicks = $oiopub_set->prefix . "oiopub_tracker_clicks";
		$oiopub_set->dbtable_tracker_visits = $oiopub_set->prefix . "oiopub_tracker_visits";
		$oiopub_set->dbtable_tracker_archive = $oiopub_set->prefix . "oiopub_tracker_archive";
		//pids array
		$oiopub_set->pids = array();
	}

	//install
	function install() {
		global $oiopub_set;
		if(!isset($oiopub_set->tracker) || $oiopub_set->tracker['install'] < $this->version) {
			if(empty($oiopub_set->tracker['install'])) {
				include_once($oiopub_set->modules_dir . '/' . $oiopub_set->tracker_folder . '/install/install.php');
				$this->cron($this, "add");
			} else {
				include_once($oiopub_set->modules_dir . '/' . $oiopub_set->tracker_folder . '/install/upgrade.php');
			}
			$oiopub_set->tracker['install'] = $this->version;
			oiopub_update_config('tracker', $oiopub_set->tracker);
		}
	}
	
	//cron jobs
	function cron($class, $action="add") {
		global $oiopub_cron;
		if($action == "add") {
			$oiopub_cron->add_job(array(&$class, 'tracking_update'), time()+1800, 900);
			$oiopub_cron->add_job(array(&$class, 'share_stats'), time()+14400, 86400);
			$oiopub_cron->add_job(array(&$class, 'reports'), time()+21600, 86400);
		} elseif($action == "remove") {
			$oiopub_cron->remove_job(array(&$class, 'tracking_update'));
			$oiopub_cron->remove_job(array(&$class, 'share_stats'));
			$oiopub_cron->remove_job(array(&$class, 'reports'));
		}
	}
	
	//uninstall
	function uninstall() {
		global $oiopub_set;
		include_once($oiopub_set->modules_dir . '/' . $oiopub_set->tracker_folder . '/install/uninstall.php');
		$this->cron($this, "remove");
	}

	//platform
	function platform() {
		global $oiopub_set;
		$file = $oiopub_set->modules_dir . '/' . $oiopub_set->tracker_folder . '/platform/' . $oiopub_set->platform . '.php';
		if(@file_exists($file)) {
			include_once($file);
			if(function_exists('oiopub_tracker_init')) {
				oiopub_tracker_init($this);
			}
		}
	}
	
	//add actions
	function hooks() {
		global $oiopub_hook;
		if(defined('OIOPUB_STATS')) {
			$oiopub_hook->add('stats_page', array(&$this, 'stats_page'));
		}
		if(defined('OIOPUB_MAIL')) {
			$oiopub_hook->add('mail_client', array(&$this, 'mail_stats'));
		}
		if(defined('OIOPUB_JS')) {
			$oiopub_hook->add('javascript_output', array(&$this, 'tracking_code_js'));
		}
		if(oiopub_is_admin()) {
			if(isset($_GET['page']) && $_GET['page'] == "oiopub-tracker.php") {
				$oiopub_hook->add('my_modules', array(&$this, 'admin_options'));
				$oiopub_hook->add('help_desk', array(&$this, 'help'));
			}
			if(isset($_REQUEST['do']) && $_REQUEST['do'] == "oiopub-remove") {
				$oiopub_hook->add('delete_modules', array(&$this, 'uninstall'));
			}
		}
	}
	
	//archive
	function archive() {
		global $oiopub_set, $oiopub_cron;
		if($oiopub_set->tracker['enabled'] == 1) {
			if(oiopub_is_admin()) {
				if(isset($_GET['page']) && $_GET['page'] == 'oiopub-tracker.php') {
					if(isset($_GET['manual']) && $_GET['manual'] == 1) {
						$this->tracking_update();
					}
				}
			}
		}
	}
	
	//help
	function help() {
		global $oiopub_set;
		echo "<b>Section Covered:</b> Tracker Module\n";
		echo "<br /><br />\n";
		echo "The tracker keeps tabs on clicks and impressions on purchased ads.\n";
		echo "<br /><br />\n";
		echo "You can opt to send weekly ad reports to users, as well as sending a stats overview to OIOpublisher.com (requires API Key) for use in the marketplace.\n";
		echo "<br /><br />\n";
		echo "Stats can be filtered by date and purchase ID.\n";
		echo "<br /><br />\n";
		echo "<b>Setup Requirements:</b>\n";
		echo "<br /><br />\n";
		echo "If using the php display option for ads, the footer hook must be included on your web page. Please see the <a href='" . $oiopub_set->admin_url . "/admin.php?page=oiopub-tracker.php&help=1&show=output' target='_parent'>output integration</a> menu for more details.\n";
		echo "<br /><br />\n";
		echo "<b>Manual Stats Update:</b>\n";
		echo "<br /><br />\n";
		echo "If you see a warning, or your stats haven't been updated for a while, you should use the 'manual update' feature.\n";
		echo "<br /><br />\n";
		echo "<b>Blacklist / Whitelist Filters:</b>\n";
		echo "<br /><br />\n";
		echo "Filters are split into 3 categories - <a href='http://en.wikipedia.org/wiki/IP_address' target='_blank'>IP address</a>, <a href='http://en.wikipedia.org/wiki/User_agent' target='_blank'>User-Agents</a> and <a href='http://en.wikipedia.org/wiki/Referer' target='_blank'>Referring URL</a>. You can choose to exclude (blacklist) or include (whitelist) values from any of the 3 categories.\n";
		echo "<br /><br />\n";
		echo "<i>Note that a whitelist will exclude everything apart from the values you explicitly include.</i>\n";
	}
	
	//stats report
	function reports() {
		global $oiopub_db, $oiopub_set;
		//reports enabled?
		if($oiopub_set->tracker['enabled'] != 1 || $oiopub_set->tracker['reports'] != 1 || $oiopub_set->demo) {
			return;
		}
		//get current date
		$now_date = date('Y-m-d', time());
		$now_day = date('D', time());
		//already sent today?
		if(isset($oiopub_set->tracker['last_send']) && $now_date === date('Y-m-d', $oiopub_set->tracker['last_send'])) {
			return;
		}
		//get items?
		if(!$items = $oiopub_db->GetAll("SELECT adv_name,adv_email,payment_time,rand_id FROM " . $oiopub_set->dbtable_purchases . " WHERE item_channel IN(2,3,5) AND item_status='1' AND payment_status='1'")) {
			return;
		}
		//start loop
		foreach($items as $i) {
			//get payment date
			$pay_date = date('Y-m-d', $i->payment_time);
			$pay_day = date('D', $i->payment_time);
			//send email?
			if($pay_date !== $now_date && $pay_day === $now_day) {
				$subject = oiopub_email_readable($oiopub_set->site_name . " - " . __oio("Weekly Ad Report"));
				$message = oiopub_email_readable(__oio("Dear") . " " . $i->adv_name . ",||" . __oio("You can view your weekly stats report using the link below") . ":||" . $oiopub_set->plugin_url . "/stats.php?rand=" . $i->rand_id . "&period=week||" . __oio("Thanks") . ",|" . $oiopub_set->site_url);
				oiopub_mail_client($i->adv_email, $subject, $message);
			}
		}
		//update last send time
		$oiopub_set->tracker['last_send'] = time();
		oiopub_update_config('tracker', $oiopub_set->tracker);
	}
	
	//share stats
	function share_stats() {
		global $oiopub_db, $oiopub_set, $oiopub_api;
		if($oiopub_set->tracker['enabled'] == 1 && $oiopub_set->tracker['share'] == 1) {
			if(strlen($oiopub_set->api_key) == 16 && $oiopub_set->api_valid == 1) {
				$yesterday = gmdate('Y-m-d', mktime(0, 0, 0, gmdate("m") , gmdate("d") - 1, gmdate("Y")));
				$purchases = $oiopub_db->GetOne("SELECT COUNT(pid) FROM " . $oiopub_set->dbtable_tracker_archive . " WHERE date='$yesterday' AND pid!='0'");
				$totals = $oiopub_db->GetRow("SELECT total_clicks,total_visits FROM " . $oiopub_set->dbtable_tracker_archive . " WHERE date='$yesterday' AND pid='0'");
				//get data
				$data = array();
				$data['action'] = "store";
				$data['version'] = $this->version;
				$data['date'] = $yesterday;
				$data['clicks'] = $totals->total_clicks;
				$data['visits'] = $totals->total_visits;
				$data['purchases'] = $purchases;
				//send data
				$url = $oiopub_api->oiohost . "/tracker.php";
				$res = $oiopub_api->send($url, $data);
			}
		}
	}
	
	//mail stats
	function mail_stats($pid) {
		global $oiopub_db, $oiopub_set, $oiopub_mail_extras;
		if($oiopub_set->tracker['enabled'] == 1) {
			$item = $oiopub_db->GetRow("SELECT item_channel,item_status,rand_id FROM " . $oiopub_set->dbtable_purchases . " WHERE item_id='$pid'");
			if($item->item_channel != 1 && $item->item_channel != 4) {
				if($item->item_status == -1 || $item->item_status == 1 || $item->item_status == 3) {
					$oiopub_mail_extras .= __oio("You can keep track of ad performance at any time using the tracker url below") . ":";
					$oiopub_mail_extras .= "\n\n";
					$oiopub_mail_extras .= $oiopub_set->plugin_url."/stats.php?rand=".$item->rand_id;
				}
			}
		}
	}

	//log click
	function log_click($id, $url='') {
		global $oiopub_set, $oiopub_db;
		//set vars
		$id = intval($id);
		$time_now = time();
		$referer_custom = '';
		$date_now = date('Y-m-d', $time_now);
		$ip = ip2long($oiopub_set->client_ip);
		$referer = oiopub_var('HTTP_REFERER', 'server');
		$dupe_limit = $oiopub_set->test_site ? 10 : 1800;
		//recognised browser?
		if(!$agent = oiopub_browser_check()) {
			$agent = oiopub_var('HTTP_USER_AGENT', 'server');
		}
		//valid ID?
		if($id <= 0) {
			$this->debug_message('click', 'Invalid ad ID');
			return $url;
		}
		//get data?
		if(!$item = $oiopub_db->GetRow("SELECT * FROM " . $oiopub_set->dbtable_purchases . " WHERE item_id='$id'")) {
			$this->debug_message('click', 'Ad not found in database');
			return $url;
		}
		//custom referer?
		if($item->item_model == "days") {
			$url = oiopub_var('url', 'get');
			$referer_custom = oiopub_var('ref', 'get');		
		}
		//update url?
		if(empty($url)) {
			if(($item->item_channel == 3 && $item->item_type != 4) || $item->item_channel == 5) {
				$url = $item->item_page;
			} else {
				$url = $item->item_url;
			}
		}
		//tracker enabled?
		if($oiopub_set->tracker['enabled'] != 1) {
			$this->debug_message('click', 'Stats tracker disabled');
			return $url;
		}
		//filter admin IP?
		if($this->admin_filter && !$oiopub_set->test_site) {
			if(isset($oiopub_set->admin_ips) && in_array($ip, $oiopub_set->admin_ips)) {
				$this->debug_message('click', 'Admin IP address filtered out');
				return $url;
			}
		}
		//valid IP address?
		if(!oiopub_spam_check('ip')) {
			$this->debug_message('click', 'Invalid IP address');
			return $url;
		}
		//valid user agent?
		if(!oiopub_spam_check('agent')) {
			$this->debug_message('click', 'Invalid user agent');
			return $url;
		}
		//valid referer?
		if(!$referer_custom && !oiopub_spam_check('referer')) {
			$this->debug_message('click', 'Invalid referring url');
			return $url;
		}
		//is dupe click?
		if(!$oiopub_db->GetOne("SELECT pid FROM " . $oiopub_set->dbtable_tracker_clicks . " WHERE pid='$id' AND ip='$ip' AND time > '" . ($time_now - $dupe_limit) . "'")) {
			//set referer url
			$referer = $referer_custom ? $referer_custom : $referer;
			//insert into db?
			if(!$oiopub_db->query("INSERT INTO " . $oiopub_set->dbtable_tracker_clicks . " (pid,time,date,ip,agent,referer) VALUES ('$id','$time_now','$date_now','$ip','$agent','$referer')")) {
				$this->debug_message('click', 'Database insert failed - ' . $oiopub_db->LastError());
			}
		}
		//success!
		return $url;
	}

	//log visit
	function log_visit($ids, $check_referer=true) {
		global $oiopub_set, $oiopub_db;
		//set vars
		$time_now = time();
		$date_now = date('Y-m-d', $time_now);
		$ids = oiopub_check_pids($ids, '|');
		$ip = ip2long($oiopub_set->client_ip);
		$referer = oiopub_var('HTTP_REFERER', 'server');
		//recognised browser?
		if(!$agent = oiopub_browser_check()) {
			$agent = oiopub_var('HTTP_USER_AGENT', 'server');
		}
		//tracker enabled?
		if($oiopub_set->tracker['enabled'] != 1) {
			$this->debug_message('impression', 'Stats tracker disabled');
			return false;
		}
		//valid IDs?
		if(strlen($ids) <= 0) {
			$this->debug_message('impression', 'Invalid ad IDs');
			return false;
		}
		//filter admin IP?
		if($this->admin_filter && !$oiopub_set->test_site) {
			if(isset($oiopub_set->admin_ips) && in_array($ip, $oiopub_set->admin_ips)) {
				$this->debug_message('impression', 'Admin IP address filtered out');
				return false;
			}
		}
		//valid IP address?
		if(!oiopub_spam_check('ip')) {
			$this->debug_message('impression', 'Invalid IP address');
			return false;
		}
		//valid user agent?
		if(!oiopub_spam_check('agent')) {
			$this->debug_message('impression', 'Invalid user agent');
			return false;
		}
		//valid referer?
		if($check_referer && !oiopub_spam_check('referer')) {
			$this->debug_message('impression', 'Invalid referring url');
			return false;
		}
		//insert into db?
		if(!$oiopub_db->query("INSERT INTO " . $oiopub_set->dbtable_tracker_visits . " (pids,time,date,ip,agent,referer) VALUES ('$ids','$time_now','$date_now','$ip','$agent','$referer')")) {
			$this->debug_message('impression', 'Database insert failed - ' . $oiopub_db->LastError());
			return false;
		}
		//success!
		return true;
	}

	//tracker debugging
	function debug_message($type, $msg) {
		//allowed to debug?
		if(!$this->debug || ($this->debug == 'click' && $type != 'click')) {
			return false;
		}
		//function exists?
		if(!function_exists('file_put_contents')) {
			return false;
		}
		//log it!
		return @file_put_contents('errors.log', $type . " - " . $msg . "\n", FILE_APPEND);
	}

	//add tracking code
	function tracking_code($echo=null) {
		global $oiopub_set;
		//tracker enabled?
		if($oiopub_set->tracker['enabled'] != 1) {
			return;
		}
		//are the IDs in an array?
		if(!isset($oiopub_set->pids) || !is_array($oiopub_set->pids)) {
			$oiopub_set->pids = array();
		}
		//anything in the array?
		if(empty($oiopub_set->pids)) {
			return;
		}
		//add zero?
		if(!in_array('0', $oiopub_set->pids)) {
			$oiopub_set->pids[] = 0;
		}
		//format as string
		$ids = trim(implode("|", $oiopub_set->pids));
		//get default mode
		$mode = isset($oiopub_set->tracker['mode']) ? $oiopub_set->tracker['mode'] : 'pixel';
		//use pixel?
		if($echo === true || ($echo !== false && $mode === 'pixel')) {
			//display image
			echo '<img id="oio-pixel" src="' . $oiopub_set->tracker_url . '/tracker.php?pids=' . $ids . '" alt="" />' . "\n";
		} else {
			//log visit
			$this->log_visit($ids);
		}
	}

	//js tracking
	function tracking_code_js() {
		$this->tracking_code(false);
	}

	//update stats
	function tracking_update() {
		global $oiopub_set, $oiopub_db;
		//set conditions
		@set_time_limit(0);
		@ini_set("memory_limit", "256M");
		//update clicks
		$this->tracking_update_clicks();
		//update visits
		$this->tracking_update_visits();
		//remove any bad dates
		$oiopub_db->query("DELETE FROM " . $oiopub_set->dbtable_tracker_archive . " WHERE date='0000-00-00'");
		//optimize table?
		if(mt_rand(1, 100) == 1) {
			$oiopub_db->query("OPTIMIZE TABLE " . $oiopub_set->dbtable_tracker_archive);
		}
	}

	//update click stats
	function tracking_update_clicks($limit=1000) {
		global $oiopub_set, $oiopub_db;
		//set vars
		$archive = true;
		$time_now = time();
		$limit = (int) $limit;
		$date_now = date('Y-m-d', $time_now);
		//start archive loop
		while($archive !== false) {
			//get clicks data?
			if(!$data = $oiopub_db->GetAll("SELECT pid, date FROM " . $oiopub_set->dbtable_tracker_clicks . " WHERE status='0' ORDER BY id ASC LIMIT " . $limit)) {
				break;
			}		
			//last loop?
			if($limit <= 0 || count($data) < $limit) {
				$archive = false;
			}
			//create array
			$clicks = array();
			//loop through data
			foreach($data as $key => $val) {
				//unset data
				unset($data[$key]);
				//add to array
				$clicks[$val->date][$val->pid] += 1;
			}
			//loop through clicks
			foreach($clicks as $date => $ads) {
				//unset data
				unset($clicks[$date]);
				//convert to time
				$time = strtotime($date);
				//loop through ads
				foreach($ads as $id => $counter) {
					//unset data
					unset($ads[$id]);
					//check if row exists
					$exists = (int) $oiopub_db->GetOne("SELECT id FROM " . $oiopub_set->dbtable_tracker_archive . " WHERE pid='$id' AND date='$date'");
					//insert or update?
					if($exists > 0) {
						$oiopub_db->query("UPDATE " . $oiopub_set->dbtable_tracker_archive . " SET total_clicks=total_clicks+$counter WHERE pid='$id' AND date='$date' LIMIT 1");
					} else {
						$oiopub_db->query("INSERT INTO " . $oiopub_set->dbtable_tracker_archive . " (pid,time,date,total_clicks) VALUES ('$id','$time','$date','$counter')");
					}
					//check purchase
					$this->tracking_update_purchase('clicks', $id, $counter);
				}
			}
			//update records
			$oiopub_db->query("UPDATE " . $oiopub_set->dbtable_tracker_clicks . " SET status='1' WHERE status='0' ORDER BY id ASC LIMIT " . $limit);
		}
		//delete old records
		$oiopub_db->query("DELETE FROM " . $oiopub_set->dbtable_tracker_clicks . " WHERE status='1' AND date < '$date_now'");
		//optimize table?
		if(mt_rand(1, 100) == 1) {
			$oiopub_db->query("OPTIMIZE TABLE " . $oiopub_set->dbtable_tracker_clicks);
		}
	}

	//update visit stats
	function tracking_update_visits($limit=1000) {
		global $oiopub_set, $oiopub_db;
		//set vars
		$archive = true;
		$time_now = time();
		$limit = (int) $limit;
		$date_now = date('Y-m-d', $time_now);
		//start archive loop
		while($archive !== false) {
			//get visits data?
			if(!$data = $oiopub_db->GetAll("SELECT pids, date FROM " . $oiopub_set->dbtable_tracker_visits . " WHERE status='0' ORDER BY id ASC LIMIT " . $limit)) {
				break;
			}
			//last loop?
			if($limit <= 0 || count($data) < $limit) {
				$archive = false;
			}
			//create array
			$visits = array();
			//loop through data
			foreach($data as $key => $val) {
				//unset data
				unset($data[$key]);
				//format pids
				$exp = explode("|", $val->pids);
				$exp_count = count($exp);
				//loop through sections
				for($z=0; $z < $exp_count; $z++) {
					$ad_id = intval($exp[$z]);
					$visits[$val->date][$ad_id] += 1;
				}
			}
			//loop through visits
			foreach($visits as $date => $ads) {
				//unset data
				unset($visits[$date]);
				//convert to time
				$time = strtotime($date);
				//loop through ads
				foreach($ads as $id => $counter) {
					//unset data
					unset($ads[$id]);
					//check if row exists
					$exists = (int) $oiopub_db->GetOne("SELECT id FROM " . $oiopub_set->dbtable_tracker_archive . " WHERE pid='$id' AND date='$date'");
					//insert or update?
					if($exists > 0) {
						$oiopub_db->query("UPDATE " . $oiopub_set->dbtable_tracker_archive . " SET total_visits=total_visits+$counter WHERE pid='$id' AND date='$date' LIMIT 1");
					} else {
						$oiopub_db->query("INSERT INTO " . $oiopub_set->dbtable_tracker_archive . " (pid,time,date,total_visits) VALUES ('$id','$time','$date','$counter')");
					}
					//check purchase
					$this->tracking_update_purchase('impressions', $id, $counter);
				}
			}
			//update records
			$oiopub_db->query("UPDATE " . $oiopub_set->dbtable_tracker_visits . " SET status='1' WHERE status='0' ORDER BY id ASC LIMIT " . $limit);
		}
		//delete old records
		$oiopub_db->query("DELETE FROM " . $oiopub_set->dbtable_tracker_visits . " WHERE status='1' AND date < '$date_now'");
		//optimize table?
		if(mt_rand(1, 100) == 1) {
			$oiopub_db->query("OPTIMIZE TABLE " . $oiopub_set->dbtable_tracker_visits);
		}
	}

	//tracking update, purchase check
	function tracking_update_purchase($type, $purchase_id, $count) {
		global $oiopub_set, $oiopub_db;
		//sanitize data
		$count = intval($count);
		$purchase_id = intval($purchase_id);
		//get data?
		if(!$data = $oiopub_db->GetRow("SELECT item_duration_left FROM " . $oiopub_set->dbtable_purchases . " WHERE item_id='$purchase_id' AND item_model='$type' AND item_duration > 0")) {
			return false;
		}
		//how many left?
		$num_left = $data->item_duration_left - $count;
		$oiopub_db->query("UPDATE " . $oiopub_set->dbtable_purchases . " SET item_duration_left='$num_left' WHERE item_id='$purchase_id' LIMIT 1");
		//expire purchase?
		if($num_left <= 0) {
			oiopub_approve("expire", $purchase_id);
		}
	}

	//stats page output
	function stats_page($id=0) {
		global $oiopub_db, $oiopub_set;
		$output = '';
		if($id <= 0) return;
		$rand = oiopub_clean($_GET['rand']);
		$week_style = "";
		$month_style = "";
		$nostats = false;
		$videoad = false;
		$date_now = date('Y-m-d', time());
		if(isset($_POST['oiopub_date_day1'])) {
			$time_frame = "custom";
			//date from
			$date_day1 = intval($_POST['oiopub_date_day1']);
			$date_month1 = intval($_POST['oiopub_date_month1']);
			$date_year1 = intval($_POST['oiopub_date_year1']);
			//date to
			$date_day2 = intval($_POST['oiopub_date_day2']);
			$date_month2 = intval($_POST['oiopub_date_month2']);
			$date_year2 = intval($_POST['oiopub_date_year2']);
			if($date_day1 > 0 && $date_month1 > 0 && $date_year1 > 0) {
				if($date_day2 > 0 && $date_month2 > 0 && $date_year2 > 0) {
					$sql_date = "AND a.date>='".$date_year1."-".$date_month1."-".$date_day1."' AND a.date<='".$date_year2."-".$date_month2."-".$date_day2."'";
				} else {
					$sql_date = "AND a.date='".$date_year1."-".$date_month1."-".$date_day1."'";
				}
			}
		} elseif($_GET['period'] == 'week' || $_GET['period'] == '') {
			//timeframe
			$time_frame = "Last 7 Days";
			$week_style = $_POST ? '' : ' style="color:red;"';
			//date from
			$date1 = date('Y-m-d', (time() - (86400 * 7)));
			$exp = explode("-", $date1);
			$date_day1 = intval($exp[2]);
			$date_month1 = intval($exp[1]);
			$date_year1 = intval($exp[0]);	
			//date to			
			$date2 = date('Y-m-d', (time() - (86400 * 0)));
			$exp = explode("-", $date2);
			$date_day2 = intval($exp[2]);
			$date_month2 = intval($exp[1]);
			$date_year2 = intval($exp[0]);
			//$sql_date
			$sql_date = "AND a.date>='$date1' AND a.date<='$date2'";
		} elseif($_GET['period'] == 'month') {
			//timeframe
			$time_frame = "Last 30 Days";
			$month_style = $_POST ? '' : ' style="color:red;"';
			//date from
			$date1 = date('Y-m-d', (time() - (86400 * 30)));
			$exp = explode("-", $date1);
			$date_day1 = intval($exp[2]);
			$date_month1 = intval($exp[1]);
			$date_year1 = intval($exp[0]);	
			//date to			
			$date2 = date('Y-m-d', (time() - (86400 * 0)));
			$exp = explode("-", $date2);
			$date_day2 = intval($exp[2]);
			$date_month2 = intval($exp[1]);
			$date_year2 = intval($exp[0]);
			//$sql_date
			$sql_date = "AND a.date>='$date1' AND a.date<='$date2'";
		}
		$sql = "SELECT a.*, p.item_channel, p.item_type, p.item_duration, p.payment_amount, p.payment_currency FROM " . $oiopub_set->dbtable_tracker_archive . " a, " . $oiopub_set->dbtable_purchases . " p WHERE a.pid=p.item_id AND a.pid='$id' $sql_date GROUP BY a.date ORDER BY a.date";
		$data = $oiopub_db->GetAll($sql);
		$output .= "<h3 style='margin-top:10px; margin-bottom:10px;'><i>" . __oio("Daily Statistics") . "</i> <span style='float:right; position:relative; top:15px; left:0px; font-size:12px;'><a href='stats.php?rand=" . $rand . "&period=week'" . $week_style . ">" . __oio("Last 7 days") . "</a> | <a href='stats.php?rand=" . $rand . "&period=month'" . $month_style . ">" . __oio("Last 30 days") . "</a></span></h3>\n";
		//$output .= "<div style='margin-top:-10px; margin-bottom:15px; padding:3px; background:yellow;'><b>" . __oio("Time Frame") . ":</b> " . $time_frame . "</div>";
		if(empty($data)) {
			$output .= "<div style='padding-top:0px;'><i>" . __oio("No stats found for this period of time.") . "</i></div>\n";
		} else {
			$bgcolor = "#E6E8FA";
			$output .= "<table width=\"100%\" cellspacing=\"4\" cellpadding=\"4\" border=\"0\">\n";
			$output .= "<tr><td><b>" . __oio("Date") . "</b></td><td><b>" . __oio("Clicks") . "</b></td><td><b>" . __oio("Impressions") . "</b></td><td><b>" . __oio("CTR") . "</b></td><td><b>" . __oio("eCPM") . "</b></td><td><b>" . __oio("eCPC") . "</b></td></tr>\n";
			$output .= "<tr><td colspan='6' height='5'></td></tr>\n";
			foreach($data as $r) {
				$payment_currency = $r->payment_currency;
				if($r->item_channel != 1 && $r->item_channel != 4) {
					if($r->total_visits > 0) {
						$ctr = number_format(($r->total_clicks / $r->total_visits) * 100, 2) . "%";
					} else {
						$ctr = "0.000%";
					}
					if($r->item_duration > 0 && $r->total_visits > 0) {
						$cpm = number_format(($r->payment_amount / $r->item_duration) / ($r->total_visits / 1000), 2);
						$cpm = oiopub_amount($cpm, $r->payment_currency);
					} else {
						$cpm = "N/A";
					}
					if($r->item_duration > 0 && $r->total_clicks > 0) {
						$cpc = number_format(($r->payment_amount / $r->item_duration) / $r->total_clicks, 2);
						$cpc = oiopub_amount($cpc, $r->payment_currency);
					} else {
						$cpc = "N/A";
					}
					$output .= "<tr style=\"background:$bgcolor;\"><td>" . $r->date . "</td><td>" . $r->total_clicks . "</td><td>" . $r->total_visits . "</td><td>" . $ctr . "</td><td>" . $cpm . "</td><td>" . $cpc . "</td></tr>\n";
					$clicks += intval($r->total_clicks);
					$visits += intval($r->total_visits);
					if($bgcolor == "#FFFFFF") {
						$bgcolor = "#E6E8FA";
					} else {
						$bgcolor = "#FFFFFF";
					}
				} else {
					$nostats = true;
				}
				if($r->item_channel == 3 && $r->item_type == 1) {
					$videoad = true;
				}
			}
			if($nostats == true) {
				$output .= "<tr><td colspan=\"7\" height=\"10\"></td></tr>\n";
				$output .= "<tr><td colspan=\"6\" align=\"center\"><i>" . __oio("no stats available for this ad type") . "</i></td></tr>\n";
				$output .= "</table>\n";
			} else {
				if($visits > 0) {
					$ctr = ($clicks / $visits) * 100;
				} else {
					$ctr = 0;
				}
				$output .= "<tr><td colspan=\"7\" height=\"10\"></td></tr>\n";
				$output .= "<tr style=\"background:#CCFFCC;\"><td><b>" . __oio("Totals") . ":</b></td><td>" . $clicks . "</td><td>" . $visits . "</td><td>" . number_format($ctr, 2) . "%</td><td> - </td><td> - </td></tr>\n";
				$output .= "</table>\n";
			}
		}
		if($videoad == true) {
			$output .= "<div style='padding-top:15px;'><small>" . __oio("video ads cannot have clicks tracked, therefore only impressions will display above") . "</small></div>\n";
		}
		$array1[0] = "- day -"; for($z=1; $z <= 31; $z++) $array1[$z] = $z;
		$array2[0] = "- month -"; $array2 = oiopub_get_months();
		$array3[0] = "- year -"; for($z=2007; $z <= date('Y', time()); $z++) $array3[$z] = $z;
		$output .= "<br /><br /><br />\n";
		$output .= "<h3><i>" . __oio("Custom Date Range") . "</i></h3>\n";
		$output .= "<form action=\"" . oiopub_clean($_SERVER["REQUEST_URI"]) . "\" method=\"post\">\n";
		$output .= "<input type='hidden' name='csrf' value='" . oiopub_csrf_token() . "' />\n";
		$output .= "<table border='0'>";
		$output .= "<tr><td>";
		$output .= "<table border='0' class='regular'>";
		$output .= "<tr><td><b>" . __oio("Date From") . ":</b></td>";
		$output .= "<td>" . oiopub_dropmenu_kv($array1, 'oiopub_date_day1', $date_day1) . " ";
		$output .= oiopub_dropmenu_kv($array2, 'oiopub_date_month1', $date_month1) . " ";
		$output .= oiopub_dropmenu_kv($array3, 'oiopub_date_year1', $date_year1) . "</td></tr>";
		$output .= "<tr><td><b>" . __oio("Date To") . ":</b></td>";
		$output .= "<td>" . oiopub_dropmenu_kv($array1, 'oiopub_date_day2', $date_day2) . " ";
		$output .= oiopub_dropmenu_kv($array2, 'oiopub_date_month2', $date_month2) . " ";
		$output .= oiopub_dropmenu_kv($array3, 'oiopub_date_year2', $date_year2) . "</td></tr>";
		$output .= "</table>";
		$output .= "</td><td rowspan='2' valign='middle' style='padding-left:40px;'>";
		$output .= "<table border='0'>";
		$output .= "<tr><td></td><td><input type=\"submit\" value=\"" . __oio('Show stats') . "\" /></td></tr>";
		$output .= "</table>";
		$output .= "</td></tr>";
		$output .= "</table>\n";
		$output .= "</form>\n";
		$output .= "<div style='padding:10px; border:1px dashed #000; margin-top:40px;'>\n";	
		$output .= "1.) <b>" . __oio("CTR") . "</b> = " . __oio("click through rate") . "</i>\n";
		$output .= "<br />\n";
		$output .= "2.) <b>" . __oio("eCPM") . "</b> = " . __oio("effective cost per thousand impressions") . "</i>\n";
		$output .= "<br />\n";
		$output .= "3.) <b>" . __oio("eCPC") . "</b> = " . __oio("effective cost per click") . "</i>\n";
		$output .= "</div>\n";
		echo $output;
	}
	
	//archive overview
	function options_overview() {
		global $oiopub_set, $oiopub_db;
		//get vars
		$purchase_id = 0;
		$sql_purchase = "";
		$and1 = "";
		$order_by = "";
		//current date
		$date_now = date('Y-m-d', time());
		//process archive request
		if(isset($_POST['oiopub_pid'])) {
			//timeframe
			$time_frame = "Custom";
			//purchase ID
			$purchase_id = intval($_POST['oiopub_pid']);
			//date from
			$date_day1 = intval($_POST['oiopub_date_day1']);
			$date_month1 = intval($_POST['oiopub_date_month1']);
			$date_year1 = intval($_POST['oiopub_date_year1']);
			//date to
			$date_day2 = intval($_POST['oiopub_date_day2']);
			$date_month2 = intval($_POST['oiopub_date_month2']);
			$date_year2 = intval($_POST['oiopub_date_year2']);
		} elseif((isset($_GET['period']) && $_GET['period'] == 'week') || !isset($_GET['period'])) {
			//timeframe
			$time_frame = "Last 7 Days";
			//date from
			$date1 = date('Y-m-d', (time() - (86400 * 7)));
			$exp = explode("-", $date1);
			$date_day1 = intval($exp[2]);
			$date_month1 = intval($exp[1]);
			$date_year1 = intval($exp[0]);	
			//date to			
			$date2 = date('Y-m-d', (time() - (86400 * 0)));
			$exp = explode("-", $date2);
			$date_day2 = intval($exp[2]);
			$date_month2 = intval($exp[1]);
			$date_year2 = intval($exp[0]);
		} elseif($_GET['period'] == 'month') {
			//timeframe
			$time_frame = "Last 30 Days";
			//date from
			$date1 = date('Y-m-d', (time() - (86400 * 30)));
			$exp = explode("-", $date1);
			$date_day1 = intval($exp[2]);
			$date_month1 = intval($exp[1]);
			$date_year1 = intval($exp[0]);	
			//date to			
			$date2 = date('Y-m-d', (time() - (86400 * 0)));
			$exp = explode("-", $date2);
			$date_day2 = intval($exp[2]);
			$date_month2 = intval($exp[1]);
			$date_year2 = intval($exp[0]);
		}
		//calculate stats
		if($date_day1 > 0 && $date_month1 > 0 && $date_year1 > 0) {
			$type = "Purchase";
			$select = "a.pid as id, a.total_clicks as tc, a.total_visits as tv";
			$my_date = $date_year1."-".$date_month1."-".$date_day1;
			if($date_day2 > 0 && $date_month2 > 0 && $date_year2 > 0) {
				$sql_date = "a.date>='".$date_year1."-".$date_month1."-".$date_day1."' AND a.date<='".$date_year2."-".$date_month2."-".$date_day2."'";
			} else {
				$sql_date = "a.date='".$date_year1."-".$date_month1."-".$date_day1."'";
			}
			if($purchase_id > 0) {
				$type = "Date";
				$select = "a.date as id, a.total_clicks as tc, a.total_visits as tv, p.item_duration, p.payment_amount, p.payment_currency";
				$sql_purchase = "a.pid='".$purchase_id."'";
				$and1 = " AND ";
				$group_by = "GROUP BY a.date";
			} elseif($date_day2 > 0 && $date_month2 > 0 && $date_year2 > 0) {
				$type = "Date";
				$select = "a.date as id, SUM(a.total_clicks) as tc, SUM(a.total_visits) as tv, p.item_duration, p.payment_amount, p.payment_currency";
				$group_by = "GROUP BY a.date";
			} else {
				$select .= ", p.item_channel, p.item_type, p.item_url, p.item_page, p.item_duration, p.payment_amount, p.payment_currency";
				$sql_date .= " AND p.item_status='1' AND p.payment_status='1' AND p.item_channel!='1' AND p.item_channel!='4'";
				$order_by = "ORDER BY a.pid";
			}
		} elseif($purchase_id > 0) {
			$type = "Date";
			$select = "a.date as id, a.total_clicks as tc, a.total_visits as tv, p.item_duration, p.payment_amount, p.payment_currency";
			$sql_purchase = "a.pid='".$purchase_id."'";
			$order_by = "ORDER BY a.date";
		}
		if(!empty($select)) {
			$sql = "SELECT $select FROM " . $oiopub_set->dbtable_tracker_archive . " a INNER JOIN " . $oiopub_set->dbtable_purchases . " p ON a.pid=p.item_id WHERE $sql_purchase $and1 $sql_date $group_by $order_by";
			$archive_res = $oiopub_db->GetAll($sql);
			$res_rows = intval($oiopub_db->num_rows);
		}
		//view stats
		echo "<h2>Stats Archive</h2>\n";
		echo "Filter results by purchase ID, or a specific date range.\n";
		echo "<br /><br /><br />\n";
		echo "<b>Preset Date Filters:</b>&nbsp; <a href='admin.php?page=oiopub-tracker.php&period=week'>Last 7 Days</a> &nbsp;|&nbsp; <a href='admin.php?page=oiopub-tracker.php&period=month'>Last 30 days</a>\n";
		echo "<br /><br /><br />\n";
		$array1[0] = "- day -"; for($z=1; $z <= 31; $z++) $array1[$z] = $z;
		$array2[0] = "- month -"; $array2 = oiopub_get_months();
		$array3[0] = "- year -"; for($z=2007; $z <= date('Y', time()); $z++) $array3[$z] = $z;
		echo "<form action=\"" . oiopub_clean($_SERVER["REQUEST_URI"]) . "\" method=\"post\">\n";
		echo "<input type='hidden' name='csrf' value='" . oiopub_csrf_token() . "' />\n";
		$filters  = '';
		$filters .= "<table border='0' cellpadding='0' cellspacing='0' style='margin-left:-5px;'>";
		$filters .= "<tr><td>";
		$filters .= "<table border='0' cellpadding='4' cellspacing='4'>";
		$filters .= "<tr><td><b>Date From:</b></td>";
		$filters .= "<td>" . oiopub_dropmenu_kv($array1, 'oiopub_date_day1', $date_day1) . " ";
		$filters .= oiopub_dropmenu_kv($array2, 'oiopub_date_month1', $date_month1) . " ";
		$filters .= oiopub_dropmenu_kv($array3, 'oiopub_date_year1', $date_year1) . "</td></tr>";
		$filters .= "<tr><td><b>Date To:</b></td>";
		$filters .= "<td>" . oiopub_dropmenu_kv($array1, 'oiopub_date_day2', $date_day2) . " ";
		$filters .= oiopub_dropmenu_kv($array2, 'oiopub_date_month2', $date_month2) . " ";
		$filters .= oiopub_dropmenu_kv($array3, 'oiopub_date_year2', $date_year2) . "</td></tr>";
		$filters .= "</table>";
		$filters .= "</td><td rowspan='2' valign='middle' style='padding-left:40px;'>";
		$filters .= "<table border='0' cellpadding='4' cellspacing='4'>";
		$filters .= "<tr><td></td><td><input type=\"submit\" value=\"Filter By Date\" /></td></tr>";
		$filters .= "</table>";
		$filters .= "</td></tr>";
		$filters .= "</table>";
		$filters .= "<br />";
		$filters .= "<table border='0' cellpadding='0' cellspacing='0' style='margin-left:-5px;'>";
		$filters .= "<tr><td>";
		$filters .= "<table border='0' cellpadding='4' cellspacing='4'>";
		$filters .= "<tr><td><b>Purchase ID:</b></td>";
		$filters .= "<td><input type=\"text\" name=\"oiopub_pid\" value=\"" . intval($purchase_id) . "\" size=\"5\" /> &nbsp<i>(set to zero to disable ID filter)</i></td></tr>";
		$filters .= "</table>";
		$filters .= "</td><td style='padding-left:5px;'>";
		$filters .= "<table border='0' cellpadding='4' cellspacing='4'>";
		$filters .= "<tr><td></td><td><input type=\"submit\" value=\"Filter By ID\" /></td></tr>";
		$filters .= "</table>";
		$filters .= "</td></tr>";
		$filters .= "</table>\n";
		echo $filters;
		echo "<br /><br />\n";
		echo "<span style='background:yellow; padding:5px; width:100%;'><b>Time Frame:</b> " . $time_frame . "</span>\n";
		if($purchase_id > 0) {
			echo "<br /><br />\n";
			$myitem = $oiopub_db->GetRow("SELECT item_channel,item_type,item_url,item_page FROM " . $oiopub_set->dbtable_purchases . " WHERE item_id='$purchase_id'");
			if(empty($myitem)) {
				echo "<i>No data on record containing that Purchase ID\n";
			} else {
				$array = oiopub_adtype_info($myitem);
				echo "<b>Purchase Type?</b>&nbsp; " . $array['type'] . "\n";
				echo "<br />\n";
				echo "<b>Purchase URL?</b>&nbsp; <a href=\"" . $array['url'] . "\" target=\"_blank\">" . $array['url'] . "</a>\n";
			}
		}
		echo "<br /><br /><br />\n";
		echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $oiopub_set->plugin_url . "/libs/bubble/bubble.css\" />\n";
		echo "<script type=\"text/javascript\" src=\"" . $oiopub_set->plugin_url . "/libs/bubble/bubble.js\"></script>\n";
		echo "<script type=\"text/javascript\">window.onload=function(){enableTooltip()};</script>\n";
		if($res_rows <= 0) {
			echo "<i>No results found within search paramters</i>\n";
		} else {
			$daily_visits = array();
			$sql_date = str_replace("a.", "", $sql_date);
			$daily = $oiopub_db->GetAll("SELECT date,total_visits FROM " . $oiopub_set->dbtable_tracker_archive . " WHERE " . $sql_date . " AND pid='0' GROUP BY date");
			if(!empty($daily)) {
				foreach($daily as $d) {
					$daily_visits[$d->date] = intval($d->total_visits);
				}
			}
			$clicks = 0;
			$visits = 0;
			$bgcolor = "#FFFFFF";
			echo "<table width=\"100%\" cellspacing=\"4\" cellpadding=\"4\" border=\"0\">\n";
			echo "<tr><td><b>" . $type . "</b></td><td><b>Ad Clicks</b></td><td><b>Ad Impressions</b></td><td><b>CTR</b></td><td><b>eCPM</b></td><td><b>eCPC</b></td></tr>\n";
			foreach($archive_res as $r) {
				if($r->tv > 0) {
					$ctr = ($r->tc / $r->tv) * 100;
				} else {
					$ctr = 0;
				}
				if($r->item_duration > 0 && $r->tv > 0) {
					$cpm = ($r->payment_amount / $r->item_duration) / ($r->tv / 1000);
				} else {
					$cpm = 0;
				}
				if($r->item_duration > 0 && $r->tc > 0) {
					$cpc = ($r->payment_amount / $r->item_duration) / $r->tc;
				} else {
					$cpc = 0;
				}
				if($r->id > 0 && strpos($r->id, "-") === false) {
					$array = oiopub_adtype_info($r);
					$my_id = $array['type'] . "<br /><a href=\"" . $array['url'] . "\" target=\"_blank\">" . $array['url'] . "</a>";
				} else {
					$my_id = "";
				}
				echo "<tr style=\"background:$bgcolor;\"><td>" . $r->id . "</td><td>" . $r->tc . "</td><td><a style='text-decoration:none;' href='javascript://' title='Ad impressions recorded over " . intval($daily_visits[$r->id]) . " separate page loads'>" . $r->tv . "</a></td><td>" . number_format($ctr, 2) . "%</td><td>" . number_format($cpm, 2) . "</td><td>" . number_format($cpc, 2) . "</td></tr>\n";
				$clicks += intval($r->tc);
				$visits += intval($r->tv);
				if($bgcolor == "#FFFFFF") {
					$bgcolor = "#E6E8FA";
				} else {
					$bgcolor = "#FFFFFF";
				}
			}
			//calculate totals
			if($visits > 0) {
				$ctr = ($clicks / $visits) * 100;
			} else {
				$ctr = 0;
			}
			echo "<tr><td colspan=\"5\" height=\"10\"></td></tr>\n";
			echo "<tr style=\"background:#CCFFCC;\"><td><b>Totals:</b></td><td>" . $clicks . "</td><td>" . $visits . "</td><td>" . number_format($ctr, 2) . "%</td><td> - </td><td> - </td></tr>\n";
			echo "</table>\n";
			echo "<br /><br />\n";
			echo "<div style='padding:10px; background:#F3F3F3; border:1px dashed #999;'>\n";
			echo "1.) <b>CTR</b> = click through rate</i>\n";
			echo "<br />\n";
			echo "2.) <b>eCPM</b> = effective cost per thousand impressions</i>\n";
			echo "<br />\n";
			echo "3.) <b>eCPC</b> = effective cost per click</i>\n";
			echo "</div>\n";
		}
		echo "</form>\n";
		echo "<br /><br /><br />\n";
		//manual archive output
		echo "<h2>Manual Update</h2>\n";
		echo "Allows you to manually update stats, in case the cron job fails for any reason.\n";
		echo "<form action=\"admin.php\" method=\"get\">\n";
		echo "<input type=\"hidden\" name=\"page\" value=\"oiopub-tracker.php\" />\n";
		echo "<input type=\"hidden\" name=\"manual\" value=\"1\" />\n";
		echo "<input type=\"submit\" value=\"Update Now\" />\n";
		echo "</form>\n";
		echo "<br /><br />\n";
	}
	
	//admin settings
	function options_settings() {
		global $oiopub_set;
		//update settings
		if(isset($_POST['oiopub_tracker_enabled'])) {
			//tracker options
			$oiopub_set->tracker['enabled'] = intval($_POST['oiopub_tracker_enabled']);
			$oiopub_set->tracker['mode'] = oiopub_clean($_POST['oiopub_tracker_mode']);
			$oiopub_set->tracker['reports'] = intval($_POST['oiopub_tracker_reports']);
			$oiopub_set->tracker['share'] = intval($_POST['oiopub_tracker_share']);
			$oiopub_set->tracker['ip_filter'] = oiopub_clean($_POST['ip_filter']);
			$oiopub_set->tracker['agent_filter'] = oiopub_clean($_POST['agent_filter']);
			$oiopub_set->tracker['referer_filter'] = oiopub_clean($_POST['referer_filter']);
			oiopub_update_config('tracker', $oiopub_set->tracker);
			//tracker filters
			oiopub_update_config('ip_filter_data', oiopub_array_format(explode("\n", $_POST['ip_filter_data'])));
			oiopub_update_config('agent_filter_data', oiopub_array_format(explode("\n", $_POST['agent_filter_data'])));
			oiopub_update_config('referer_filter_data', oiopub_array_format(explode("\n", $_POST['referer_filter_data'])));
		}
		echo "<h2>Configure Settings</h2>\n";
		echo "Allows you to automatically issue stats reports to advertisers; including clicks and impressions.\n";
		echo "<br /><br /><br />\n";
		echo "<form action=\"" . $oiopub_set->request_uri . "\" method=\"post\">\n";
		echo "<input type='hidden' name='csrf' value='" . oiopub_csrf_token() . "' />\n";
		echo "<b>Enable Module?</b>";
		echo "<br /><br />\n";
		echo oiopub_dropmenu_kv($oiopub_set->arr_yesno, 'oiopub_tracker_enabled', $oiopub_set->tracker['enabled']);
		echo "&nbsp;&nbsp;<i>setting this value to 'yes' will switch on the purchase tracking stats</i>\n";
		echo "<br /><br />\n";
		echo "<b>Tracking mode</b>";
		echo "<br /><br />\n";
		echo oiopub_dropmenu_k(array( 'pixel', 'server' ), 'oiopub_tracker_mode', $oiopub_set->tracker['mode']);
		echo "&nbsp;&nbsp;<i>'pixel' mode is more accurate, but you can switch to 'server' mode to improve performance</i>\n";
		echo "<br /><br />\n";
		echo "<b>Send Weekly Reports?</b>";
		echo "<br /><br />\n";
		echo oiopub_dropmenu_kv($oiopub_set->arr_yesno, 'oiopub_tracker_reports', $oiopub_set->tracker['reports']);
		echo "&nbsp;&nbsp;<i>setting this value to 'yes' will automatically send out weekly stats reports to advertisers</i>\n";
		echo "<br /><br />\n";
		echo "<b>Send Daily CTR Stats to Marketplace?</b>";
		echo "<br /><br />\n";
		echo oiopub_dropmenu_kv($oiopub_set->arr_yesno, 'oiopub_tracker_share', $oiopub_set->tracker['share']);
		echo "&nbsp;&nbsp;<i>setting this value to 'yes' will automatically send daily total CTR stats to OIOpublisher</i>\n";
		echo "<br /><br />\n";
		echo "<div class=\"submit\"><input type=\"submit\" value=\"Update Settings\" /></div>\n";
		echo "<br />\n";
		echo "<span id='filters'></span>\n";
		echo "<br />\n";
		//filter options
		echo "<h2>Blacklist Filtering</h2>\n";
		echo "This feature allows you to blacklist (or whitelist) certain IP addresses, useragents and referring urls. The aim is to stop invalid clicks from being registered by the stats tracker. For more information, please see the <a href='admin.php?page=oiopub-help.php'>tracker help</a> section.\n";
		echo "<br /><br />\n";
		echo "<i>NB: If you're not sure what these filters do, just leave them at their default values.</i>\n";
		echo "<br /><br /><br />\n";
		$this->options_settings_filter('ip');
		echo "<br /><br />\n";
		$this->options_settings_filter('agent');
		echo "<br /><br />\n";
		$this->options_settings_filter('referer');
		echo "<br /><br />\n";
		echo "<div class=\"submit\"><input type=\"submit\" value=\"Update Settings\" /></div>\n";
		echo "</form>\n";
		echo "<br /><br />\n";
	}
	
	//admin filter options
	function options_settings_filter($type='ip') {
		global $oiopub_set;
		//get filter vars
		$type = strtolower($type);
		$var = $type . "_filter";
		$var_text = $var . "_data";
		$setting = $oiopub_set->tracker[$var];
		if(oiopub_count($oiopub_set->{$var_text}) > 0) {
			$data = implode("\n", $oiopub_set->{$var_text});
		} else {
			$data = "";
		}
		//output filter options
		echo "<b>" . ucfirst($type) . " Filter</b>: " .  oiopub_dropmenu_k(array("whitelist", "blacklist"), $var, $setting);
		echo "<br /><br />\n";
		echo "<b>" . ucfirst($type) . " Filter Data:</b> (one per line)\n";
		echo "<br /><br />\n";
		echo "<textarea name='$var_text' rows='5' cols='70'>" . $data . "</textarea>\n";
		
	}
	
	//live stats
	function options_live() {
		global $oiopub_set, $oiopub_db;
		$total = 0; $output = "";
		$type = oiopub_var('type', 'get');
		echo "<h2>Live Stats Data</h2>\n";
		echo "This screen shows you live data for the current day. You can choose between viewing <a href='admin.php?page=oiopub-tracker.php&opt=live'>clicks</a> and <a href='admin.php?page=oiopub-tracker.php&opt=live&type=impressions'>impressions</a> data\n";
		echo "<br /><br />\n";
		echo "<i>You can use this data to help <a href='admin.php?page=oiopub-tracker.php&opt=settings#filters'>filter out</a> suspected bots and other suspicious entries.</i>\n";
		echo "<br /><br /><br />\n";
		if($type == "impressions") {
			//live visits data
			$data = $oiopub_db->GetAll("SELECT * FROM " . $oiopub_set->dbtable_tracker_visits . " ORDER BY time");
			if(!empty($data)) {
				$output .= "<table width='100%' border='1' cellpadding='4' cellspacing='4'>\n";
				$output .= "<tr><td><b>Date</b></td><td><b>Ad IDs</b></td><td><b>IP Address</b></td><td><b>Browser</b></td><td><b>Referer</b></td><td><b>Processed?</b></td></tr>\n";
				foreach($data as $d) {
					$output .= "<tr><td>" . date("d-m-Y, H:i", $d->time) . "</td><td>" . str_replace(array('0|', '|'), array('', ', '), $d->pids) . "</td><td>" . long2ip($d->ip) . "</td><td>" . $d->agent . "</td><td>" . $d->referer . "</td><td>" . ($d->status == 1 ? 'Yes' : 'No') . "</td></tr>\n";
					$total++;
				}
				$output .= "</table>\n";
			} else {
				$output .= "No clicks in the tracker table at the moment\n";
			}
			echo "<b>Total Impressions:</b> " . $total . "\n";
			echo "<br /><br />\n";
			echo $output;
		} else {
			//live click data
			$data = $oiopub_db->GetAll("SELECT * FROM " . $oiopub_set->dbtable_tracker_clicks . " ORDER BY time");
			if(!empty($data)) {
				$output .= "<table width='100%' border='1' cellpadding='4' cellspacing='4'>\n";
				$output .= "<tr><td><b>Date</b></td><td><b>Ad ID</b></td><td><b>IP Address</b></td><td><b>Browser</b></td><td><b>Referer</b></td><td><b>Processed?</b></td></tr>\n";
				foreach($data as $d) {
					if($d->pid > 0) {
						$output .= "<tr><td>" . date("d-m-Y, H:i", $d->time) . "</td><td>" . $d->pid . "</td><td>" . long2ip($d->ip) . "</td><td>" . $d->agent . "</td><td>" . $d->referer . "</td><td>" . ($d->status == 1 ? 'Yes' : 'No') . "</td></tr>\n";
						$total++;
					}
				}
				$output .= "</table>\n";
			} else {
				$output .= "No clicks in the tracker table at the moment\n";
			}
			echo "<b>Total Clicks:</b> " . $total . "\n";
			echo "<br /><br />\n";
			echo $output;
		}
	}
	
	//admin options
	function admin_options() {
		global $oiopub_set;
		//get option
		$option_type = oiopub_var('opt', 'get');
		echo "<table width='100%' style='background:#CCFFCC; border:1px solid #9AFF9A; padding:10px;'>\n";
		echo "<tr><td>\n";
		$options = "<font color='blue'><b>Options:</b></font>&nbsp; ";
		if(empty($option_type)) { $options .= "<a href='admin.php?page=oiopub-tracker.php'><font color='red'><b>Overview</b></font></a> | "; } else { $options .= "<a href='?page=oiopub-tracker.php'><b>Overview</b></a> | "; }
		if($option_type == "settings") { $options .= "<a href='admin.php?page=oiopub-tracker.php&opt=settings'><font color='red'><b>Settings</b></font></a> | "; } else { $options .= "<a href='admin.php?page=oiopub-tracker.php&opt=settings'><b>Settings</b></a> | "; }
		if($option_type == "live") { $options .= "<a href='admin.php?page=oiopub-tracker.php&opt=live'><font color='red'><b>Live Stats</b></font></a>"; } else { $options .= "<a href='admin.php?page=oiopub-tracker.php&opt=live'><b>Live Stats</b></a>"; }
		echo $options;
		echo "</td></tr>\n";
		echo "</table>\n";
		echo "<br />\n";
		if(empty($option_type)) {
			$this->options_overview();
		} elseif($option_type == 'settings') {
			$this->options_settings();
		} elseif($option_type == 'live') {
			$this->options_live();
		}
	}
	
}

//execute class
$oiopub_plugin[$oio_module] = new oiopub_tracker($oio_version);

?>