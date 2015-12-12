<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();


/* OUTPUT CLASS */

class oiopub_output {

	var $db;
	var $settings;

	var $type;
	var $zone;
	var $options;

	var $zn;
	var $zn_def;

	var $used_ads = array();

	//constructor
	function oiopub_output() {
		global $oiopub_set, $oiopub_db;
		//set global vars
		$this->db =& $oiopub_db;
		$this->settings =& $oiopub_set;
	}

	//get ad zone
	function zone($type, $id, $options=array()) {
		global $oiopub_plugin;
		//set class vars
		$this->type = $type;
		$this->zone = intval($id);
		//set zone vars
		$this->zn = $this->type . 's_' . $this->zone;
		$this->zn_def = $this->zn . '_defaults';
		//can we continue?
		if(!defined('oiopub_' . $this->type . 's') || $this->zone <= 0 || $this->settings->enabled != 1 || $this->settings->{$this->zn}['enabled'] != 1) {
			return;
		}
		//set used ads array?
		if(!isset($this->used_ads[$this->zn])) {
			$this->used_ads[$this->zn] = array();
		}
		//set options
		$this->options = $this->format_options($options);
		//create ad list
		$ads = $this->ad_list($this->purchased_ads($this->type), $this->default_ads($this->type));
		//log impressions now?
		if($this->options['log_impressions'] === 'now' && isset($oiopub_plugin['tracker']) && !defined('OIOPUB_JS')) {
			//build IDs
			$ids = array();
			//loop through ads
			foreach($ads as $ad) {
				if($ad->item_id > 0) {
					$ids[] = $ad->item_id;
				}
			}
			//add 'zero' ID?
			if($ids) $ids[] = 0;
			//call logging method
			$oiopub_plugin['tracker']->log_visit(implode('|', $ids), false);
		}
		//skip formatting?
		if($this->options['raw_data']) {
			return $ads;
		}
		//get output method
		$method = "format_output_" . $this->type;
		//does method exist?
		if(method_exists($this, $method)) {
			$output = $this->$method($ads);
		} else {
			$output = "";
		}
		//unset global category cache?
		if(isset($this->settings->cats)) {
			unset($this->settings->cats);
		}
		//cache buster
		$func = function_exists('str_ireplace') ? 'str_ireplace' : 'str_replace';
		$output = $func(array( "[timestamp]", "[cachebuster]" ), array( time(), md5(uniqid(mt_rand(), true)) ), $output);
		//surrounding code?
		if(!empty($output)) {
			$output = $this->options['markup_before'] . $output . $this->options['markup_after'];
		}
		//process spintax?
		if(function_exists('oiopub_spintax')) {
			$output = oiopub_spintax($output);
		}
		//strip tags?
		if($this->options['markup_allow_tags']) {
			$exp = explode(',', $this->options['markup_allow_tags']);
			$output = strip_tags($output, '<' . implode('><', $exp) . '>');
		}
		//remove dupe units
		$output = str_replace(array( 'px%', '%px' ), array( 'px', '%' ), $output);
		//return result
		if($this->options['echo']) {
			echo $output;
		} else {
			return $output;
		}
	}

	//purchased ads
	function purchased_ads($type) {
		//set vars
		$sql_extra = '';
		$cats = array();
		$zones = array();
		//stop here?
		if($this->options['show'] == 'defaults') {
			return array();
		}
		//single zone?
		if($this->zone > 0) {
			//categories found?
			if(strlen($this->options['category']) > 0) {
				//global cache
				$cats = $this->settings->cats = $this->parse_list($this->options['category'], 'intval');
			} elseif(function_exists('oiopub_get_category') && $this->settings->{$this->zn}['cats'] == 1) {
				//use platform function
				$cats = oiopub_get_category();
			}
			//include categories?
			if(!empty($cats)) {
				$sql_extra .= " AND category_id IN(0," . implode(',', $cats) . ")";
			}
			//include sub IDs?
			if($subids = $this->parse_list($this->options['subid'], 'oiopub_clean')) {
				//check for 'empty' ID?
				foreach($subids as $key => $val) {
					if($val === 'empty') {
						$subids[$key] = '';
					}
				}
				$sql_extra .= " AND item_subid IN('" . implode("','", $subids) . "')";
			}
			//include purchase ID?
			if($ids = $this->parse_list($this->options['purchase'], 'intval')) {
				$sql_extra .= " AND item_id IN(" . implode(',', $ids) . ")";
			}
			//exclude purchase IDs?
			if(!$this->options['repeats'] && $ids = $this->parse_list($this->used_ads[$this->zn], 'intval')) {
				$sql_extra .= " AND item_id NOT IN(" . implode(",", $ids) . ")";
			}
			//zones as array?
			if(!$zones = $this->parse_list($this->options['zones'], 'intval')) {
				$zones = array( $this->zone );
			}
		}
		//set types array
		$types = array( 'link' => 2, 'inline' => 3, 'banner' => 5 );
		//build sql query
		$sql = "SELECT * FROM " . $this->settings->dbtable_purchases . " WHERE item_channel=" . $types[$type] . " AND item_status=1 AND payment_status=1" . ($zones ? " AND item_type IN(" . implode(",", $zones) . ")" : "") . " AND (payment_time=0 OR payment_time < " . time() . ")" . $sql_extra . " ORDER BY payment_time DESC";
		//execute query
		$res = $this->db->GetAll($sql);
		//return
		return is_array($res) ? $res : array();
	}

