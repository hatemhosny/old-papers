<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

/* INSTALLER FUNCTIONS */

//install check
function oiopub_install_check() {
	global $oiopub_db, $oiopub_set;
	if(!isset($oiopub_set->install_check)) {
		$check = $oiopub_db->GetOne("SELECT value FROM " . $oiopub_set->dbtable_config . " WHERE name='hash'");
		if(strlen($check) == 16) {
			$oiopub_set->install_check = 1;
		}
	}
	if(isset($oiopub_set->install_check)) {
		return true;
	}
	return false;
}

//install check redirect
function oiopub_install_redirect() {
	global $oiopub_set;
	//platform check
	if($oiopub_set->platform != "standalone") {
		return;
	}
	//set vars
	$location = '';
	$is_installed = (bool) oiopub_install_check();
	$current_request = rtrim(str_replace(array("https://", "http://", "www.", "/index.php"), "", $oiopub_set->page_url), "/");
	$plugin_request = rtrim(str_replace(array("https://", "http://", "www.", "/index.php"), "", $oiopub_set->plugin_url), "/");	//platform check
	//not installed?
	if($is_installed === false) {
		//go to install?
		if($current_request != $plugin_request . "/install.php" && $current_request != $plugin_request . "/admin.php") {
			$location = $oiopub_set->plugin_url . "/install.php";
		}
	}
	//installed?
	if($is_installed !== false) {
		//go to admin?
		if($current_request == $plugin_request . "/install.php") {
			$location = $oiopub_set->plugin_url . "/admin.php";
		}
		//go to purchase?
		if($current_request == $plugin_request) {
			$location = $oiopub_set->plugin_url . "/purchase.php";
		}
	}
	//redirect user?
	if(!empty($location)) {
		header("Location: $location");
		exit();
	}
}

//install wrapper
function oiopub_install_wrapper() {
	global $oiopub_set, $oiopub_install;
	if(!isset($oiopub_install)) {
		include_once($oiopub_set->folder_dir . '/include/install.php');
		$oiopub_install = new oiopub_install();
	}
	$oiopub_install->install();
}

//uninstall wrapper
function oiopub_uninstall_wrapper() {
	global $oiopub_set, $oiopub_install;
	//demo mode?
	if($oiopub_set->demo) {
		return;
	}
	//get input source
	if(!$oiopub_set->version) {
		$array = $_GET;
	} else {
		$array = $_POST;
	}
	//preocess uninstall request
	if(isset($array['page']) && $array['page'] == 'oiopub-opts.php') {
		if(isset($array['do']) && $array['do'] == 'oiopub-remove') {
			if(oiopub_is_admin() && oiopub_auth_check() && oiopub_csrf_token()) {
				if(!isset($oiopub_install)) {
					include_once($oiopub_set->folder_dir . '/include/install.php');
					$oiopub_install = new oiopub_install();
				}
				$oiopub_install->uninstall();
			}
		}
	}
}

//manual deactivation
function oiopub_deactivate_wrapper() {
	global $oiopub_set;
	//demo mode?
	if($oiopub_set->demo) {
		return;
	}
	//process de-activation request
	if(oiopub_is_admin() && oiopub_auth_check()) {
		if(isset($_GET['oio-deactivate']) && $_GET['oio-deactivate'] == 1) {
			if(function_exists('oiopub_script_deactivate')) {
				oiopub_script_deactivate();
			}
		}
	}
}

//upgrade wrapper
function oiopub_upgrade_wrapper() {
	global $oiopub_set, $oiopub_install, $oiopub_version;
	if(oiopub_is_admin() && oiopub_auth_check()) {
		if(!empty($oiopub_set->version) && $oiopub_version > $oiopub_set->version) {
			if(!isset($oiopub_install)) {
				include_once($oiopub_set->folder_dir . '/include/install.php');
				$oiopub_install = new oiopub_install();
			}
			$oiopub_install->upgrade();
		}
	}
}

//upgrade script
function oiopub_upgrade_script($type="manual") {
	global $oiopub_set;
	if(oiopub_is_admin() && oiopub_auth_check()) {
		include_once($oiopub_set->folder_dir . "/include/upgrade.php");
		if($type == "auto") {
			if(!$oiopub_set->demo) {
				$upgrade = new oiopub_upgrade_auto;
				$upgrade->init();
			}
		} else {
			$upgrade = new oiopub_upgrade_manual;
			$upgrade->init();
		}
	}
}

/* CONFIG FUNCTIONS */

//get config option
function oiopub_get_config($name, $db_check=1) {
	global $oiopub_db, $oiopub_set;
	if(empty($name)) return;
	//clear res
	$rows = 0;
	$res = false;
	//prepare values
	$name = oiopub_clean($name);
	//get option
	if($db_check == 2) {
		$res = $oiopub_db->GetOne("SELECT value FROM " . $oiopub_set->dbtable_config . " WHERE name='$name'");
		$rows = $oiopub_db->num_rows;
	} elseif(isset($oiopub_set->$name)) {
		$res = $oiopub_set->$name;
		$rows = 1;
	} elseif($db_check == 1) {
		$res = $oiopub_db->GetOne("SELECT value FROM " . $oiopub_set->dbtable_config . " WHERE name='$name'");
		$rows = $oiopub_db->num_rows;
	}
	if($res === false || $rows == 0) {
		return false;
	}
	return $res;
}

//insert new config option
function oiopub_add_config($name, $value='', $api_load=0, $strip=0) {
	global $oiopub_db, $oiopub_set;
	if(empty($name)) return;
	//prepare values
	$name = oiopub_clean($name);
	$value_db = oiopub_prepare_insert($value, $strip);
	$api_load = intval($api_load);
	//check options table
	$value_check = oiopub_get_config($name, 2);
	//insert option
	if($value_check === false) {
		$oiopub_db->query("INSERT INTO " . $oiopub_set->dbtable_config . " (name,value,api_load) VALUES ('$name','$value_db','$api_load')");
		$oiopub_set->$name = $value;
		//flush cache
		$oiopub_db->CacheFlush("SELECT * FROM " . $oiopub_set->dbtable_config);
	}
}

//update config option
function oiopub_update_config($name, $value='', $api_load=0, $strip=0) {
	global $oiopub_db, $oiopub_set;
	if(empty($name)) return;
	//prepare values
	$continue = false;
	$name = oiopub_clean($name);
	$value_db = oiopub_prepare_insert($value, $strip);
	$api_load = intval($api_load);
	//check options table
	$value_check = oiopub_get_config($name, 2);
	//update option
	if($value_check === false) {
		$oiopub_db->query("INSERT INTO " . $oiopub_set->dbtable_config . " (name,value,api_load) VALUES ('$name','$value_db','$api_load')");
		$continue = true;
	} elseif($value_check !== $value) {
		$oiopub_db->query("UPDATE " . $oiopub_set->dbtable_config . " SET value='$value_db' WHERE name='$name'");
		$continue = true;
	}
	if($continue === true) {
		//option value
		$oiopub_set->$name = $value;
		//make api request
		oiopub_api_config($name);
		//flush cache
		$oiopub_db->CacheFlush("SELECT * FROM " . $oiopub_set->dbtable_config);
	}
}

//delete config option
function oiopub_delete_config($name) {
	global $oiopub_db, $oiopub_set;
	if(empty($name)) return;
	//prepare values
	$name = oiopub_clean($name);
	//delete option
	$oiopub_db->query("DELETE FROM " . $oiopub_set->dbtable_config . " WHERE name='$name'");
	//flush cache
	$oiopub_db->CacheFlush("SELECT * FROM " . $oiopub_set->dbtable_config);
}

//config api request
function oiopub_api_config($name) {
	global $oiopub_set, $oiopub_db, $oiopub_cron, $oiopub_api;
	//anything to process?
	if(empty($name) || empty($_POST)) {
		return;
	}
	//valid state?
	if(defined('OIOPUB_INSTALL') || defined('OIOPUB_UNINSTALL') || defined('OIOPUB_UPGRADE') || defined('OIOPUB_CRON')) {
		return;
	}
	//check api key
	if(strlen($oiopub_set->api_key) == 16 && $oiopub_set->api_valid == 1) {
		if(isset($oiopub_cron) && isset($oiopub_api)) {
			if(!$oiopub_cron->is_job(array(&$oiopub_api, "settings_send"))) {
				$api = $oiopub_db->GetOne("SELECT api_load FROM " . $oiopub_set->dbtable_config . " WHERE name='$name'");
				if($api == 1) {
					$oiopub_cron->add_job(array(&$oiopub_api, "settings_send"), time()+300, 0);
					return true;
				}
			}
		}
	}
	return false;
}

