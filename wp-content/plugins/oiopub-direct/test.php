<?php

//display all errors
error_reporting(E_ALL);

//set time limit
set_time_limit(10);

//session start (test)
function oiopub_test_session() {
	//session exists?
	if(session_id()) {
		return true;
	}
	//update settings?
	if(function_exists('ini_set')) {
		ini_set('session.cookie_path', "/");
		ini_set('session.use_trans_sid', 0);
		ini_set('session.use_only_cookies', 1);
		ini_Set('session.cookie_lifetime', 0);
		ini_set('session.gc_maxlifetime', 7200);
		ini_set('session.gc_probability', 1);
		ini_set('session.gc_divisor', 100);
	}
	//set module name?
	if(!session_module_name()) {
		session_module_name('files');
	}
	//is file module?
	if(session_module_name() == 'files') {
		//check saved path
		$path = session_save_path();
		//is path writable?
		if(!$path || !is_writable($path)) {
			//set default path
			$default = "/tmp";
			//update path
			session_save_path($default);
		}
	}
	//start session	
	return session_start();
}

//spawn cron (test)
function oiopub_test_spawn($spawn_url) {
	//get vars
	$result = "";
	$parse = parse_url($spawn_url);
	//attempt connection
	if(!$fp = fsockopen($parse['host'], 80, $errno, $errstr, 5)) {
		$result = "Error Number: " . $errno . "<br />Error Message: " . $errstr;
		return $result;
	}
	//set user-agent
	$user_agent = !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "OIOpub Direct Scheduled Task Manager";
	//set request
	$request  = "GET " . (isset($parse['path']) ? $parse['path'] : '/') . (isset($parse['query']) ? '?' . $parse['query'] : '') . " HTTP/1.0\r\n";
	$request .= "Host: " . $parse['host'] . "\r\n";
	$request .= "User-Agent: " . strip_tags($user_agent) . "\r\n";
	$request .= "Connection: Close\r\n\r\n";			
	//send request
	fputs($fp, $request, strlen($request));
	while(!feof($fp)) {
		$result .= fgets($fp, 4096);
	}
	//close
	fclose($fp);
	//return
	return $result;
}

//get file content (test)
function oiopub_test_file($file, $buffer=4096, $timeout=3) {
	//set vars
	$blocking = 1;
	$context = null;
	$result = false;
	//clear cache
	clearstatcache();
	//default timeout
	ini_set('default_socket_timeout', $timeout); 
	//create context?
	if(function_exists('stream_context_create')) {
		$context = stream_context_create(array(
			'http' => array(
				'timeout' => $timeout,
			),
		));
	}
	//open handle
	if($fp = fopen($file, 'rb', false, $context)) {
		//set stream options?
		if(function_exists('stream_set_timeout')) {
			stream_set_timeout($fp, $timeout);
			stream_set_blocking($fp, $blocking);
		}
		//get result
		while($tmp = fread($fp, $buffer)) {
			//check time out?
			if(function_exists('stream_get_meta_data')) {
				//get meta data
				$info = stream_get_meta_data($fp);
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
	//return
	return $result;
}

//ping IP (test)
function oiopub_test_ping($ip) {
	//test IP
	$long = ip2long($ip);
	//convert to IP?
	if(!$long || $long == -1) {
		$ip = gethostbyname($ip);
	}
	//windows OS?
	if(strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
		exec("ping -n 3 " . escapeshellarg($ip), $output, $status);
	} else {
		exec("ping -c 3 " . escapeshellarg($ip), $output, $status);
	}
	//return
	return 'Return = ' . $status . "\n<br />\nOutput = " . print_r($output, true);
}

//session test
if(isset($_GET['do']) && $_GET['do'] == "session") {
	//start session
	oiopub_test_session();
	//write to session?
	if(!isset($_SESSION['test'])) {
		$_SESSION['test'] = "success";
		echo "<p>Refresh the page now.</p>";
		echo "<p>If sessions are working correctly, you'll see the word <b>success</b> printed.</p>";
		echo "<p>If you continue to see this message, please contact your web host and ask them to make sure that the directory '" . session_save_path() . "' is configured to store session data correctly.</p>";
	} else {
		echo $_SESSION['test'];
	}
}

//display test
if(isset($_GET['do']) && $_GET['do'] == "display") {
	echo "success";
}

//cron test
if(isset($_GET['do']) && $_GET['do'] == "cron") {
	//set url
	$url = explode("?", $_SERVER['REQUEST_URI']);
	$url = "http://" . strip_tags($_SERVER['HTTP_HOST'] . $url[0]) . "?do=display";
	//get result
	$result = oiopub_test_spawn($url);
	//show output
	echo str_replace(array("\r\n", "\n"), "<br />", $result);
}

//file test
if(isset($_GET['do']) && $_GET['do'] == "file") {
	//set url
	$url = "http://api.oiopublisher.com/2.0/version.txt";
	//get result
	$result = oiopub_test_file($url);
	//show output
	echo str_replace(array("\r\n", "\n"), "<br />", $result);
}

//ping test
if(isset($_GET['do']) && $_GET['do'] == "ping") {
	//set vars
	$domain = "api.oiopublisher.com";
	//get result
	echo oiopub_test_ping($domain);
}

//ad list
if(isset($_GET['do']) && $_GET['do'] == 'ads') {
	//load OIO script
	include_once('index.php');
	//set vars
	$number = 0;
	$zone = intval($_GET['zone']);
	//run query
	$res = $oiopub_db->GetAll("SELECT * FROM " . $oiopub_set->dbtable_purchases . " WHERE item_channel=5 AND item_type=" . $zone . " AND item_status=1 AND payment_status=1");
	//loop through results
	foreach($res as $ad) {
		//set vars
		$number++;
		//set urls
		$image_url = $ad->item_url;
		$link_url = $ad->item_page;
		$tracking_url = $oiopub_set->tracker_url . '/go.php?id=' . $ad->item_id;;
		//display output
		echo '<p><b>#' . $number . ' - ' . $link_url . '</b></p>' . "\n";
		echo '<a href="' . $link_url . '" target="_blank"><img src="' . $image_url . '" alt="" style="border:none;" /></a>' . "\n";
		echo '<p style="margin-bottom:5px;"><b>Tracking link</b></p>' . "\n";
		echo htmlentities('<a href="' . $tracking_url . '"><img src="' . $image_url . '" alt="" /></a>', ENT_QUOTES) . "\n";
		echo '<p style="margin-bottom:5px;"><b>Actual link</b></p>' . "\n";
		echo htmlentities('<a href="' . $link_url . '"><img src="' . $image_url . '" alt="" /></a>', ENT_QUOTES) . "\n";
		echo '<br /><br /><br />' . "\n";
	}
}