	//default ads
	function default_ads($type) {
		//set vars
		$res = array();
		$is_inline = ($type == 'inline' || strpos($this->zn, 'inline') !== false);
		//stop here?
		if($this->options['show'] == 'purchases') {
			return array();
		}
		//zones as array?
		if(!$zones = $this->parse_list($this->options['zones'], 'intval')) {
			$zones = array( $this->zone );
		}
		//loop through array
		foreach($zones as $zone) {
			//set keys
			if($is_inline) {
				$zn = $this->zn;
				$zn_def = $this->zn_def;
				$type = 'banner';
			} else {
				$zn = $this->type . 's_' . $zone;
				$zn_def = $zn . '_defaults';
			}
			//count defaults
			if(isset($this->settings->{$zn_def}['type'])) {
				$count = oiopub_count($this->settings->{$zn_def}['type']);
			} else {
				$count = 0;
			}
			//loop through defaults
			for($z=1; $z <= $count; $z++) {
				//valid link?
				if($type == "link" && empty($this->settings->{$zn_def}['url'][$z])) {
					continue;
				}
				//valid banner?
				if($type == "banner" && (empty($this->settings->{$zn_def}['image'][$z]) && empty($this->settings->{$zn_def}['html'][$z]))) {
					continue;
				}
				//category filter?
				if(isset($this->settings->{$zn}) && function_exists('oiopub_get_category')) {
					if($this->settings->{$zn}['cats'] == 1 && $this->settings->{$zn_def}['cats'][$z] > 0) {
						$cat_ids = oiopub_get_category();
						if(!empty($cat_ids) && !in_array($this->settings->{$zn_def}['cats'][$z], $cat_ids)) {
							continue;
						}
					}
				}
				//convert location to array?
				if(!is_array($this->settings->{$zn_def}['geo2'][$z])) {
					if($this->settings->{$zn_def}['geo2'][$z]) {
						$this->settings->{$zn_def}['geo2'][$z] = array( $this->settings->{$zn_def}['geo2'][$z] );
					} else {
						$this->settings->{$zn_def}['geo2'][$z] = array();
					}
				}
				//geo-location check?
				if($this->settings->visitor_country) {
					//check options
					if(!$this->settings->{$zn_def}['geo2'][$z]) {
						$grab = true;
					} elseif($this->settings->{$zn_def}['geo1'][$z] == 1 && (in_array('GLOB', $this->settings->{$zn_def}['geo2'][$z]) || in_array($this->settings->visitor_country, $this->settings->{$zn_def}['geo2'][$z]))) {
						$grab = true;
					} elseif($this->settings->{$zn_def}['geo1'][$z] == 1 && in_array('LAST', $this->settings->{$zn_def}['geo2'][$z])) {
						$grab = false;
					} elseif($this->settings->{$zn_def}['geo1'][$z] == 2 && !in_array($this->settings->visitor_country, $this->settings->{$zn_def}['geo2'][$z])) {
						$grab = true;
					} else {
						$grab = false;
					}
					//continue?
					if(!$grab) {
						continue;
					}
				}
				//add to array?
				if($this->options['repeats'] || !in_array($z*-1, $this->used_ads[$zn])) {
					$ad = new oiopub_std;
					$ad->item_id = $z*-1;
					$ad->item_nofollow = 1;
					$ad->item_type = $this->zone;
					//link or banner?
					if($type == "link") {
						$ad->item_url = stripslashes($this->settings->{$zn_def}['url'][$z]);
						$ad->item_page = stripslashes($this->settings->{$zn_def}['anchor'][$z]);
						$ad->item_notes = stripslashes($this->settings->{$zn_def}['desc'][$z]);
						$ad->item_tooltip = isset($this->settings->{$zn_def}['tooltip'][$z]) ? stripslashes($this->settings->{$zn_def}['tooltip'][$z]) : $ad->item_page;
					} else {
						$ad->item_url = stripslashes($this->settings->{$zn_def}['image'][$z]);
						$ad->item_page = stripslashes($this->settings->{$zn_def}['site'][$z]);
						$ad->item_notes = $this->settings->{$zn_def}['html'][$z];
						$ad->item_tooltip = isset($this->settings->{$zn_def}['tooltip'][$z]) ? stripslashes($this->settings->{$zn_def}['tooltip'][$z]) : oiopub_domain($ad->item_page);
					}
					//done
					$res[] = $ad;
				}
			}
		}
		//return
		return $res;
	}

