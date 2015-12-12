<?php

//define vars
define('OIOPUB_LOAD_LITE', 1);

//init
include_once(str_replace("\\", "/", dirname(__FILE__)) . "/index.php");

//don't show errors
@ini_set('display_errors', 0);

//set vars
$mode = isset($_GET['mode']) ? oiopub_clean($_GET['mode']) : '';
$zone = isset($_GET['zone']) ? (int) $_GET['zone'] : '';
$index = isset($_GET['index']) ? (int) $_GET['index'] : 0;
$cache = isset($_GET['cache']) ? (int) $_GET['cache'] : 3600;
$ref = isset($_GET['ref']) ? oiopub_clean($_GET['ref']) : oiopub_var('server', 'HTTP_REFERER', 'email');

//update http referer
$_SERVER['HTTP_REFERER'] = $ref;

//invalid call?
if(!$mode || !$zone) {
	exit();
}

//call json?
if(!$json = oiopub_file_contents($oiopub_set->plugin_url . '/json.php?type=banner&zone=' . $zone . '&cache=' . $cache)) {
	exit();
}

//decode json
$json = json_decode($json, true);

//call failed?
if(is_string($json) || !isset($json[$index])) {
	exit();
}

//link & image vars
$id = $json[$index]['id'];
$image = $json[$index]['image'];
$ext = strtolower(pathinfo($image, PATHINFO_EXTENSION));
$link = $json[$index]['link_track'] ? $json[$index]['link_track'] . '&ref=' . $ref : $json[$index]['link'];

//failed?
if(!$link || !$image || !$ext || $ext === 'swf') {
	exit();
}

//dont cache
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

//redirect link?
if($mode === 'link') {
	header("Location: " . $link);
	exit();
}

//get image content
$content = oiopub_file_contents($image);

//log visit?
if(isset($oiopub_plugin['tracker']) && $id > 0) {
	$oiopub_plugin['tracker']->log_visit('0|' . $id);
}

//set image headers
header("Content-Type: image/" . $ext);
header("Content-Length: " . strlen($content));

//display
echo $content;
exit();