/* CLASS WRAPPERS */

//translate wrapper
function __oio($text, $params=array(), $lang=null) {
	global $oiopub_set;
	static $translate = null;
	//anything to process?
	if($translate === false) {
		return oiopub_translate_placeholders($text, $params);
	}
	//php5 check?
	if(!$translate && version_compare(PHP_VERSION, '5.0.0', '<')) {
		$translate = false;
		return oiopub_translate_placeholders($text, $params);
	}
	//lang file check
	if(!$translate && !is_file($oiopub_set->folder_dir . "/include/translate.php")) {
		$translate = false;
		return oiopub_translate_placeholders($text, $params);
	}
	//create object?
	if(!$translate) {
		include_once($oiopub_set->folder_dir . "/include/translate.php");
		$options = array( 'lang' => $oiopub_set->lang, 'lang_dir' => $oiopub_set->lang_dir );
		$translate = new oiopub_translate($options);
	}
	//translate text
	return $translate->get($text, $params, $lang);
}

//translate placeholders
function oiopub_translate_placeholders($text, $params=array()) {
	//loop through params
	foreach($params as $key=>$val) {
		//format key
		$key = is_numeric($key) ? $key + 1 : $key;
		//replace param
		$text = preg_replace('/%s/', $val, $text, 1);
		$text = preg_replace('/%' . $key . '/', $val, $text);
	}
	return $text;
}

//approvals wrapper
function oiopub_approve($func, $id) {
	global $oiopub_set, $oiopub_approvals;
	if(!isset($oiopub_approvals)) {
		include_once($oiopub_set->folder_dir . "/include/approvals.php");
		$oiopub_approvals = new oiopub_approvals;
	}
	if(method_exists($oiopub_approvals, $func)) {
		$oiopub_approvals->{$func}($id);
		oiopub_flush_cache();
		return true;
	}
	return false;
}

//filesystem wrapper
function oiopub_filesystem($temp_dir='') {
	global $oiopub_set;
	include_once($oiopub_set->folder_dir . "/include/filesystem.php");
	$filesystem = new oiopub_filesystem($temp_dir);
	return $filesystem;
}

//parser wrapper
function oiopub_parser($feed) {
	global $oiopub_set;
	include_once($oiopub_set->folder_dir . "/include/xml.php");
	$parser = new oiopub_xml;
	return $parser->xml_load_file($feed);
}

//reports wrapper
function oiopub_reports($do, $type, $args=array()) {
	global $oiopub_set;
	include_once($oiopub_set->folder_dir . "/include/reports.php");
	$reports = new oiopub_reports;
	$func = $do . "_" . $type;
	if(method_exists($reports, $func)) {
		return $reports->{$func}($args);
	}
	return false;
}

//payment wrapper
function oiopub_payment($name) {
	global $oiopub_set, $oiopub_module;
	//get class
	$class = "oiopub_payment_" . $name;
	//include payment class
	include_once($oiopub_set->folder_dir . "/include/payment.php");
	//include processor class
	if($oiopub_module->$name == 1) {
		include_once($oiopub_set->modules_dir . "/" . $name . "/include/ipn.class.php");
	}
	//init class
	if(class_exists($class)) {
		$payment = new $class();
		return $payment;
	} else {
		die($name. " payment processor does not exist!");
	}
}

//client wrapper
function oiopub_client() {
	global $oiopub_set;
	static $client = null;
	if($client === null) {
		include_once($oiopub_set->folder_dir . "/include/spam.php");
		$client = new oiopub_spam();
	}
	return $client;
}

//check spam var
function oiopub_spam_check($var='', $empty=1) {
	$client = oiopub_client();
	return $client->allow_var($var, $empty);
}

//browser check
function oiopub_browser_check() {
	$client = oiopub_client();
	return $client->is_browser();
}

//spider check
function oiopub_spider_check() {
	$client = oiopub_client();
	return $client->is_spider();
}

/* SANITIZER FUNCTIONS */

//escape string
function oiopub_escape($str) {
	$str = strtr($str, array( '\\' => '\\\\', "'" => "\'", '"' => '\"', "\x1a" => '\x1a', "\x00" => '\x00' ));
	return $str;
}

//clean input
function oiopub_clean($input, $strip=1, $escape=1, $wspace=1) {
	if(oiopub_has_magic_quotes()) {
		$input = stripslashes($input);
	}
	if($strip == 1) {
		$input = strip_tags($input);
		$input = htmlspecialchars($input, ENT_QUOTES);
	}
	if($escape == 1) {
		$input = oiopub_escape($input);
	}
	if($wspace == 1) {
		$input = preg_replace('|\s+|', ' ', $input);
	}
	return trim($input);
}

//globals var
function oiopub_var($name, $type, $default='', $clean=1) {
	$name = oiopub_clean($name);
	$type = "_" . strtoupper($type);
	if(isset($GLOBALS[$type])) {
		if(isset($GLOBALS[$type][$name])) {
			if(!empty($GLOBALS[$type][$name])) {
				return ($clean == 1 ? oiopub_clean($GLOBALS[$type][$name]) : $GLOBALS[$type][$name]);
			} elseif(!empty($default)) {
				return ($clean == 1 ? oiopub_clean($default) : $default);
			}
		}
	}
	return '';
}

//prepare db insert
function oiopub_prepare_insert($value, $strip=0) {
	if(is_array($value) || is_object($value)) {
		$value = serialize($value);
	} else {
		if(oiopub_has_magic_quotes()) {
			$value = stripslashes($value);
		}
		if($strip == 1) {
			$value = strip_tags($value);
		}
	}
	return oiopub_escape($value);
}

//decode input
function oiopub_decode($input) {
	$input = urldecode($input);
	$input = oiopub_clean($input);
	return $input;
}

/* COMMON FUNCTIONS */

//csrf token
function oiopub_csrf_token($var='csrf') {
	global $oiopub_set;
	//turned off?
	if(!$oiopub_set->csrf) {
		return true;
	}
	//init csrf
	static $csrf = false;
	//check $_POST requests
	if(!$csrf && !empty($_POST) && !defined('OIOPUB_INSTALL')) {
		if(!isset($_POST[$var]) || empty($_POST[$var]) || $_POST[$var] != $oiopub_set->csrf_key) {
			echo '<p><b>Invalid Request</b></p>' . "\n";
			echo '<p>Please refresh the page and try again</p>' . "\n";
			die();
		}
	}
	//update token?
	if(!$csrf) {
		oiopub_update_config('csrf_key', md5(microtime() . uniqid(rand(), true) . $_SERVER['HTTP_USER_AGENT']));
		$csrf = true;
	}
	//return token
	return $oiopub_set->csrf_key;
}

//mailer function
function oiopub_mail_client($to='', $subject='', $message='', $from='', $html=null) {
	global $oiopub_set;
	//set vars
	$from = empty($from) ? $oiopub_set->admin_mail : $from;
	$safe_mode = (bool) @ini_get('safe_mode');
	$params = "-f" . $from;
	//valid email?
	if(empty($to) || empty($subject) || empty($message) || empty($from)) {
		return false;
	}
	//set headers
	$headers  = 'From: ' . $oiopub_set->site_name . ' <' . $from . '>' . "\n";
	$headers .= 'Reply-To: ' . $from . "\n";
	$headers .= 'X-Mailer: PHP/' . phpversion() . "\n";
	//check for html?
	if($html === null && strip_tags($message) !== $message) {
		$html = true;
	}
	//use html?
	if($html) {
		$headers .= 'MIME-Version: 1.0' . "\n";
		$headers .= 'Content-type: text/html; charset=utf-8' . "\n";
		//add lines breaks?
		if(strip_tags($message) === strip_tags($message, '<p><br><div><table>')) {
			$message = str_replace("\n", "<br />", $message);
		}
	} else {
		$headers .= 'Content-type: text/plain; charset=utf-8' . "\n";
	}
	//use WP mail?
	if(function_exists('wp_mail')) {
		return wp_mail($to, $subject, $message, $headers);
	}
	//safe mode?
	if($safe_mode) {
		return @mail($to, $subject, $message, $headers);
	} else {
		return @mail($to, $subject, $message, $headers, $params);
	}
}

