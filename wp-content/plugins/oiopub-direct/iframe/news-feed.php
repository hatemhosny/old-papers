<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

//define vars
define('OIOPUB_NEWS', 1);
define('OIOPUB_ADMIN', 1);

//init
include_once("../index.php");

//show load screen
echo "<div class='loading' style='text-align:center; padding-top:30px;'>Loading... <img src='" . $oiopub_set->plugin_url . "/images/loading.gif' style='border:0px;' alt='' /></div>\n";
flush();

//perform checks
if(oiopub_install_check()) {
	//is plugin enabled?
	if($oiopub_set->enabled != 1) {
		exit();
	}
	//check access
	if(!oiopub_auth_check()) {
		exit();
	}
}

//get vars
$count = 0;
$output = "";
$feed = urldecode(oiopub_var('feed', 'get'));
$number = intval(oiopub_var('num', 'get'));

//format feed url?
if(strpos($feed, "http") !== 0) {
	$feed = "http://" . $feed;
}

//cache string
$cache_string = $feed . $number;

//process
if($cache = $oiopub_cache->get($cache_string)) {
	$output .= $cache;
} else {
	$item_array = array();
	$xml = oiopub_parser($feed);
	$output .= "<ul style='line-height:24px; list-style:none; margin:0; padding:0;'>";
	if(!empty($xml->channel)) {
		foreach($xml->channel->item as $item) {
			if(!in_array($item->link, $item_array)) {
				if(count($item_array) == 0) {
					$style = ' style="color:red;"';
				} else {
					$style = '';
				}
				$output .= "<li>&raquo; <a href='" . $item->link . "' target='_blank'" . $style . ">" . $item->title . "</a></li>";
				$item_array[] = $item->link;
				$count++; if($count >= $number) break;
			}
		}
	} else {
		$output .= "<li>&raquo; unable to load blog feed</li>";
	}
	$output .= "</ul>";
	$oiopub_cache->write($cache_string, $output);
}

//hide load screen
echo "<style type='text/css'>.loading{display:none;}</style>\n";
flush();

//output
echo "<meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />\n";
echo "<body style='margin:0; padding:0; font:13px \"Lucida Grande\", \"Lucida Sans Unicode\", Tahoma, Verdana, sans-serif;'>\n";
echo $output;
echo "</body>\n";

?>