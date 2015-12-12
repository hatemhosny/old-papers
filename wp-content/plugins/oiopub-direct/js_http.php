<?php

//define vars
define('OIOPUB_JS', 1);
define('OIOPUB_LOAD_LITE', 1);

//don't show errors
@ini_set('display_errors', 0);

//init
include_once(str_replace("\\", "/", dirname(__FILE__)) . "/index.php");

//set vars
$min_refresh = 5;
$result = array();
$cls = oiopub_var('cls', 'get');
$queries = isset($_GET['queries']) ? $_GET['queries'] : array();

//plugin enabled?
if($oiopub_set->enabled != 1 || empty($cls)) {
	exit();
}

//loop through queries
foreach($queries as $query) {
	//set vars
	$html = "";
	$markup = array();
	$options = array();
	$query = rawurldecode($query);
	//parse options
	parse_str($query, $options);
	//store markup options
	foreach($options as $key => $val) {
		if($key === 'markup_before' || $key === 'markup_after') {
			$markup[$key] = stripslashes($val);
		}
	}
	//clean options
	$options = array_map('oiopub_clean', $options);
	$options = array_merge($options, $markup);
	//get zone vars
	$id = (string) $options['id'];
	$zone = (int) $options['zone'];
	$type = (string) $options['type'];
	$refresh = (int) $options['refresh'];
	$refreshed = (int) $options['refreshed'];
	//unset vars
	unset($options['id'], $options['zone'], $options['type'], $options['refresh'], $options['refreshed']);
	//no echo
	$options['echo'] = false;
	//min refresh?
	if($refresh > 0 && $refresh < $min_refresh) {
		$refresh = $min_refresh;
	}
	//valid zone?
	if($zone <= 0) continue;
	//get function
	$function = "oiopub_" . $type . "_zone";
	//function exists?
	if(function_exists($function)) {
		//get html output
		$html = $function($zone, $options);
		$html = str_replace(array("\r\n", "\r", "\n\n"), "\n", $html);
		$html = trim($html);
	}
	/*
	if(empty($html)) {
		$html = ucfirst($type) . " Ad zone " . $zone . " not defined";
	}
	*/
	//remove refreshed?
	if($refreshed == 1) {
		$query = str_replace("&refreshed=1", "", $query);
	}
	//add to result
	$result[] = array( 'id' => $id, 'zone' => $zone, 'type' => $type, 'refresh' => $refresh, 'query' => trim($query), 'content' => trim($html) );
	//include css?
	if(count($result) == 1 && $refreshed != 1) {
		$result[0]['css'] = $oiopub_set->plugin_url_org . '/images/style/output.css?' . str_replace('.', '', $oiopub_set->version);
	}
}

//json function exists?
if(!function_exists('oiopub_json_encode')) {
	function oiopub_json_encode($a) {
		if(is_null($a)) {
			return 'null';
		}
		if(is_bool($a)) {
			return $a ? 'true' : 'false';
		}
		if(is_scalar($a)) {
			if(is_float($a)) {
				return floatval(str_replace(",", ".", strval($a)));
			}
			if(is_string($a)) {
				return '"' . str_replace(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'), $a) . '"';
			}
			return $a;
		}
		$numeric = true;
		$result = array();
		foreach($a as $k => $v) {
			if(is_numeric($k)) {
				$result[] = oiopub_json_encode($v);
			} else {
				$numeric = false;
				$result[] = oiopub_json_encode($k) . ':' . oiopub_json_encode($v);
			}
		}
		if($numeric) {
			return '[' . join(',', $result) . ']';
		} else {
			return '{' . join(',', $result) . '}';
		}
	}
}

//js output hook
$oiopub_hook->fire('javascript_output');

//javascript file
header("Content-type: text/javascript");

//format as json
echo $cls . ".json(" . oiopub_json_encode($result) . ");";

?>