//flush cache
function oiopub_flush_cache($dir='') {
	global $oiopub_cache;
	//OIO cache
	if(isset($oiopub_cache)) {
		$oiopub_cache->flush(0, '', $dir);
	}
	//wp-cache (flush)
	if(function_exists('wp_cache_flush')) {
		@wp_cache_flush();
	}
	//wp-cache (clear)
	if(function_exists('wp_cache_clear_cache')) {
		@wp_cache_clear_cache();
	}
	//w3-total-cache
	if(function_exists('w3tc_pgcache_flush')) {
		@w3tc_pgcache_flush();
	}
	return true;
}

//flush output
function oiopub_flush($sleep=0) {
	//@ob_flush();
	@flush();
	if($sleep > 0) {
		@sleep($sleep);
	}
}

//session start
function oiopub_session_start() {
	global $oiopub_set;
	//session exists?
	if(@session_id()) {
		return true;
	}
	//update settings?
	if(function_exists('ini_set')) {
		@ini_set('session.cookie_path', "/");
		@ini_set('session.use_trans_sid', 0);
		@ini_set('session.use_only_cookies', 1);
		@ini_Set('session.cookie_lifetime', 3600);
		@ini_set('session.gc_maxlifetime', 7200);
		@ini_set('session.gc_probability', 1);
		@ini_set('session.gc_divisor', 100);
	}
	//set module name?
	if(!session_module_name()) {
		session_module_name('files');
	}
	//is file module?
	if(session_module_name() == 'files') {
		//check saved path
		$path = @session_save_path();
		//is path writable?
		if(!$path || !@is_writable($path)) {
			//set default path
			$default = $oiopub_set->cache_dir . "/sessions";
			//create path?
			if(!is_dir($default)) {
				if(!@mkdir($default, 0755) || !@is_writable($default)) {
					$default = "/tmp";
				}
			}
			//update path
			@session_save_path($default);
		}
	}
	//start session	
	return @session_start();
}

//serialize array / object
function oiopub_serialize($data) {
	if(is_array($data) || is_object($data)) {
		return serialize($data);
	}
	return $data;
}

//unserialize array / object
function oiopub_unserialize($data) {
	if(!empty($data) && is_string($data)) {
		$data = trim($data);
		if(preg_match("/^[adobis]:[0-9]+:.*[;}]/si", $data)) {
			return unserialize($data);
		}
		if($data == 'a:0:{}') {
			return unserialize($data);
		}
	}
	return $data;
}

//proper stripslashes
function oiopub_stripslashes($data) {
	if(is_object($data)) return $data;
	return (is_array($data) ? array_map('oiopub_stripslashes', $data) : stripslashes($data));
}

//create random id
function oiopub_rand($length, $num=0) {
    $rand = "";
    if($num == 0) {
		$possible = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    } else {
		$possible = "0123456789";
    }
    for($z=0; $z < $length; $z++) {
        $char = substr($possible, rand(0, strlen($possible)-1), 1);
        $rand .= $char;
    }
    return $rand;
}

//hmac hash
function oiopub_hmac($type, $data, $key) {
	$b = 64;
	if(strlen($key) > $b) {
		$key = pack("H*", $type($key));
	}
	$key  = str_pad($key, $b, chr(0x00));
	$ipad = str_pad('', $b, chr(0x36));
	$opad = str_pad('', $b, chr(0x5c));
	$k_ipad = $key ^ $ipad;
	$k_opad = $key ^ $opad;
	return $type($k_opad  . pack("H*", $type($k_ipad . $data)));
}

//looping array shift
function oiopub_array_shift($array, $shifts=1) {
	for($z=0; $z < $shifts; $z++) {
		$elm = array_shift($array);
	}
	return $elm;
}

//string length limit
function oiopub_strlimit($var, $max) {
	$length = strlen($var);
	if($length > $max) {
		$var = substr($var, 0, $max);
		$var .= '...';
	}
	return $var;
}

//word count function
function oiopub_count_words($str){
	$words = 0;
	$str = eregi_replace(" +", " ", $str);
	$array = explode(" ", $str);
	for($i=0; $i < oiopub_count($array); $i++){
		if(eregi("[0-9A-Za-zÀ-ÖØ-öø-ÿ]", $array[$i])){
			$words++;
		}
	}
	return $words;
}

//array to object
function oiopub_arr2obj($array=null) {
	$tmp = new stdClass;
	if(!is_array($array)) return $array;
	foreach($array as $key => $value) {
		if(is_array($value)){
			$tmp->$key = oiopub_arr2obj($value);
		} else {
			if(is_numeric($key)) {
				$key = '_' .$key;
			}
			$tmp->$key = $value;
		}
	}
	return $tmp;
}

//make sure array is correct
function oiopub_array_format($data, $empty=1) {
	$array = array();
	if(!empty($data)) {
		foreach($data as $key => $val) {
			$val = trim($val);
			if($empty != 1 || (!empty($val) && $empty == 1)) {
				$array[$key] = $val;
			}
		}
	}
	return $array;
}

//get hostname
function oiopub_hostname() {
	$host = strip_tags($_SERVER['HTTP_HOST']);
	$host = @parse_url("http://".$host);
	$host = $host['host'];
	preg_match('/[^.]+\.[^.]+$/', $host, $matches);
	return strtolower($matches[0]);
}

//nice url
function oiopub_nice_url($url) {
	$url = strtolower($url);
	$url = preg_replace('/[^a-zA-Z0-9-\s]/', '', $url);
	$url = str_replace(" ", "-", $url);
	$url = str_replace("--", "-", $url);
	return $url;
}

//rand hash
function oiopub_hash($string) {
	global $oiopub_set;
	return md5($string . $oiopub_set->hash);
}

//get / set subid
function oiopub_subid($channel, $type) {
	global $item;
	//valid request?
	if($channel <= 0 || $type <= 0) {
		return false;
	}
	//subid already exists?
	if(isset($item) && $item->item_subid) {
		return $item->item_subid;
	}
	//start session
	oiopub_session_start();
	//get subid
	if($sub_id = oiopub_var('subid', 'get')) {
		$_SESSION['subid_' . $channel . '_' . $type] = $sub_id;
	} else {
		$sub_id = oiopub_var('subid_' . $channel . '_' . $type, 'session');
	}
	//return
	return $sub_id;
}

//get file contents
function oiopub_file_contents($file, $buffer=4096, $timeout=5) {
	//set vars
	$blocking = 1;
	$context = null;
	$result = false;
	//clear cache
	@clearstatcache();
	//default timeout
	@ini_set('default_socket_timeout', $timeout); 
	//create context?
	if(function_exists('stream_context_create')) {
		$context = @stream_context_create(array(
			'http' => array(
				'timeout' => $timeout,
			),
		));
	}
	//which transport method?
	if(function_exists('curl_init') && strpos($file, 'http') === 0) {
		//use CURL
		$ch = @curl_init();
		curl_setopt($ch, CURLOPT_URL, $file);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		$result = @curl_exec($ch);
		@curl_close($ch);
	} else {
		//open handle
		if($fp = @fopen($file, 'rb', false, $context)) {
			//set stream options?
			if(function_exists('stream_set_timeout')) {
				@stream_set_timeout($fp, $timeout);
				@stream_set_blocking($fp, $blocking);
			}
			//get result
			while($tmp = @fread($fp, $buffer)) {
				//check time out?
				if(function_exists('stream_get_meta_data')) {
					//get meta data
					$info = @stream_get_meta_data($fp);
					//has timed out?
					if($info && $info['timed_out']) {
						$result = false;
						break;
					}
				}
				//add data
				$result .= $tmp;
			}
			//close
			fclose($fp);
		}
	}
	//return
	return $result;
}

