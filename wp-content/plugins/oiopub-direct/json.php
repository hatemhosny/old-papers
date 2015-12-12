<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

//define vars
define('OIOPUB_LOAD_LITE', 1);

//init
include_once(str_replace("\\", "/", dirname(__FILE__)) . "/index.php");

//is plugin enabled?
if($oiopub_set->enabled != 1) {
	exit();
}

//required inputs
$type = isset($_REQUEST['type']) ? oiopub_clean($_REQUEST['type']) : ''; //zone type (e.g. banner, link)
$zone = isset($_REQUEST['zone']) ? (int) $_REQUEST['zone'] : 0; //zone ID (e.g. 1)

//optional inputs
$limit = isset($_REQUEST['limit']) ? (int) $_REQUEST['limit'] : 0; //number of results to return
$shuffle = isset($_REQUEST['shuffle']) ? (int) $_REQUEST['shuffle'] : 0; //randomise results?
$defaults = isset($_REQUEST['defaults']) ? (int) $_REQUEST['defaults'] : 1; //include default ads?
$readable = isset($_REQUEST['readable']) ? (int) $_REQUEST['readable'] : 0; //make json human readable?
$cache = isset($_REQUEST['cache']) ? (int) $_REQUEST['cache'] : 0; //cache results for X seconds?
$stats = (isset($_REQUEST['stats']) && $oiopub_module->tracker == 1 && $oiopub_set->tracker['enabled'] == 1) ? (int) $_REQUEST['stats'] : 0; //include ad stats?

//misc vars
$output = '';
$data = array();
$types = array( 'banner' => "banners", 'link' => "links" );
$z = isset($types[$type]) ? $types[$type] . "_" . $zone : '';
$z_def = $z . '_defaults';
$cache_key = "json_api_" . $z;
$cache_hit = false;

//invalid input?
if(!$z || !$zone) {
	$output = 'invalid zone ' . ($z ? 'ID' : 'type');
	$cache = 0;
}

//continue?
if(!$output) {
	//check cache?
	if($cache > 0 && $data = $oiopub_cache->get($cache_key, $cache)) {
		$data = json_decode($data, true);
	}
	//check db?
	if(!$data) {
		//reset data
		$data = array();
		//find matches from database
		$ads = $oiopub_db->GetAll("SELECT * FROM " . $oiopub_set->dbtable_purchases . " WHERE item_channel='" . oiopub_type_check($type) . "' AND item_status='1' AND payment_status='1' AND item_type='$zone' ORDER BY " . ($shuffle ? "RAND()" : "payment_time ASC") . ($limit > 0 ? " LIMIT $limit" : ""));
		//process results
		foreach($ads as $ad) {
			//start / end times
			$start = (int) $ad->payment_time;
			$end = (int) $ad->item_duration > 0 ? ($ad->payment_time + (86400 * $ad->item_duration)) : 0;
			//add data row
			$row = array(
				'id' => $ad->item_id,
				'type' => $type,
				'zone' => $zone,
				'image' => $type == "link" ? "" : $ad->item_url,
				'anchor' => $type == "link" ? $ad->item_page : "",
				'link' => $type == "link" ? $ad->item_url : $ad->item_page,
				'link_track' => $oiopub_set->tracker_url . "/go.php?id=" . $ad->item_id,
				'tooltip' => $ad->item_tooltip,
				'html' => $ad->item_notes,
				'width' => (int) $oiopub_set->{$z}['width'],
				'height' => (int) $oiopub_set->{$z}['height'],
				'start' => $start,
				'end' => $end,
				'is_default' => false,
			);
			//get stats?
			if($stats) {
				$s = $oiopub_db->GetRow("SELECT SUM(total_clicks) as clicks, SUM(total_visits) as impressions FROM " . $oiopub_set->dbtable_tracker_archive . " WHERE pid='" . $ad->item_id . "'" . ($end > 0 ? " AND date BETWEEN '" . date('Y-m-d', $start) . "' AND '" . date('Y-m-d', $end) . "'" : ""));
				$row['clicks'] = (int) $s->clicks;
				$row['impressions'] = (int) $s->impressions;
			}
			//add row
			$data[] = $row;
		}
		//include default ads?
		if($defaults && isset($oiopub_set->{$z_def})) {
			//set local vars
			$default = $oiopub_set->{$z_def};
			//loop through default ads
			for($i=1; $i <= count($default['type']); $i++) {
				//stop here?
				if($limit > 0 && count($data) >= $limit) {
					break;
				}
				//create row
				$row = array(
					'id' => null,
					'type' => $type,
					'zone' => $zone,
					'image' => $type == "link" ? "" : $default['image'][$i],
					'anchor' => $type == "link" ? $default['anchor'][$i] : "",
					'link' => $type == "link" ? $default['url'][$i] : $default['site'][$i],
					'link_track' => "",
					'tooltip' => "",
					'html' => $type == "link" ? $default['desc'][$i] : $default['html'][$i],
					'width' => (int) $oiopub_set->{$z}['width'],
					'height' => (int) $oiopub_set->{$z}['height'],
					'start' => null,
					'end' => null,
					'is_default' => true,
				);
				//add stats?
				if($stats) {
					$row['clicks'] = 0;
					$row['impressions'] = 0;
				}
				//save row?
				if($row['link']) {
					$data[] = $row;
				}
			}
		}
	}
}

//shuffle?
if($shuffle) {
	shuffle($data);
}

//convert to json
$output = json_encode($output ? $output : $data);

//make readable?
if($readable) {
	//set vars
	$json = $output;
	$json_strlen = strlen($json);
	$output = '';
	$output_pos = 0;
	//loop through string
	for($i=0; $i<=$json_strlen; $i++) {
		$char = substr($json, $i, 1);
		if($char == '}' || $char == ']') {
			$output .= "\n";
			$output_pos --;
			for($j=0; $j < $output_pos; $j++) {
				$output .= '  ';
			}
		}
		$output .= $char;
		if($char == ',' || $char == '{' || $char == '[') {
			$output .= "\n";
			if($char == '{' || $char == '[') {
				$output_pos ++;
			}
			for($j=0; $j < $output_pos; $j++) {
				$output .= '  ';
			}
		}
	}
}

//update cache?
if(isset($ads) && $cache > 0 && $output) {
	$oiopub_cache->write($cache_key, $output, $cache);
}

//set headers
header('Content-Type: application/json');
echo $output;
exit();