	//create ad list
	function ad_list($purchased, $defaults) {
		//number of slots
		$slots = $this->options['cols'] * $this->options['rows'];
		//seed the shuffle
		srand((float) microtime() * 1000000);
		//mix them up?
		if($this->options['shuffle']) {
			shuffle($purchased);
			shuffle($defaults);
		}
		//count them
		$purchased_count = oiopub_count($purchased);
		$defaults_count = oiopub_count($defaults);
		//equal rotation?
		if($this->settings->{$this->zn}['def_method'] == 0) {
			//merge arrays
			$purchased = array_merge($purchased, $defaults);
			//mix them up?
			if($this->options['shuffle']) {
				shuffle($purchased);
			}
			//slice array
			$purchased = array_slice($purchased, 0, $slots);
			//reset defaults
			$defaults = array();
		}
		//fill in gaps?
		if($this->settings->{$this->zn}['def_method'] == 1) {
			//merge arrays
			$purchased = array_merge($purchased, $defaults);
			//slice array
			$purchased = array_slice($purchased, 0, $slots);
			//reset defaults
			$defaults = array();
		}
		//weighted rotation?
		if($this->settings->{$this->zn}['def_method'] == 2) {
			//set vars
			$weights = array();
			//all spaces
			$spaces = $slots * $this->settings->{$this->zn}['rotator'];
			//calculate ratios
			$purchased_ratio = $purchased_count / $spaces;
			$purchased_ratio = $purchased_ratio > 1 ? 1 : $purchased_ratio;
			$default_ratio = 1 - $purchased_ratio;
			//set purchased weights
			foreach($purchased as $key => $val) {
				unset($purchased[$key]);
				$weights[] = array(
					'data' => $val,
					'weight' => $purchased_ratio / $purchased_count,
				);
			}
			//set default weights
			foreach($defaults as $key => $val) {
				unset($defaults[$key]);
				$weights[] = array(
					'data' => $val,
					'weight' => $default_ratio / $defaults_count,
				);
			}
			//loop through slots
			for($z=0; $z < $slots; $z++) {
				//set vars
				$current = 0;
				$repeats = false;
				$total_weight = $this->calc_total_weight($weights);
				$rand = mt_rand(0, PHP_INT_MAX) / PHP_INT_MAX;
				//loop through weights
				foreach($weights as $key => $val) {
					$current += $val['weight'];
					$prob = $current / $total_weight;
					if($prob > $rand) {
						if(!$repeats) {
							unset($weights[$key]);
						}
						$purchased[] = $val['data'];
						break;
					}
				}
			}
		}
		//count again
		$empty_slots = 0;
		$purchased_count = oiopub_count($purchased);
		$purchase_url = oiopub_purchase_url($this->type, $this->zone, $this->options);
		//empty ad slots
		for($z=$purchased_count; $z < $slots; $z++) {
			//stop here?
			if($this->options['empty'] >= 0 && $empty_slots >= $this->options['empty']) {
				break;
			}
			//count
			$empty_slots++;
			//set vars
			$purchased[$z] = new oiopub_std;
			$purchased[$z]->item_id = 0;
			$purchased[$z]->item_type = $this->zone;
			$purchased[$z]->item_nofollow = 1;
			$purchased[$z]->item_notes = "";
			//can advertise?
			$can_advertise = $this->settings->{$this->zn}['price'][0] > 0 || $this->settings->{$this->zn}['link_exchange'];
			//link or banner?
			if($this->type == 'link') {
				if($can_advertise) {
					$purchased[$z]->item_url = $purchase_url;
					$purchased[$z]->item_page = $this->settings->{$this->zn}['def_text'] ? $this->settings->{$this->zn}['def_text'] : __oio("Add Your Link Here");
					$purchased[$z]->item_tooltip = __oio("Advertise Here");
					$purchased[$z]->item_empty = 1;
				}
			} else {
				$purchased[$z]->item_url = $this->settings->{$this->zn}['def_image'] ? $this->settings->{$this->zn}['def_image'] : ($can_advertise ? __oio("Advertise Here") : __oio("OIOpublisher.com"));
				$purchased[$z]->item_page = $can_advertise ? $purchase_url : "http://www.oiopublisher.com/ref.php?u=" . $this->settings->affiliate_id;
				$purchased[$z]->item_nofollow = 1;
				$purchased[$z]->item_tooltip = $can_advertise ? __oio("Advertise Here") : "OIOpublisher Ad Manager";
				$purchased[$z]->item_notes = "";
				$purchased[$z]->item_empty = $this->settings->{$this->zn}['def_image'] ? 2 : 1;
			}
		}
		//return
		return array_slice($purchased, 0, $slots);
	}