//dropdown menu one
function oiopub_dropmenu_k($array, $name, $current, $width='', $onchange='', $sort=0, $tabindex='') {
	$style = "";
	$extras = "";
	$onchange = str_replace('"', "'", $onchange);
	if(empty($array)) return;
	if($sort == 1) sort($array);
	if(!empty($width)) $style .= ' width:' . $width . 'px;';
	if(!empty($onchange)) $extras .= ' onchange="' . $onchange . '"';
	if(!empty($tabindex)) $extras .= ' tabindex="' . $tabindex . '"';
	if(is_array($current)) {
		$extras .= ' multiple';
		$style .= ' height: 80px;';
		$name = str_replace("[]", "", $name) . "[]";
	} else {
		$extras .= ' size="1"';
		$current = array( $current );
	}
	$menu = "<select name=\"" . $name . "\" style=\"" . $style . "\"" . $extras . ">\n";
	foreach($array as $key) {
		$menu .= "<option value=\"" . $key . "\"";
		if(in_array($key, $current)) {
			$menu .= " selected";
		}
		$menu .= "> " . $key . "</option>\n";
    }
    $menu .= "</select>\n";
    return $menu;
}

//dropdown menu two
function oiopub_dropmenu_kv($array, $name, $current, $width='', $onchange='', $sort=0, $tabindex='') {
	$style = "";
	$extras = "";
	$onchange = str_replace('"', "'", $onchange);
	if(empty($array)) return;
	if($sort == 1) asort($array);
	if(!empty($width)) $style .= ' width:' . $width . 'px;';
	if(!empty($onchange)) $extras .= ' onchange="' . $onchange . '"';
	if(!empty($tabindex)) $extras .= ' tabindex="' . $tabindex . '"';
	if(is_array($current)) {
		$extras .= ' multiple';
		$style .= ' height: 80px;';
		$name = str_replace("[]", "", $name) . "[]";
	} else {
		$extras .= ' size="1"';
		$current = array( $current );
	}
	$menu = "<select name=\"" . $name . "\" style=\"" . $style . "\"" . $extras . ">\n";
	foreach($array as $key => $value) {
		$menu .= "<option value=\"" . $key . "\"";
		if(in_array($key, $current)) {
			$menu .= " selected";
		}
		$menu .= "> " . $value . "</option>\n";
    }
    $menu .= "</select>\n";
    return $menu;
}

//count data rows
function oiopub_count($data) {
	return (!empty($data) ? count($data) : 0);
}

//rand number array
function oiopub_mrand($l, $h, $t, $len=false) {
	if($l > $h){
		$a = $l;$b = $h;$h = $a;$l = $b;
	}
	if((($h-$l)+1) < $t || $t <= 0) {
		return false;
	}
	$n = array();
	if($len > 0) {
		if(strlen($h) < $len && strlen($l) < $len) {
			return false;
		}
		if(strlen($h-1) < $len && strlen($l-1) < $len && $t > 1) {
			return false;
		}
		do {
			$x = rand($l, $h);
			if(!in_array($x, $n) && strlen($x) == $len) {
				$n[] = $x;
			}
		} while(oiopub_count($n) < $t);
	} else {
		do{
			$x = rand($l, $h);
			if(!in_array($x, $n)){
				$n[] = $x;
			}
		} while(oiopub_count($n)<$t);
	}
	return $n;
}

//string between
function oiopub_string_between($start, $end, $content) {
	$r = explode($start, $content);
	if(isset($r[1])) {
		$r = explode($end, $r[1]);
		return $r[0];
	}
	return '';
}

//validate url
function oiopub_validate_url($url, $timeout=10) {
	//set vars
	$data = '';
	$url = trim($url);
	//parse url?
	if(!$parse = @parse_url($url)) {
		return false;
	}
	//get url scheme
	switch($parse['scheme']) {
		case 'https': $scheme = 'ssl://'; $port = 443; break;
		case 'http': default: $scheme = ''; $port = 80;   
	}
	//open stream
	if($fp = @fsockopen($scheme . $parse['host'], $port, $errno, $errstr, $timeout)) {
		//build request
		$request  = 'GET ' . (isset($parse['path'])? $parse['path']: '/') . (isset($parse['query'])? '?' . $parse['query']: '') . " HTTP/1.0\r\n";
		$request .= 'Host: ' . $parse['host'] . "\r\n";
		$request .= 'User-Agent: ' . strip_tags($_SERVER['HTTP_USER_AGENT']) . "\r\n";
		$request .= 'Connection: close' . "\r\n\r\n";
		//send request
		fputs($fp, $request);   
		//set read timeout
		stream_set_timeout($fp, $timeout);		
		//read response
		while(!feof($fp)) {
			//get next line
			$line = fgets($fp, 4096);
			//get meta data
			$info = stream_get_meta_data($fp);
			//has timed out?
			if($line === false || $info['timed_out']) {
				break;
			}
			//add to data
			$data .= $line;
		}
		//close
		fclose($fp);
	}
	//return result
	return $data ? $data : false;
}

//image url validate
function oiopub_image_url($url) {
	$url = strtolower($url);
	if(!preg_match("/.(gif|png|jpg|jpeg|swf)$/", $url)) {
		return false;
	}
	return true;
}

//video url validate
function oiopub_video_url($url) {
	if(preg_match('!^http://www.youtube.com\/watch\?v\=(.*)$!i', $url)) {
		$explode = explode("v=", $url);
		return "youtube|".str_replace("/", "", $explode[1]);	
	}
	if(preg_match('!^http://www.youtube.com\/v\/(.*)$!i', $url)) {
		$explode = explode("v/", $url);
		return "youtube|".str_replace("/", "", $explode[1]);
	}
	return false;
}

//check db table exist
function oiopub_checktable($table) {
	global $oiopub_db;
	$check = $oiopub_db->GetOne("SHOW TABLES LIKE '" . $table . "'");
	if($check == $table) {
		return true;
	}
	return false;
}

//header redirect
function oiopub_header_redirect($url='') {
	global $oiopub_set;
	$url = (!empty($url) ? oiopub_clean($url) : $oiopub_set->request_uri);
	ob_end_clean();
	set_time_limit(0);
	ignore_user_abort(TRUE);
	header("Connection: close");
	header("Location: ". $url);
	ob_start();
	$size = ob_get_length();
	header("Content-Length: $size");
	ob_end_flush();
	flush();
}

//captcha function
function oiopub_captcha() {
	global $oiopub_set;
    do {
        $num1 = rand(0, 9);
        $num2 = rand(0, 9);
        $operator = rand(0, 1);
        $type = rand(0, 3);
        if($operator == 0) {
            switch($type) {
                case 0:
                    $question = $num1 . " - " . $num2;
                    break;
                case 1:
                    $question = $num1 . " " . __oio("minus") . " " . $num2;
                    break;
                case 2:
                case 3:
                    $question = __oio("Subtract") . " " . $num2 . " " . __oio("from") . " " . $num1;
                    break;
            }
            $answer = $num1 - $num2;
        } else {
            switch($type) {
                case 0:
                    $question = $num1 . " + " . $num2;
                    break;
                case 1:
                    $question = $num1 . " " . __oio("plus") . " " . $num2;
                    break;
                case 2:
                    $question = __oio("Add") . " " . $num1 . " " . __oio("and") . " " . $num2;
                    break;
                case 3:
                    $question = __oio("The sum of") . " " . $num1 . " " . __oio("and") . " " . $num2;
                    break;
            }
            $answer = $num1 + $num2;
        }
    } while ($answer < 0);
    $answer = md5(md5($answer) . $oiopub_set->hash);
    return array('question' => $question, 'answer' => $answer);
}

//get months
function oiopub_get_months() {
	$array = array();
	$array[0] = "- month -";
	$array[1] = "January";
	$array[2] = "February";
	$array[3] = "March";
	$array[4] = "April";
	$array[5] = "May";
	$array[6] = "June";
	$array[7] = "July";
	$array[8] = "August";
	$array[9] = "September";
	$array[10] = "October";
	$array[11] = "November";
	$array[12] = "December";
	return $array;
}

//validate email
function oiopub_validate_email($email, $check_dns=false) {
	//regex pattern
	$pattern = '/^(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){255,})(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){65,}@)(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22))(?:\\.(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-+[a-z0-9]+)*\\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-+[a-z0-9]+)*)|(?:\\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\\]))$/iD';
	//is valid?
	if(!preg_match($pattern, $email)) {
		return false;
	}
	//check dns?
	if($check_dns && function_exists('checkdnsrr')) {
		if(!checkdnsrr($domain, "MX") && !checkdnsrr($domain, "A")) {
			return false;
		}
	}
	//success
	return true;
}

