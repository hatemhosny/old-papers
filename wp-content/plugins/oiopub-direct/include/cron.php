<?php

/*
Copyright (C) 2007  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

//cron class
class oiopub_cron {

	var $alerts = 1;
	var $timeout = 1;
	var $blocking = 0;
	var $test = false;

	//init
	function oiopub_cron() {
		global $oiopub_set, $oiopub_db;
		//cron disabled?
		if(isset($oiopub_set->cron_disabled) && $oiopub_set->cron_disabled == 1) {
			if($this->test) {
				echo 'Cron disabled';
				exit();
			}
			return;
		}
		//cron defined?
		if(defined('OIOPUB_CRON') || strpos($_SERVER['REQUEST_URI'], "cron") !== false) {
			if($this->test) {
				echo 'Invalid cron url';
				exit();
			}
			return;
		}
		//already running?
		if($oiopub_set->cron_running >= time()) {
			if($this->test) {
				echo 'Cron already running';
				exit();
			}
			return;
		}
		//any jobs in the queue?
		if(oiopub_count($oiopub_set->cron_jobs) == 0) {
			if($this->test) {
				echo 'No cron jobs found';
				exit();
			}
			return;
		}
		//get first function
		$func = oiopub_array_shift(array_keys($oiopub_set->cron_jobs), 1);
		//spawn task?
		if($oiopub_set->cron_jobs[$func]['time'] <= time()) {
			//get latest access time
			$accessed = $oiopub_db->GetOne("SELECT value FROM " . $oiopub_set->dbtable_config . " WHERE name='cron_accessed' LIMIT 1");
			//stop here?
			if($accessed && $accessed >= time()) {
				return;
			}
			//set cron accessed
			oiopub_update_config('cron_accessed', time()+60);
			//spawn
			return $this->spawn();
		}
	}

	//spawn cron
	function spawn() {
		global $oiopub_set;
		//set vars
		$spawn_url = $oiopub_set->plugin_url_org . "/cron.php?t=" . time();
		$parse = parse_url($spawn_url);
		//try CURL first?
		if(function_exists('curl_init') && oiopub_file_contents($spawn_url)) {
			return true;
		}
		//successful connection?
		if(!$fp = @fsockopen($parse['host'], 80, $errno, $errstr, $this->timeout)) {
			//show failure?
			if($this->test) {
				echo 'Unable to connect  to ' . $parse['host'] . ' - ' . $errstr;
				exit();
			}
			//stop
			return false;
		}
		//set stream options?
		if(function_exists('stream_set_timeout')) {
			@stream_set_timeout($fp, $this->timeout);
			@stream_set_blocking($fp, $this->blocking);
		}
		//set user-agent
		$user_agent = !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "OIOpub Direct Scheduled Task Manager";
		//set request headers
		$request  = "GET " . (isset($parse['path']) ? $parse['path'] : '/') . (isset($parse['query']) ? '?' . $parse['query'] : '') . " HTTP/1.0\r\n";
		$request .= "Host: " . $parse['host'] . "\r\n";
		$request .= "User-Agent: " . strip_tags($user_agent) . "\r\n";
		$request .= "Connection: Close\r\n\r\n";			
		//send request
		@fputs($fp, $request, strlen($request));
		//clear buffer
		@fread($fp, 1024);
		//close
		@fclose($fp);
		//return
		return true;
	}

	//run job
	function run_job($num=1, $time_check=true) {
		global $oiopub_set, $oiopub_db;
		//set result
		$result = false;
		//any jobs in the queue?
		if(oiopub_count($oiopub_set->cron_jobs) == 0) {
			return $result;
		}
		//get latest running time
		$running = $oiopub_db->GetOne("SELECT value FROM " . $oiopub_set->dbtable_config . " WHERE name='cron_running' LIMIT 1");
		//already running?
		if($running && $running >= time()) {
			return $result;
		}
		//block further requests
		oiopub_update_config('cron_running', time()+30);
		//get next cron
		$func = oiopub_array_shift(array_keys($oiopub_set->cron_jobs), $num);
		//run cron?
		if($oiopub_set->cron_jobs[$func]['time'] <= time() || $time_check === false) {
			//get vars
			$function = unserialize($func);
			$period = $oiopub_set->cron_jobs[$func]['period'];
			$args = is_array($oiopub_set->cron_jobs[$func]['args']) ? $oiopub_set->cron_jobs[$func]['args'] : array();
			//is class?
			if(is_array($function)) {
				$is_class = is_string($function[0]) ? $function[0] : get_class($function[0]);
			} else {
				$is_class = false;
			}
			//run cron?
			if($is_class && $is_class != '__PHP_Incomplete_Class' && method_exists($function[0], $function[1])) {
				@call_user_func_array($function, $args);
			} elseif(is_string($function) && function_exists($function)) {
				@call_user_func_array($function, $args);
			}
			//remove cron
			$this->remove_job($function);
			//re-add job?
			if($period > 0) {
				$next_time = time() + $period;
				$this->add_job($function, $next_time, $period, $args);
			}
			//successful run
			$result = true;
		}
		//return result
		return $result;
	}

	//job scheduled?
	function is_job($function) {
		global $oiopub_set;
		if(!$function) return;
		$function = serialize($function);
		if(isset($oiopub_set->cron_jobs[$function])) {
			return true;
		}
		return false;
	}

	//add job
	function add_job($function, $time, $period, $args=array()) {
		global $oiopub_set;
		if(!$function) return;
		$function = serialize($function);
		if(empty($oiopub_set->cron_jobs)) {
			$oiopub_set->cron_jobs = array();
		}
		if(!isset($oiopub_set->cron_jobs[$function])) {
			$array = array();
			if(oiopub_count($oiopub_set->cron_jobs) > 0) {
				foreach($oiopub_set->cron_jobs as $key => $val) {
					if(!empty($function) && $time < $val['time']) {
						$array[$function] = array( "time"=>$time, "period"=>$period, "args"=>$args );
						$function = "";
					}
					$array[$key] = $val;
				}
			}
			if(!empty($function)) {
				$array[$function] = array( "time"=>$time, "period"=>$period, "args"=>$args );
			}
			oiopub_update_config('cron_jobs', $array);
		}
	}

	//remove job
	function remove_job($function) {
		global $oiopub_set;
		if(!$function) return;
		$function = serialize($function);
		if(isset($oiopub_set->cron_jobs[$function])) {
			unset($oiopub_set->cron_jobs[$function]);
			if(oiopub_count($oiopub_set->cron_jobs) == 0) {
				$oiopub_set->cron_jobs = array();
			}
			oiopub_update_config('cron_jobs', $oiopub_set->cron_jobs);
		}
	}

	//re-add all jobs
	function refresh_all() {
		global $oiopub_set, $oiopub_db;
		global $oiopub_cron, $oiopub_install, $oiopub_module, $oiopub_plugin;
		//clear all jobs
		oiopub_update_config('cron_jobs', "");
		//load install class?
		if(!isset($oiopub_install)) {
			include_once($oiopub_set->folder_dir . "/include/install.php");
			$oiopub_install = new oiopub_install();
		}
		//load install class?
		if(!isset($oiopub_module)) {
			include_once($oiopub_set->folder_dir . "/include/modules.php");
			$oiopub_module = new oiopub_modules();
		}
		//add core jobs
		$oiopub_install->install_cron();
		//add module jobs
		if(!empty($oiopub_module->modcount)) {
			foreach($oiopub_module->modcount as $mod) {
				if(method_exists($oiopub_plugin[$mod[5]], "cron")) {
					$oiopub_plugin[$mod[5]]->cron($oiopub_plugin[$mod[5]], "add");
				}
			}
		}
	}

	//version check
	function version_check() {
		global $oiopub_set;
		if($this->alerts == 0) {
			return;
		}
		//plugin version check
		$version = oiopub_file_contents("http://api.oiopublisher.com/2.0/version.txt");
		if(!empty($version)) {
			if($oiopub_set->version < $version && $oiopub_set->version_status == 0) {
				$subject = "OIOpublisher Direct - updated version released";
				$message = "This message has been automatically generated by your installation of OIO - an updated version has been released. To download the new release, or view the changelog, please click below:\n\nhttp://download.oiopublisher.com";
				//oiopub_mail_client($oiopub_set->admin_mail, $subject, $message);
				oiopub_update_config('version_status', 1);
				oiopub_update_config('overlay_notify', 1);
			}
		}
		//new alert check
		$alert = oiopub_file_contents("http://api.oiopublisher.com/2.0/alert.txt");
		$alert = intval($alert);
		if($alert > $oiopub_set->alert_last && $alert > 0) {
			oiopub_update_config('alert_current', $alert);
			$check = oiopub_file_contents("http://api.oiopublisher.com/2.0/alert-version.txt");
			if(!empty($check)) {
				$exp = explode(" ", $check);
				if($exp[0] == "+" && $oiopub_set->version < $exp[1]) {
					oiopub_update_config('alert_last', $alert);
				}
				if($exp[0] == "-" && $oiopub_set->version > $exp[1]) {
					oiopub_update_config('alert_last', $alert);
				}
				if($exp[0] == "=" && $oiopub_set->version != $exp[1]) {
					oiopub_update_config('alert_last', $alert);
				}
			}
		}
	}

	//purchase expiry
	function purchase_expire() {
		global $oiopub_set, $oiopub_db;
		//get purchase data
		$data = $oiopub_db->GetAll("SELECT * FROM " . $oiopub_set->dbtable_purchases . " WHERE item_status='1' AND item_channel!='1' AND payment_status='1' AND item_model='days'");
		//loop through data
		foreach($data as $key => $val) {
			//unset row
			unset($data[$key]);
			//days model only
			$time_limit = time() - (86400 * $val->item_duration);
			//anything to process?
			if($val->item_duration <= 0 || $val->payment_time <= 0 || $val->payment_time > $time_limit) {
				continue;
			}
			//subscription set?
			if($val->item_subscription == 0) {
				//no subscription
				oiopub_approve("expire", $val->item_id);
			} else {
				//subscription
				$val->subscription = true;
				oiopub_approve("history", $val);
				$new_time = $val->payment_time + (86400 * $val->item_duration);
				$oiopub_db->query("UPDATE " . $oiopub_set->dbtable_purchases . " SET payment_time='$new_time' WHERE item_id='$val->item_id' LIMIT 1");
			}
		}
	}

}

?>