	//format banner ad output
	function format_output_banner($ads) {
		global $oiopub_module;
		//set vars
		$output = '';
		$_output = '';
		$number = 0;
		$number_empty = 0;
		//reset spacing?
		if($this->options['rows'] == 1 && $this->options['cols'] == 1) {
			$this->options['spacing'] = 0;
		}
		//set zone vars
		$zone_items = $this->options['rows'] * $this->options['cols'];
		$zone_width = ($this->options['width'] * $this->options['cols']) + ($this->options['spacing'] * ($this->options['cols'] - 1));
		$ad_width_ratio = ($this->options['width'] / $zone_width) * 100;
		$ad_margin_ratio = ($this->options['spacing'] / $zone_width) * 100;
		$ad_height = $this->options['height'];
		//use new window?
		$new_window = $this->settings->general_set['new_window'] == 1 ? ' target="_blank"' : '';
		//loop through ads
		foreach($ads as $ad) {
			//all items used?
			if($number >= $zone_items) {
				break;
			}
			//add one
			$number++;
			//set tracker ID
			if($ad->item_id > 0 && is_array($this->settings->pids)) {
				if($this->options['log_impressions'] === 'lazy') {
					$this->settings->pids[] = $ad->item_id;
				}
			}
			//set used ID
			if($ad->item_id != 0 && !in_array($ad->item_id, $this->used_ads[$this->zn])) {
				$this->used_ads[$this->zn][] = $ad->item_id;
			}
			//set nofollow html
			if($this->settings->{$this->zn}['nofollow'] == 1 || ($this->settings->{$this->zn}['nofollow'] == 2 && $ad->item_nofollow == 1)) {
				$nofollow = ' rel="nofollow"';
			} else {
				$nofollow = '';
			}
			//set target url
			if(empty($ad->item_page)) {
				$url = 'javascript://';
			} elseif($oiopub_module->tracker == 1 && $this->settings->tracker['enabled'] == 1 && $ad->item_id > 0 && $ad->direct_link == 0) {
				$url = $this->settings->tracker_url . '/go.php?id=' . $ad->item_id;
			} else {
				$url = $ad->item_page;
			}
			//set content
			if(isset($ad->item_empty) && $ad->item_empty == 1) {
				//empty ad slot
				$_content = '<a rel="nofollow"' . (stripos($url, 'http') === 0 ? $new_window : '') . ' href="' . $url . '" title="' . stripslashes($ad->item_tooltip) . '"><span class="oio-table"><span class="oio-cell">' . $ad->item_url . '</span></a>';
				//add number
				$number_empty++;
				//set empty class
				$_classes = ' oio-empty';
			} else {
				//filled ad slot
				if(!empty($ad->item_notes)) {
					//raw html
					$_content = oiopub_php_eval($ad->item_notes);
				} else {
					//get height dynamically?
					if(!$ad_height && $tmp = @getimagesize($ad->item_url)) {
						$w = isset($tmp[0]) ? (int) $tmp[0] : 0;
						$hr = $w ? ($this->options['width'] / $w) : 1;
						$ad_height = isset($tmp[1]) ? (int) ($tmp[1] * $hr) : 0;
					}
					//create img tag
					$_content = oiopub_image_display($ad->item_url, $url, $this->options['width'], $ad_height, 0, $ad->item_tooltip);
					//not a flash ad?
					if(strpos($_content, '<object') === false) {
						$_content = '<a' . $nofollow . (stripos($url, 'http') === 0 ? $new_window : '') . ' href="' . $url . '" title="' . stripslashes($ad->item_tooltip) . '">' . $_content . '</a>';
					}
				}
				//default image url?
				if(isset($ad->item_empty) && $ad->item_empty == 2) {
					$number_empty++;
				}
				//set filled class
				$_classes = '';
			}
			//last column?
			if($number % $this->options['cols'] == 0) {
				$_classes .= ' oio-last-col';
			}
			//last row?
			if(($zone_items - $this->options['cols']) < $number) {
				$_classes .= ' oio-last-row';
			}
			//open tag?
			if($this->options['markup_ad_tag']) {
				$_output .= '<' . $this->options['markup_ad_tag'] . ' class="oio-slot' . $_classes . '">';
			}
			//add content
			$_output .= $_content;
			//close tag?
			if($this->options['markup_ad_tag']) {
				$_output .= '</' . $this->options['markup_ad_tag'] . '>';
			}
			//new line
			$_output .= "\n";
			//all empty slots used?
			if(isset($ad->item_empty) && $this->options['empty'] >= 0) {
				if($this->options['empty'] <= $number_empty) {
					break;
				}
			}
		}
		//stop here?
		if(empty($_output)) {
			return;
		}
		//set dynamic css?
		if($this->options['markup_allow_style']) {
			//set position?
			if($this->options['align'] != 'none') {
				$position_css = ($this->options['align'] == 'center') ? ' margin: 0 auto !important;' : ' float: ' . $this->options['align'] . ' !important;';
			} else {
				$position_css = '';
			}
			//set aspect ratio
			$ad_aspect_ratio = $ad_height / $this->options['width'];
			//set css
			$output .= '<style type="text/css">' . "\n";
			$output .= '#oio-banner-' . $this->zone . ' { ' . ($this->options['fluid'] ? 'max-' : '') . 'width: ' . $zone_width . 'px !important;' . $position_css . ' }' . "\n";
			$output .= '#oio-banner-' . $this->zone . ' .oio-slot { width: ' . (floor($ad_width_ratio * 1000) / 1000) . '% !important; margin: 0% ' . (floor($ad_margin_ratio * 1000) / 1000) . '% ' . (floor($ad_margin_ratio * 1000) / 1000) . '% 0% !important; padding-bottom: ' . (floor($ad_width_ratio * $ad_aspect_ratio * 1000) / 1000) . '% !important; }' . "\n";
			$output .= '#oio-banner-' . $this->zone . ' .oio-last-col { margin-right: 0 !important; }' . "\n";
			$output .= '#oio-banner-' . $this->zone. ' .oio-last-row { margin-bottom: 0 !important; }' . "\n";
			$output .= '</style>' . "\n";
		}
		//add title?
		if($this->options['title']) {
			$output .= stripslashes($this->options['title']) . "\n";
		}
		//open tag?
		if($this->options['markup_zone_tag']) {
			$output .= '<' . $this->options['markup_zone_tag'] . $this->options['markup_zone_class'] . $this->options['markup_zone_id'] . '>' . "\n";
		}
		//add content
		$output .= $_output;
		//close tag?
		if($this->options['markup_zone_tag']) {
			$output .= '</' . $this->options['markup_zone_tag'] . '>' . "\n";
		}
		//clear float?
		if(!$this->options['wrap']) {
			if($this->options['align'] == "left" || $this->options['align'] == "right") {
				$output .= '<div class="oio-clear-' . $this->options['align'] . '"></div>' . "\n";
			}
		}
		//add script?
		if($this->options['fluid']) {
			$output .= '<script>' . "\n";
			$output .= '(function(){for(var a=document.getElementById("oio-banner-' . $this->zone . '"),b=a,c,d=window.getComputedStyle;!a.clientWidth;){b=b.parentNode;if(!b||!d||1!==b.nodeType)break;c=d(b,null).getPropertyValue("float");if("left"==c||"right"==c)b.style.width="100%",b.style.maxWidth="' . $zone_width . 'px"};})();' . "\n";
			$output .= '</script>' . "\n";	
		}
		//return
		return $output;
	}