//email readable
function oiopub_email_readable($text='') {
	$text = stripslashes(str_replace("|", "\r\n", $text));
	return $text;
}

//email storage
function oiopub_email_storage($text='') {
	$text = oiopub_clean(str_replace("\r\n", "|", $text), 0, 1);
	return $text;
}

//get domain
function oiopub_domain($url) {
	$res = '';
	$parse = @parse_url($url);
	if(!empty($parse) && !empty($parse['host'])) {
		$res = $parse['host'];
	}
	return $res;
} 

//get domain base
function oiopub_domain_base($url) {
	$base_domain = '';
	$G_TLD = array('biz','com','edu','gov','info','int','mil','name','net','org','aero','asia','cat','coop','jobs','mobi','museum','pro','tel','travel','arpa','root','berlin','bzh','cym','gal','geo','kid','kids','lat','mail','nyc','post','sco','web','xxx','nato','example','invalid','localhost','test','bitnet','csnet','ip','local','onion','uucp','co');
	$C_TLD = array('ac','ad','ae','af','ag','ai','al','am','an','ao','aq','ar','as','at','au','aw','ax','az','ba','bb','bd','be','bf','bg','bh','bi','bj','bm','bn','bo','br','bs','bt','bw','by','bz','ca','cc','cd','cf','cg','ch','ci','ck','cl','cm','cn','co','cr','cu','cv','cx','cy','cz','de','dj','dk','dm','do','dz','ec','ee','eg','er','es','et','eu','fi','fj','fk','fm','fo','fr','ga','gd','ge','gf','gg','gh','gi','gl','gm','gn','gp','gq','gr','gs','gt','gu','gw','gy','hk','hm','hn','hr','ht','hu','id','ie','il','im','in','io','iq','ir','is','it','je','jm','jo','jp','ke','kg','kh','ki','km','kn','kr','kw','ky','kz','la','lb','lc','li','lk','lr','ls','lt','lu','lv','ly','ma','mc','md','mg','mh','mk','ml','mm','mn','mo','mp','mq','mr','ms','mt','mu','mv','mw','mx','my','mz','na','nc','ne','nf','ng','ni','nl','no','np','nr','nu','nz','om','pa','pe','pf','pg','ph','pk','pl','pn','pr','ps','pt','pw','py','qa','re','ro','ru','rw','sa','sb','sc','sd','se','sg','sh','si','sk','sl','sm','sn','sr','st','sv','sy','sz','tc','td','tf','tg','th','tj','tk','tl','tm','tn','to','tr','tt','tv','tw','tz','ua','ug','uk','us','uy','uz','va','vc','ve','vg','vi','vn','vu','wf','ws','ye','yu','za','zm','zw','eh','kp','me','rs','um','bv','gb','pm','sj','so','yt','su','tp','bu','cs','dd','zr');
	if(!$full_domain = oiopub_domain($url)) {
		return $base_domain;
	}
	$DOMAIN = array_reverse(explode('.', $full_domain));
	if(count($DOMAIN) == 4 && is_numeric($DOMAIN[0]) && is_numeric($DOMAIN[3])) {
		return $full_domain;
	}
	if(count($DOMAIN) <= 2) {
		return $full_domain;
	}
	if(in_array($DOMAIN[0], $C_TLD) && in_array($DOMAIN[1], $G_TLD) && $DOMAIN[2] != 'www') {
		$full_domain = $DOMAIN[2] . '.' . $DOMAIN[1] . '.' . $DOMAIN[0];
	} else {
		$full_domain = $DOMAIN[1] . '.' . $DOMAIN[0];;
	}
	return $full_domain;
}

/* PURCHASE FUNCTIONS */

//output files
function oiopub_output_files() {
	global $oiopub_set;
	if(!oiopub_is_admin()) {
		//geo-location
		oiopub_geolocation();
		//load code hacks
		if(@file_exists($oiopub_set->folder_dir . "/hacks.php")) {
			include_once($oiopub_set->folder_dir . "/hacks.php");
		}
		//load output files
		include_once($oiopub_set->folder_dir . "/include/output.php");
		include_once($oiopub_set->folder_dir . "/include/legacy.php");
		//init inline ads
		if(class_exists('oiopub_inline') && oiopub_inline) {
			new oiopub_inline();
		}
	}
}

//mail templates wrapper
function oiopub_mail_templates($request=1) {
	global $oiopub_set, $oiopub_install;
	if(!isset($oiopub_install)) {
		include_once($oiopub_set->folder_dir . '/include/install.php');
		$oiopub_install = new oiopub_install;
	}
	$oiopub_install->mail_data();
	$oiopub_install->mail_templates($request);
}

//download file
function oiopub_download_file($filename, $ctype, $id, $rand) {
	global $oiopub_db, $oiopub_set;
	$filename = "downloads/".$filename;
	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-Control: private", false);
	header("Content-Type: $ctype");
	header("Content-Disposition: attachment; filename=\"" . basename($filename) . "\";");
	header("Content-Transfer-Encoding: binary");
	header("Content-Length: " . @filesize($filename));
	set_time_limit(0);
	@readfile($filename);
	$oiopub_db->query("UPDATE " . $oiopub_set->dbtable_purchases . " SET published_status=published_status+1 WHERE item_id='$id' AND rand_id='$rand'");
}

function oiopub_image_display($image_url, $link_url, $width, $height, $border=0, $alt='', $id='', $class='', $style='') {
	global $oiopub_set;
	//width attr: %age?
	if(strpos($width, '%') !== false) {
		$style .= $width ? ' width="' . $width . '"' : '';
		$width = '';
	} else {
		$width = $width ? ' width="' . $width . '"' : '';
	}
	//height attr: %age?
	if(strpos($height, '%') !== false) {
		$style .= $height ? ' height="' . $height . '"' : '';
		$height = '';
	} else {
		$height = $height ? ' height="' . $height . '"' : '';
	}
	//other attr
	$id = $id ? ' id="' . $id . '"' : '';
	$alt = ' alt="' . stripslashes($alt) . '"';
	$border = ' border="' . (int) $border . '"';
	$class = $class ? ' class="' . $class . '"' : '';
	$style = $style ? ' style="' . $style . '"' : '';
	//set vars
	$output = '';
	$ext = substr(strrchr($image_url, '.'), 1);
	$is_video = (bool) (strpos($ext, '/') !== false);
	//object or image?
	if($is_video || $ext == 'swf' || $ext == '') {
		//build url
		if($is_video) {
			$url = $image_url;
		} else {
			$url = $image_url . '?clickTAG=' . urlencode($link_url);
		}
		//flash output
		$output .= '<object type="application/x-shockwave-flash" data="' . $url . '"' . $width . $height . $id . $class . '>' . "\n";
		$output .= '<param name="movie" value="' . $url . '" />' . "\n";
		$output .= '<param name="quality" value="high" />' . "\n";
		$output .= '<param name="wmode" value="transparent" />' . "\n";
		$output .= '<param name="allowscriptaccess" value="always" />' . "\n";
		$output .= '</object>';
	} else {
		//image output
		$output .= '<img src="' . $image_url . '"' . $width . $height . $border . $id . $class . $alt . $style . ' />';
	}
	return $output;
}

//image display
function oiopub_image_display_test($image_url, $link_url, $width, $height, $border=0, $alt='', $id='', $class='', $style='') {
	global $oiopub_set;
	//get options
	$alt = empty($alt) ? ' alt=""' : ' alt="' . stripslashes($alt) . '"';
	$id = empty($id) ? '' : ' id="' . $id . '"';
	$class = empty($class) ? '' : ' class="' . $class . '"';
	//get ext
	$ext = substr(strrchr($url, '.'), 1);
	$is_video = (bool) (strpos($ext, '/') !== false);
	//clear output
	$output = '';
	if($is_video || $ext == 'swf' || $ext == '') {
		//get dimensions
		$width = empty($width) ? '' : ' width:100%; max-width="' . $width . '"';
		$height = empty($height) ? '' : ' height:auto; max-height="' . $height . '"';
		$style = empty($style) ? '' : ' style="' . $style . '"';
		//build url
		if($is_video) {
			$url = $image_url;
		} else {
			$url = $image_url . '?clickTAG=' . urlencode($link_url);
		}
		//flash output
		$output .= '<object type="application/x-shockwave-flash" data="' . $url . '"' . $width . $height . $id . $class . $style . '>' . "\n";
		$output .= '<param name="movie" value="' . $url . '" />' . "\n";
		$output .= '<param name="quality" value="high" />' . "\n";
		$output .= '<param name="wmode" value="transparent" />' . "\n";
		$output .= '<param name="allowscriptaccess" value="always" />' . "\n";
		$output .= '</object>';
	} else {
		//get styling
		$style  = empty($width) ? '' : 'width:100%; max-width:' . (is_numeric($width) ? $width . 'px' : $width) . '; ';
		$style .= empty($height) ? '' : 'height:auto; max-height:' . (is_numeric($height) ? $height . 'px' : $height) . '; ';
		$style .= 'border:' . (int) $border . 'px;';
		//put it all together
		$style  = empty($style) ? '' : ' style="' . $style . '"';
		//image output
		$output .= '<img src="' . $image_url . '"' . $alt . $id . $class . $style . ' />';
	}
	return $output;
}

//e-mail placeholders
function oiopub_email_placeholder($text, $user) {
	global $oiopub_set, $oiopub_hook, $oiopub_module, $oiopub_mail_extras;
	//define mail
	if(!defined('OIOPUB_MAIL')) {
		define('OIOPUB_MAIL', 1);
	}
	//get purchase data
	$item = oiopub_adtype_info($user);
	//direct purchase, or API?
	if($oiopub_set->paytime == 1 || $user->submit_api == 1) {
		$text = str_replace("%paytime%", "", $text);
	} else {
		$text = str_replace("%paytime%", "", $text);
	}
	//feedback url?
	if(!empty($oiopub_set->feedback)) {
		$feedback = "Got feedback? " . $oiopub_set->feedback;
	} else {
		$feedback = '';
	}
	//set vars
	$oiopub_mail_extras = '';
	$payment_url = $oiopub_set->pay_url . "?rand=" . $user->rand_id;
	$stats_url = $oiopub_set->plugin_url . "/stats.php?email=" . urlencode($user->adv_email) . "&rand=" . $user->rand_id;
	//mail hook
	$oiopub_hook->fire('mail_client', $user->item_id);
	//add stats link?
	if($user->method == "approve" && $oiopub_module->tracker == 1 && $oiopub_set->tracker['enabled'] == 1) {
		if(in_array($user->item_channel, array( 2,3,5 ))) {
			$oiopub_mail_extras .= "\n\n";
			$oiopub_mail_extras .= __oio("Once your ad is live, you can also view stats at any time using the link below") . ":";
			$oiopub_mail_extras .= "\n\n";
			$oiopub_mail_extras .= $stats_url;	
		}
	}
	//check queue
	if($user->item_status == -2) {
		$est = oiopub_queue_estimate($user->item_channel, $user->item_type, $user->payment_time);
		if(!empty($est)) {
			//is queued
			$oiopub_mail_extras .= "\n\n";
			$oiopub_mail_extras .= __oio("Please note that your ad will not appear immediately, as you have chosen to reserve a future ad slot. You will receive another email once your ad becomes active.");
			//show estimated date?
			if(!empty($est['date'])) {
				$oiopub_mail_extras .= "\n\n";
				$oiopub_mail_extras .= __oio("The estimated publishing date is %s", array( $est['date'] ));
				if($user->payment_status != 1) {
					$oiopub_mail_extras .= " (" . __oio("please note that this date is based on immediate payment by you") . ").";
				}
			}
		}
	}
	//standardise new lines
	$text = str_replace("\r\n", "\n", $text);
	//remove 'make payment' text?
	if(preg_replace('/[^0-9\.\s]/', '', $user->cost) <= 0) {
		$user->cost = '0.00';
		$temp_text = "If you have not already done so, you will need to make a payment by going to the URL below, before your %item% will be published:\n\n%payment_url%";
		$text = str_replace($temp_text, "", $text);
	}
	//remove 'for review' text?
	if($user->item_status == 1) {
		$temp_text = "Your %item% will be reviewed shortly, and you will be notified when this has happened.";
		$text = str_replace($temp_text, "", $text);
	}
	//do replacements
	$replace1 = array("%username%", "%user_email%", "%item%", "%item_cost%", "%item_duration%", "%item_model%", "%item_title%", "%processor%", "%payment_url%", "%stats_url%", "%purchase_code%", "%site_name%", "%site_url%", "%preview_url%", "%feedback%", "%extras%");
	$replace2 = array($user->adv_name, $user->adv_email, $item['item'], $user->cost, $user->item_duration, $user->item_model, $item['type'], $user->payment_processor, $payment_url, $stats_url, $user->rand_id, $oiopub_set->site_name, $oiopub_set->site_url, $item['preview'], $feedback, trim($oiopub_mail_extras));
	$text = str_replace($replace1, $replace2, $text);
	$text = str_replace("\n\n\n", "\n", $text);
	$text = ucfirst(stripslashes($text));
	unset($oiopub_mail_extras);
	return $text;
}

//alert output
function oiopub_alert_output($item, $type) {
	$item_alert = ''; $output = '';
	if($type == "custom") { $type1 = "services"; } else { $type1 = $type."s"; }
	if(isset($item['pending']) && $item['pending'] > 0) {
		$item_alert .= "<a href='admin.php?page=oiopub-manager.php&opt=$type'>" . $item['pending'] . "</a> pending $type1, ";
	}
	if(isset($item['unpaid']) && $item['unpaid'] > 0) {
		$item_alert .= "<a href='admin.php?page=oiopub-manager.php&opt=$type'>" . $item['unpaid'] . "</a> unpaid $type1, ";
	}
	if(isset($item['unpublished']) && $item['unpublished'] > 0) {
		$item_alert .= "<a href='admin.php?page=oiopub-manager.php&opt=$type'>" . $item['unpublished']. "</a> unpublished $type1, ";
	}
	if(isset($item['badpay']) && $item['badpay'] > 0) {
		$item_alert .= "<a href='admin.php?page=oiopub-manager.php&opt=$type'>" . $item['badpay'] . "</a> invalid $type1, ";
	}
	if(!empty($item_alert)) {
		$output = substr($item_alert, 0, -2) . "<br />\n";
	}
	return $output;
}