	//format link ad output
	function format_output_link($ads) {
		global $oiopub_module;
		//set vars
		$output = '';
		$_output = '';
		$number = 0;
		$number_empty = 0;
		//set zone vars
		$zone_items = $this->options['rows'] * $this->options['cols'];
		$zone_width = $this->options['list'] ? 0 : ($this->options['width'] - ($this->options['border'] * 2));
		$zone_height = $this->options['list'] ? 0 : ($this->options['height'] - ($this->options['border'] * 2));
		//li attributes
		$_width = $zone_width > 0 ? "width:" . floor(($zone_width / $this->options['cols']) - ($this->options['padding'] * 2)) . "px; " : "";
		$_height = $zone_height > 0 ? "height:" . floor(($zone_height / $this->options['rows']) - ($this->options['padding'] * 2)) . "px;" : "";
		//use new window?
		$new_window = $this->settings->general_set['new_window'] == 1 ? ' target="_blank"' : '';
		//loop through ads
		foreach($ads as $ad) {
			//all items used?
			if($number >= $zone_items) {
				break;
			}
			//empty ad?
			if(!$ad->item_page) {
				continue;
			}
			//add number
			$number++;
			//set tracker ID
			if($ad->item_id > 0 && is_array($this->settings->pids)) {
				if($this->options['log_impressions'] === 'lazy') {
					$this->settings->pids[] = $ad->item_id;
				}
			}
			//set used ID
			if($ad->item_id != 0 && !in_array($ad->item_id, $this->used_ads[$this->zn])) {
				$this->used_ads[$this->zn][] = $ad->item_id;
			}
			//set nofollow html
			if($this->settings->{$this->zn}['nofollow'] == 1 || ($this->settings->{$this->zn}['nofollow'] == 2 && $ad->item_nofollow == 1)) {
				$nofollow = ' rel="nofollow"';
			} else {
				$nofollow = '';
			}
			//set target url
			if(empty($ad->item_url)) {
				$url = 'javascript://';
			} elseif($oiopub_module->tracker == 1 && $this->settings->tracker['enabled'] == 1 && $ad->item_id > 0 && $ad->direct_link == 0) {
				$url = $this->settings->tracker_url . '/go.php?id=' . $ad->item_id;
			} else {
				$url = $ad->item_url;
			}
			//set content
			if(isset($ad->item_empty) && $ad->item_empty == 1) {
				//empty slot allowed?
				if($this->options['list'] && $this->settings->{$this->zn}['advertise_here'] != 1) {
					break;
				}
				$_desc = '';
				$_content = '<a class="empty" rel="nofollow"' . (stripos($url, 'http') === 0 ? $new_window : '') . ' href="' . $url . '" title="' . stripslashes($ad->item_tooltip) . '">' . stripslashes($ad->item_page) . '</a>';
				//add number
				$number_empty++;
			} else {
				$_desc = '';
				//add description?
				if(!empty($ad->item_notes)) {
					//open tag?
					if($this->options['markup_desc_tag']) {
						$_desc .= '<' . $this->options['markup_desc_tag'] . $this->options['markup_desc_class'] . '>';
					}
					//add content
					$_desc .= oiopub_strlimit($ad->item_notes, $this->settings->{$this->zn}['desc_length']);
					//close tag?
					if($this->options['markup_desc_tag']) {
						$_desc .= '</' . $this->options['markup_desc_tag'] . '>';
					}
				}
				//set content
				$_content = '<a' . $nofollow . (stripos($url, 'http') === 0 ? $new_window : '') . ' href="' . $url . '" title="' . stripslashes($ad->item_tooltip) . '">' . stripslashes($ad->item_page) . '</a>' . $_desc;				
				//wrap element?
				if(!$this->options['list']) {
					$_content = '<span class="cell">' . $_content . '</span>';
				}
			}
			//open tag?
			if($this->options['markup_ad_tag']) {
				if($this->options['list']) {
					$style_tag = '';
				} else {
					$style_tag = $this->options['markup_allow_style'] ? ' style="' . $_width . $_height . '"' : '';
				}
				$_output .= '<' . $this->options['markup_ad_tag'] . $this->options['markup_ad_class'] . $style_tag . '>';
			}
			//add content
			$_output .= $_content;
			//close tag?
			if($this->options['markup_ad_tag']) {
				$_output .= '</' . $this->options['markup_ad_tag'] . '>';
			}
			//new line
			$_output .= "\n";
			//all empty slots used?
			if(isset($ad->item_empty) && $this->options['empty'] >= 0) {
				if($this->options['empty'] <= $number_empty) {
					break;
				}
			}
		}
		//stop here?
		if(empty($_output)) {
			return;
		}
		//zone title
		$output .= stripslashes($this->options['title']) . "\n";
		//get position css
		if($this->options['align'] != 'none') {
			$position_css = ($this->options['align'] == 'center' ? 'margin:0 auto;' : 'float:' . $this->options['align'] . ';');
		} else {
			$position_css = '';
		}
		//list format?
		if($this->options['list']) {
			$style = '';
			$this->options['markup_zone_class'] = str_replace('zone', 'list', $this->options['markup_zone_class']);
		} else {
			$style = '';
			if($zone_width > 0) {
				$style .= 'width:' . $zone_width . 'px; ';
			}
			if($zone_height > 0) {
				$style .= 'height:' . $zone_height . 'px; ';
			}
			$style = ' style="' . trim($style . $position_css) . '"';
		}
		//open tag?
		if($this->options['markup_zone_tag']) {
			$style_tag = $this->options['markup_allow_style'] ? $style : '';
			$output .= '<' . $this->options['markup_zone_tag'] . $this->options['markup_zone_class'] . $this->options['markup_zone_id'] . $style_tag . '>' . "\n";
		}
		//add content
		$output .= $_output;
		//close tag?
		if($this->options['markup_zone_tag']) {
			$output .= '</' . $this->options['markup_zone_tag'] . '>' . "\n";
		}
		//clear float?
		if(!$this->options['wrap']) {
			if($this->options['align'] == "left" || $this->options['align'] == "right") {
				$output .= '<hr class="oio-clear-' . $this->options['align'] . '" />' . "\n";
			}
		}
		//return
		return $output;
	}