//extract ad data
function oiopub_adtype_info($data) {
	global $oiopub_set;
	$array = array();
	//item ID
	$array['id'] = $data->item_id;
	//item channel
	if($data->item_channel == 1) {
		$array['item'] = __oio("Paid Review");
		$array['type'] = __oio("Paid Review");
		$array['url'] = $oiopub_set->site_url;
		$array['preview'] = $oiopub_set->plugin_url . "/preview.php?id=" . $data->rand_id;
		$array['aff'] = 2;
	} elseif($data->item_channel == 2) {
		$lz = "links_" . $data->item_type;
		$array['item'] = __oio("Text Ad");
		$array['type'] = __oio("Text Ad") . ", " . $oiopub_set->{$lz}['title'];
		$array['url'] = $data->item_url;
		$array['preview'] = 'N/A';
		$array['aff'] = 5;
		$array['nofollow'] = $oiopub_set->{$lz}['nofollow'];
		$array['nfboost'] = $oiopub_set->{$lz}['nfboost'];
	} elseif($data->item_channel == 3) {
		if($data->item_type == 1) { 
			$array['type'] = __oio("Inline Video Ad");
		} elseif($data->item_type == 2) { 
			$array['type'] = __oio("Inline Banner Ad");
			$array['image'] = $data->item_url;
		} elseif($data->item_type == 3) {
			$array['type'] = __oio("Inline RSS Feed Ad");
		} elseif($data->item_type == 4) {
			$array['type'] = __oio("Intext Link");
			$array['item'] = __oio("Intext Link");
			$array['url'] = $data->item_url;
			$array['preview'] = "N/A";
			$array['aff'] = 3;
			$array['nofollow'] = $oiopub_set->inline_links['nofollow'];
			$array['nfboost'] = $oiopub_set->inline_links['nfboost'];
		}
		if($data->item_type != 4) {
			$array['item'] = __oio("Inline Ad");
			$array['url'] = $data->item_url;
			$array['preview'] = "N/A";
			$array['aff'] = 3;
			$array['nofollow'] = $oiopub_set->inline_ads['nofollow'];
			$array['nfboost'] = $oiopub_set->inline_ads['nfboost'];
		}
	} elseif($data->item_channel == 4) {
		$cn = "custom_" . $data->item_type;
		$array['item'] = __oio("Custom Purchase");
		$array['type'] = $oiopub_set->{$cn}['title'];
		$array['aff'] = 4;
		if(!empty($oiopub_set->{$cn}['download'])) {
			$array['preview'] = $oiopub_set->plugin_url . "/download.php?id=" . $data->item_id . "&rand=" . $data->rand_id;
		} else {
			$array['preview'] = 'N/A';
		}
	} elseif($data->item_channel == 5) {
		$bz = "banners_" . $data->item_type;
		$array['item'] = __oio("Banner Ad");
		$array['type'] = __oio("Banner Ad") . ", " . $oiopub_set->{$bz}['title'];
		$array['url'] = $data->item_page;
		$array['preview'] = 'N/A';
		$array['image'] = $data->item_url;
		$array['aff'] = 5;
		$array['nofollow'] = $oiopub_set->{$bz}['nofollow'];
		$array['nfboost'] = $oiopub_set->{$bz}['nfboost'];
	}
	//item status
	if($data->item_status == 0) {
		$array['istatus'] = __oio("Pending");
	} elseif($data->item_status == 1) {
		$array['istatus'] = __oio("Approved");
	} elseif($data->item_status == 2) {
		$array['istatus'] = __oio("Rejected");
	} elseif($data->item_status == 3) {
		$array['istatus'] = __oio("Expired");
	} elseif($data->item_status < 0) {
		$array['istatus'] = __oio("Queued");
	}
	//payment status
	if($data->payment_status == 0) {
		$array['pstatus'] = __oio("Not Paid");
	} elseif($data->payment_status == 1) {
		$array['pstatus'] = __oio("Paid");
	} elseif($data->payment_status == 2) {
		$array['pstatus'] = __oio("Invalid Payment");
	}
	//expiration date
	if($data->item_subscription == 1) {
		$array['expire'] = __oio("None, subscription");
	} elseif($data->item_status < 0) {
		$array['expire'] = __oio("unknown (in queue)");
	} else {
		if($data->item_duration > 0) {
			$array['expire'] = date("jS M, Y", $data->payment_time + (86400 * $data->item_duration));
		} else {
			$array['expire'] = __oio("None");
		}
	}
	return $array;
}

//queue time estimate
function oiopub_queue_estimate($channel, $type, $payment_time=0, $post_id=0) {
	global $oiopub_set, $oiopub_db;
	//set vars
	$count = 0;
	$tmp = array();
	$res = array( 'queue' => 0, 'time' => 0, 'date' => '' );
	//where conditions
	$where_payment = $payment_time ? " AND payment_time < '$payment_time'" : "";
	$where_post = $post_id ? " AND post_id='$post_id'" : "";
	//active purchases
	$active = $oiopub_db->GetAll("SELECT item_model, payment_time, (payment_time + (item_duration*86400)) as expiry_time FROM " . $oiopub_set->dbtable_purchases . " WHERE item_channel='$channel' AND item_type='$type' AND payment_status='1' AND item_status IN(0,1)" . $where_post . " ORDER BY expiry_time ASC");
	//queued purchases
	$queued = $oiopub_db->GetAll("SELECT item_model, payment_time, (item_duration*86400) as duration_time FROM " . $oiopub_set->dbtable_purchases . " WHERE item_channel='$channel' AND item_type='$type' AND payment_status='1' AND item_status IN(-1,-2)" . $where_payment . $where_post . " ORDER BY payment_time ASC");	
	//loop through collections
	foreach(array( 'active', 'queued' ) as $data) {
		//loop through data
		foreach($$data as $row) {
			//uses days?
			if($row->item_model != 'days') {
				continue;
			}
			//add item
			if($row->duration_time) {
				$tmp[] = $tmp[$count] + $row->duration_time;
				$count++;
			} else {
				$tmp[] = $row->expiry_time;
			}
			//sort
			sort($tmp);
		}
	}
	//set queue count
	$res['queue'] = count($queued);
	//set time?
	if(count($tmp) > 0) {
		$res['time'] = $tmp[$res['queue'] ? $res['queue'] : 0];
	}
	//set date?
	if($res['time'] > 0) {
		$res['date'] = date('jS M Y', $res['time']);
	} elseif(!$res['queue']) {
		$res = array();
	}
	//return
	return $res;
}

//ad spots unavailable
function oiopub_ads_unavailable() {
	global $oiopub_set, $oiopub_db, $oiopub_unavailable;
	if(!isset($oiopub_unavailable)) {
		$oiopub_unavailable = array();
		$ads = $oiopub_db->CacheGetAll("SELECT item_channel,item_type FROM " . $oiopub_set->dbtable_purchases . " WHERE item_status < '2'");
		if(!empty($ads)) {
			foreach($ads as $a) {
				$oiopub_unavailable[$a->item_channel][$a->item_type] += 1;
			}
		}
	}
	return $oiopub_unavailable;
}

//ad spots used
function oiopub_spots_used($channel, $type) {
	global $oiopub_set;
	$used = oiopub_ads_unavailable();
	return intval($used[$channel][$type]);
}

//ad spots available
function oiopub_spots_available($channel, $type, $queue=true) {
	global $oiopub_set, $oiopub_db;
	$available = $oiopub_set->spots[$channel][$type] - oiopub_spots_used($channel, $type);
	if($queue) {
		$available += $oiopub_set->queue[$channel][$type];
	}
	return intval($available);
}

//ad next available
function oiopub_next_available($channel, $type) {
	global $oiopub_set, $oiopub_db;
	//calculate numbers
	$available_now = oiopub_spots_available($channel, $type, false);
	$available_queue = $available_now + $oiopub_set->queue[$channel][$type]; 
	//available now?
	if($available_now > 0) {
		return __oio("%s slot(s) available now", array($available_now));
	}
	//use queue?
	if($available_queue > 0) {
		$est = oiopub_queue_estimate($channel, $type);
		return __oio("Available, but will be queued") . " (#" . ($est['queue']+1) . ")" . ($est['date'] ? "<br /><i>" . __oio("Estimated Activation Date") . ": " . $est['date'] . "</i>" : "");
	}
	//nothing available
	return __oio("no slots currently available");
}

//module version check
function oiopub_module_vcheck($module, $min_version) {
	global $oiopub_set, $oiopub_version;
	if(oiopub_is_admin()) {
		if(!empty($oiopub_set->version) && $oiopub_version < $min_version) {
			echo "<b>OIOpublisher " . $module . " module:</b> requires a newer version of OIOpublisher Direct to be uploaded!\n";
			echo "<br /><br />\n";
			echo "Current Version: " . $oiopub_version . "\n";
			echo "<br />\n";
			echo "Required Version: " . $min_version . "\n";
			echo "<br /><br />\n";
			echo "Please <a href='http://download.oiopublisher.com' target='_blank'><b>download the latest version</b></a>.\n";
			echo "<br /><br />\n";
			echo "This message only affects the admin panel. If you do not want to install the latest OIOpublisher version, you can simply delete the " . $module . " module folder from the OIOpublisher 'modules' directory.\n";
			die();
		}
	}
}

//check purchase ID string
function oiopub_check_pids($pids, $sep, $unique=false) {
	$pids = oiopub_clean($pids);
	if(strlen($pids) > 0) {
		$exp = explode($sep, $pids);
		$exp_count = count($exp);
		if($exp_count > 0 && in_array(0, $exp)) {
			$array = array();
			for($z=0; $z < $exp_count; $z++) {
				$array[] = intval($exp[$z]);
			}
			if($unique !== false) {
				$array = array_unique($array);
			}
			sort($array);
			return trim(implode($sep, $array));
		}
	}
	return false;
}