	//get array of valid tokens
	function parse_list($input, $sanitize='', $token=',', $unique=true) {
		//set vars
		$res = array();
		$input = is_array($input) ? $input : explode($token, $input);
		//loop through array
		foreach($input as $val) {
			//sanitize value?
			if(!empty($sanitize)) {
				$val = $sanitize($val);
			}
			//is valid?
			if(!empty($val)) {
				$res[] = $val;
			}
		}
		//unique
		if($unique !== false) {
			$res = array_unique($res);
		}
		//return
		return $res;
	}

	//format options
	function format_options($options) {
		//set options
		$options = is_array($options) ? $options : array();
		//lowercase keys
		foreach($options as $key => $val) {
			$k = strtolower($key);
			if($k !== $key) {
				unset($options[$key]);
				$options[$k] = $val;
			}
		}
		//fix alignment?
		if(isset($options['position'])) {
			$options['align'] = $options['position'];
		}
		//option defaults
		$defaults = array(
			'zones' => array(),
			'align' => "center",
			'echo' => true,
			'title' => "",
			'width' => 0,
			'height' => 0,
			'cols' => 0,
			'rows' => 0,
			'spacing' => 0,
			'border' => 2,
			'padding' => 2,
			'empty' => 1,
			'subid' => "",
			'ref' => 0,
			'category' => "",
			'purchase' => "",
			'list' => true,
			'repeats' => true,
			'wrap' => false,
			'fluid' => true,
			'shuffle' => true,
			'show' => 'all',
			'raw_data' => false,
			'log_impressions' => 'lazy',
			'markup_zone_tag' => "ul",
			'markup_zone_id' => "oio-{type}-{zone}",
			'markup_zone_class' => "oio-{type}-zone",
			'markup_ad_tag' => "li",
			'markup_ad_class' => "",
			'markup_ad_class_empty' => "border oio-center",
			'markup_desc_tag' => "div",
			'markup_desc_class' => "desc",
			'markup_allow_tags' => '',
			'markup_allow_style' => true,
			'markup_allow_single' => true,
			'markup_before' => '',
			'markup_after' => '',
		);
		//admin defaults
		$admin = array(
			'width' => $this->settings->{$this->zn}['width'],
			'height' => $this->settings->{$this->zn}['height'],
			'cols' => $this->settings->{$this->zn}['cols'],
			'rows' => $this->settings->{$this->zn}['rows'],
			'spacing' => $this->settings->{$this->zn}['spacing'],
			'list' => (bool) isset($this->settings->{$this->zn}['list']) && $this->settings->{$this->zn}['list'],
		);
		//loop through defaults
		foreach($defaults as $key => $val) {
			if(isset($options[$key])) {
				//user supplied
			} elseif(isset($admin[$key])) {
				//admin config
				$options[$key] = $admin[$key];
			} else {
				//default
				$options[$key] = $val;
			}
			//sanitize input
			if(is_int($val)) {
				$options[$key] = intval($options[$key]);
			} elseif(is_bool($val)) {
				$options[$key] = $options[$key] && strtolower($options[$key]) !== 'false';
			} elseif(is_string($val) && $key !== 'markup_before' && $key !== 'markup_after') {
				$options[$key] = strip_tags($options[$key], '<h1><h2><h3><h4>');
			}
		}
		//format markup?
		foreach($options as $key => $val) {
			//is markup?
			if(strpos($key, 'markup_') !== 0) {
				continue;
			}
			//format ID?
			if($val && strpos($key, "_id") !== false) {
				$options[$key] = ' id="' . str_replace(array('{type}', '{zone}'), array($this->type, $this->zone), $val) . '"';
			}
			//format class?
			if($val && strpos($key, "_class") !== false) {
				$options[$key] = ' class="' . str_replace(array('{type}', '{zone}'), array($this->type, $this->zone), $val) . '"';
			}
		}
		//return
		return $options;
	}