//convert channel type
function oiopub_type_check($channel) {
	$res = '';
	if(is_numeric($channel)) {
		if($channel == 1) $res = "post";
		if($channel == 2) $res = "link";
		if($channel == 3) $res = "inline";
		if($channel == 4) $res = "custom";
		if($channel == 5) $res = "banner";
	} else {
		if($channel == "post") $res = 1;
		if($channel == "link") $res = 2;
		if($channel == "inline") $res = 3;
		if($channel == "custom") $res = 4;
		if($channel == "banner") $res = 5;
	}
	return $res;
}

//readonly attribute
function oiopub_readonly($var) {
	if(isset($_GET[$var]) && !empty($_GET[$var])) {
		return " readonly";
	}
	return false;
}

//prepare JS
function oiopub_js($js) {
	$js = str_replace("\\", "\\\\", $js);
	$js = preg_replace("/[\r\n]+/", '\n', $js);
	$js = str_replace('"', '\"', $js);
	$js = str_replace("'", "\'", $js);
	$js = preg_replace("/<script/i", "<scr'+'ipt", $js);
	$js = preg_replace("/<\/script/i", "</scr'+'ipt", $js);
	return $js;
}

//execute php code
function oiopub_php_eval($content) {
	if(strpos($content, '<?php') !== false) {
		ob_start();
		eval("?>$content<?php ");
		$content = ob_get_contents();
		ob_end_clean();
	}
	return $content;
}

//purchase url
function oiopub_purchase_url($type, $zone=1, $options=array()) {
	global $oiopub_set;
	$options = is_array($options) ? $options : array();
	if(!empty($oiopub_set->general_set['buypage'])) {
		if(strpos($oiopub_set->general_set['buypage'], "http://") === false) {
			$purchase_url = $oiopub_set->site_url . "/" . $oiopub_set->general_set['buypage'] . "#" . $type;
		} else {
			$purchase_url = $oiopub_set->general_set['buypage'] . "#" . $type;
		}
	} else {
		$purchase_url = $oiopub_set->plugin_url . "/purchase.php?do=" . $type . "&amp;zone=" . $zone . ($options['subid'] ? "&amp;subid=" . $options['subid'] : "");
	}
	if($options['ref'] > 0) {
		if(strpos($purchase_url, "?") !== false) {
			$purchase_url .= "&amp;ref=" . $options['ref'];
		} else {
			$purchase_url .= "?ref=" . $options['ref'];
		}
	}
	return $purchase_url;
}

//powered by
function oiopub_powered_by() {
	global $oiopub_set;
	echo '<div style="text-align:center; padding-top:30px;">';
	echo 'Powered by <a href="' . $oiopub_set->oiopub_url . '" target="_blank">OIOpublisher</a>';
	echo '</div>';
}

//list of country codes
function oiopub_geo_countries() {
	global $oiopub_set;
	static $result = array();
	if(empty($result)) {
		include_once($oiopub_set->folder_dir . "/include/countries.php");
		$result = geoip_country_list();
	}
	return $result;
}

//copy ad
function oiopub_copy_ad($item_id, $redirect=false) {
	global $oiopub_set, $oiopub_db;
	//format vars
	$item_id = (int) $item_id;
	//select row
	if($item = $oiopub_db->GetRow("SELECT * FROM " . $oiopub_set->dbtable_purchases . " WHERE item_id='$item_id'")) {
		//set new rand ID
		$item->rand_id = $item->rand_id[0] . "-" . oiopub_rand(10);
		//unset item ID
		unset($item->item_id);
		//convert to array
		$item = (array) $item;
		//update database
		$oiopub_db->query("INSERT INTO " . $oiopub_set->dbtable_purchases . " (" . implode(",", array_keys($item)) . ") VALUES ('" . implode("','", array_values($item)) . "')");
	}
	//redirect?
	if($redirect) {
		header("Location: $redirect");
		exit();
	}
}

//log admin IP
function oiopub_log_admin_ip() {
	global $oiopub_set;
	//authenticated user?
	if(!oiopub_is_admin() || !oiopub_auth_check()) {
		return;
	}
	//get IP address
	$ip = ip2long($oiopub_set->client_ip);
	//create setting?
	if(!isset($oiopub_set->admin_ips)) {
		oiopub_update_config('admin_ips', array());
	}
	//update array?
	if(!in_array($ip, $oiopub_set->admin_ips)) {
		$oiopub_set->admin_ips[] = $ip;
		if(count($oiopub_set->admin_ips) > 10) {
			array_shift($oiopub_set->admin_ips);
		}
		oiopub_update_config('admin_ips', $oiopub_set->admin_ips);
	}
}

//geo-location
function oiopub_geolocation() {
	global $oiopub_set;
	//is enabled?
	if($oiopub_set->demographics['enabled'] != 1) {
		return null;
	}
	//is admin?
	if(oiopub_is_admin() || $oiopub_set->host_name == "localhost") {
		return null;
	}
	//cookie check
	if(isset($_COOKIE['oiopub_location']) && !empty($_COOKIE['oiopub_location'])) {
		$oiopub_set->visitor_country = oiopub_clean($_COOKIE['oiopub_location']);
		return $oiopub_set->visitor_country;
	}
	//set vars
	$oiopub_set->visitor_country = "";
	$limit = time() + (24 * 3600);
	//use local database?
	if(isset($oiopub_set->demographics['db']) && $oiopub_set->demographics['db'] == 'local' && is_file($oiopub_set->folder_dir . '/include/geo/GeoIP.dat')) {
		//include geo library?
		if(!function_exists('geoip_open')) {
			include_once($oiopub_set->folder_dir . '/include/geo/geoip.php');
		}
		//open database?
		if($geoip = @geoip_open($oiopub_set->folder_dir . '/include/geo/GeoIP.dat', GEOIP_STANDARD)) {
			$oiopub_set->visitor_country = @geoip_country_code_by_addr($geoip, $oiopub_set->client_ip);
			@geoip_close($geoip);
		}
	} else {
		//call to web service successful?
		if($res = oiopub_file_contents('http://www.geoplugin.net/php.gp?ip=' . $oiopub_set->client_ip)) {
			if(is_string($res)) {
				$geoip = unserialize($res);
				$oiopub_set->visitor_country = $geoip['geoplugin_countryCode'];
			}
		}
	}
	//set cookie
	setcookie("oiopub_location", $oiopub_set->visitor_country, $limit, "/");
	//return
	return $oiopub_set->visitor_country;
}

//check for magic quotes
function oiopub_has_magic_quotes() {
	//check default?
	if(function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc() == 1) {
		return true;
	}
	//check wordpress?
	if(function_exists('wp_magic_quotes')) {
		return true;
	}
	//not found
	return false;
}

//format currency amount
function oiopub_amount($amount, $currency=null, $processor=null) {
	global $oiopub_set;
	//set vars
	$before = $after = $symbol = '';
	$currency = strtoupper(trim($currency));
	$code = strtoupper($oiopub_set->general_set['currency']);
	//set symbol?
	if(isset($oiopub_set->general_set['currency_symbol']) && $oiopub_set->general_set['currency_symbol']) {
		$symbol = $oiopub_set->general_set['currency_symbol'];
	}
	//check processor?
	if($processor && isset($oiopub_set->{$processor}['currency']) && $oiopub_set->{$processor}['currency']) {
		//update code
		$code = strtoupper($oiopub_set->{$processor}['currency']);
		//update symbol?
		if(isset($oiopub_set->{$processor}['currency_symbol']) && $oiopub_set->{$processor}['currency_symbol']) {
			$symbol = $oiopub_set->{$processor}['currency_symbol'];
		}
	}
	//use symbol?
	if($symbol && (!$currency || $currency === $code)) {
		$before = $symbol;
	} else {
		$after = $code;
	}
	//return
	return $before . trim($amount) . ($after ? ' ' . $after : '');
}

//process spintax
function oiopub_spintax($s) {
	//check for matches
	preg_match('#{(.*)}#isU', $s, $m);
	//none found?
	if(!$m || !$m[1] || strpos($m[1], '|') === false) {
		return $s;
	}
	//spintax found?
    if(strpos($m[1],'{') !== false) {
		$m[1] = substr($m[1], strrpos($m[1],'{') + 1);
    }
	//check parts
	$func = __FUNCTION__;
    $parts = explode("|", $m[1]);
    $s = preg_replace("+{" . preg_quote($m[1]) . "}+isU", $parts[array_rand($parts)], $s, 1);
	//return
    return $func($s);
}

?>