	//format legacy options
	function format_legacy_options($args) {
		//options already exist?
		if(isset($args[1]) && is_array($args[1])) {
			return $args[1];
		}
		//set vars
		$options = array();
		//legacy args list
		$args_list = array( 'position', 'title', 'echo', 'cols', 'rows', 'border', 'padding' );
		//loop through args
		foreach($args_list as $key => $val) {
			if(isset($args[$key+1])) {
				$options[$val] = $args[$key+1];
				if($val == "echo" && $options[$val] == 1) {
					$options[$val] = false;
				}
				continue;
			} 
			break;
		}
		//return
		return $options;
	}

	//calculate total weight
	function calc_total_weight($weights) {
		//set vars
		$total = 0;
		//loop through weights
		foreach($weights as $key => $val) {
			$total += $val['weight'];
		}
		//return
		return $total;
	}

}


/* OUTPUT WRAPPER FUNCTIONS */

//link zone output
if(!function_exists('oiopub_link_zone')) {
	function oiopub_link_zone($zone=1, $options=array()) {
		static $output;
		//create object?
		if(!$output) {
			$output = new oiopub_output;
		}
		//transform legacy options
		$options = $output->format_legacy_options(func_get_args());
		//get zone data
		return $output->zone('link', $zone, $options);
	}
}

//banner zone output
if(!function_exists('oiopub_banner_zone')) {
	function oiopub_banner_zone($zone=1, $options=array()) {
		static $output;
		//create object?
		if(!$output) {
			$output = new oiopub_output;
		}
		//transform legacy options
		$options = $output->format_legacy_options(func_get_args());
		//get zone data
		return $output->zone('banner', $zone, $options);
	}
}


/* MISC OUTPUT FUNCTIONS */

//display ad slots available
if(!function_exists('oiopub_ad_slots')) {
	function oiopub_ad_slots($title='') {
		global $oiopub_set, $oiopub_db;
		//check allowed
		if($oiopub_set->enabled != 1) {
			return;
		}
		//clear vars
		$res = '';
		$output = '';
		$links_total = 0;
		$banners_total = 0;
		$inline_total = 0;
		$intext_total = 0;
		$posts_total = 0;
		//used ads
		$used = oiopub_ads_unavailable();
		//text ad spots
		if($oiopub_set->links_total > 0) {
			for($z=1; $z <= $oiopub_set->links_zones; $z++) {
				$lz = "links_" . $z;
				if($oiopub_set->{$lz}['enabled'] == 1 && $oiopub_set->{$lz}['price'][0] > 0) {
					$links_total += ($oiopub_set->{$lz}['cols'] * $oiopub_set->{$lz}['rows'] * $oiopub_set->{$lz}['rotator']) + $oiopub_set->{$lz}['queue']; 
					if(isset($used[2][$z])) {
						$links_total -= $used[2][$z];
					}
				}
			}
		}
		//banner ad spots
		if($oiopub_set->banners_total > 0) {
			for($z=1; $z <= $oiopub_set->banners_zones; $z++) {
				$bz = "banners_" . $z;
				if($oiopub_set->{$bz}['enabled'] == 1 && $oiopub_set->{$bz}['price'][0] > 0) {
					$banners_total += ($oiopub_set->{$bz}['cols'] * $oiopub_set->{$bz}['rows'] * $oiopub_set->{$bz}['rotator']) + $oiopub_set->{$bz}['queue']; 
					if(isset($used[5][$z])) {
						$banners_total -= $used[5][$z];
					}
				}
			}
		}
		//inline ad spots
		if($oiopub_set->inline_ads['price'][0] > 0) {
			if($oiopub_set->inline_ads['enabled'] == 1) {
				$inline_total = $oiopub_set->inline_ads['rotator'] + $oiopub_set->inline_ads['queue'];
				if(isset($used[3][$oiopub_set->inline_ads['selection']])) {
					$inline_total -= $used[3][$oiopub_set->inline_ads['selection']];
				}
			}
		}
		//intext link spots
		if($oiopub_set->inline_links['price'][0] > 0) {
			if($oiopub_set->inline_links['enabled'] == 1) {
				$intext_total = 1;
			}
		}
		//post spots
		if($oiopub_set->posts_total > 0) {
			$posts_total = 1;
		}
		if($links_total > 0) {
			$output .= "<li><a rel=\"nofollow\" href=\"" . $oiopub_set->plugin_url . "/purchase.php?do=link\">" . $links_total . "</a> " . __oio("Text Ad slots open") . "</li>\n";
		}
		if($banners_total > 0) {
			$output .= "<li><a rel=\"nofollow\" href=\"" . $oiopub_set->plugin_url . "/purchase.php?do=banner\">" . $banners_total . "</a> " . __oio("Banner Ad slots open") . "</li>\n";
		}
		if($inline_total > 0) {
			$output .= "<li><a rel=\"nofollow\" href=\"" . $oiopub_set->plugin_url . "/purchase.php?do=inline&type=" . $oiopub_set->inline_ads['selection'] . "\">" . $inline_total . "</a> " . __oio("Inline Ad slots open") . "</li>\n";
		}
		if($intext_total > 0) {
			$output .= "<li><a rel=\"nofollow\" href=\"" . $oiopub_set->plugin_url . "/purchase.php?do=inline&type=4\">" . __oio("Intext Links") . "</a> " . __oio("are available") . "</li>\n";
		}
		if($posts_total > 0) {
			$output .= "<li><a rel=\"nofollow\" href=\"" . $oiopub_set->plugin_url . "/purchase.php?do=post\">" . __oio("Paid Reviews") . "</a> " . __oio("are available") . "</li>\n";
		}
		if(!empty($output)) {
			$res .= $title;
			$res .= "<ul class=\"oio-openslots\">" . $output . "</ul>\n";
		}
		echo $res;
	}	
}

//ad badge
if(!function_exists('oiopub_ad_badge')) {
	function oiopub_ad_badge($image='', $width='', $height='') {
		global $oiopub_set, $oiopub_module;
		//check allowed
		if($oiopub_set->enabled != 1) {
			return;
		}
		//get vars
		$width = empty($width) ? 125 : $width;
		$height = empty($height) ? 125 : $height;
		$image = empty($image) ? $oiopub_set->plugin_url . '/images/badge.png' : $image;
		//get image display
		$display = oiopub_image_display($image, "", $width, $height, 0, "Ad Badge");
		//build html
		echo "<div class=\"oio-badge oio-center\">\n";
		echo "<a rel=\"nofollow\" href=\"" . $oiopub_set->plugin_url . "/purchase.php\">" . $display . "</a>\n";
		if(isset($oiopub_module->affiliates) && $oiopub_module->affiliates == 1 && $oiopub_set->affiliates['enabled'] == 1) {
			echo "<br />\n";
			echo "<a rel=\"nofollow\" href=\"" . $oiopub_set->affiliates_url . "\">" . __oio("Affiliate Program") . "</a>\n";
		}
		echo "</div>\n";
	}
}

//header output
if(!function_exists('oiopub_header_output')) {
	function oiopub_header_output() {
		global $oiopub_set;
		//check allowed
		if($oiopub_set->enabled != 1) {
			return;
		}
		//format versio
		$v = str_replace('.', '', $oiopub_set->version);
		//get output
		echo '<link rel="stylesheet" href="' . $oiopub_set->plugin_url . '/images/style/output.css?' . $v . '" type="text/css" />' . "\n";
		if($oiopub_set->inline_ads['price'] > 0 && $oiopub_set->inline_ads['selection'] == 3) {
			echo '<!-- RSS Display Boxes - Dynamic Drive DHTML code library (www.dynamicdrive.com) -->' . "\n";
			echo '<script type="text/javascript" src="' . $oiopub_set->plugin_url . '/libs/rssbox/rssbox.js?' . $v . '"></script>' . "\n";
			echo '<style type="text/css">#myrss_feed { width:' . $oiopub_set->inline_ads['width'] . 'px; }</style>' . "\n";
		}
	}
}

//header hook wrapper
if(!function_exists('oiopub_header')) {
	function oiopub_header() {
		global $oiopub_hook;
		$oiopub_hook->fire('oiopub_header');
	}
}

//footer hook wrapper
if(!function_exists('oiopub_footer')) {
	function oiopub_footer() {
		global $oiopub_hook;
		$oiopub_hook->fire('oiopub_footer');
	}
}

//ad query data (legacy)
if(!function_exists('oiopub_ad_query')) {
	function oiopub_ad_query($channel=0, $type=0, $sql_extra='') {
		global $oiopub_db, $oiopub_set;
		//set vars
		$res = array();
		$type = intval($type);
		$channel = intval($channel);
		//continue?
		if($oiopub_set->enabled == 1 && $channel > 0) {
			$sql = "SELECT * FROM " . $oiopub_set->dbtable_purchases . " WHERE item_channel='$channel' AND item_status='1' AND payment_status='1'" . ($type > 0 ? " AND item_type='$type'" : "") . $sql_extra;
			$res = $oiopub_db->GetAll($sql);
			$res = is_array($res) ? $res : array();
		}
		//return
		return $res;
	}
}

?>