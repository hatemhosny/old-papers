<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

//spam class
class oiopub_spam {

	var $ip;
	var $agent;
	var $referer;
	
	var $vars = array( "ip", "agent", "referer" );
	var $filters = array( "blacklist", "whitelist" );
	
	//init
	function oiopub_spam() {
		global $oiopub_set;
		//remove querysting
		$exp = explode("?", oiopub_var('HTTP_REFERER', 'server'));
		//set vars
		$this->ip = $oiopub_set->client_ip;
		$this->agent = oiopub_var('HTTP_USER_AGENT', 'server');
		$this->referer = trim($exp[0]);
		//ip2long check
		$ip2long = ip2long($this->ip);
		//valid IP?
		if($ip2long === false || $ip2long == -1) {
			$this->ip = "";
		}
	}
	
	//run var through filters
	function allow_var($var, $block_empty=1) {
		global $oiopub_set;
		if(!in_array($var, $this->vars)) {
			return false;
		}
		if(empty($this->{$var})) {
			return $block_empty == 0 ? true : false;
		}
		if($var == "agent" && $this->is_spider()) {
			return false;
		}
		$var_data = $var . "_filter_data";
		$var_filter = $var . "_filter";
		$filter = $oiopub_set->tracker[$var_filter];
		$reverse = $filter == "whitelist" ? 0 : 1;
		if(method_exists($this, $filter)) {
			return $this->{$filter}($var, $oiopub_set->{$var_data}, $reverse);
		}
		return false;
	}
	
	//get blacklist
	function blacklist($var='', $data=array(), $reverse=0) {
		if(isset($this->{$var})) {
			if(!empty($data)) {
				foreach($data as $d) {
					$d = trim($d);
					if(!empty($d)) {
						if(stripos($this->{$var}, $d) !== false) {
							return $reverse == 0 ? true : false;
						}
					}
				}
				return $reverse == 0 ? false : true;
			}
		}
		return $reverse == 0 ? false : true;
	}
	
	//get whitelist
	function whitelist($var='', $data=array(), $reverse=0) {
		if(isset($this->{$var})) {
			if(!empty($data)) {
				foreach($data as $d) {
					$d = trim($d);
					if(!empty($d)) {
						if(stripos($this->{$var}, $d) !== false) {
							return $reverse == 0 ? true : false;
						}
					}
				}
				return $reverse == 0 ? false : true;
			}
		}
		return $reverse == 0 ? true : false;
	}
	
	//is spider?
	function is_spider() {
		//check number of segments
		$segments = explode(" ", $this->agent);
		//below 4, probably a bot
		if(count($segments) < 4) {
			return true;
		}
		//list of agents to check
		$agents = array( "bot", "spider", "crawl", "google", "yahoo", "msn", "ask", "altavista", "aggregator", "sphere", "auto", "curl" );
		//loop through agents
		foreach($agents as $a) {
			//match found?
			if(stripos($this->agent, $a) !== false) {
				return true;
			}
		}
		return false;	
	}

	//is browser?
	function is_browser() {
		//browser arrays
		$browsers = array( 'firefox', 'chrome', 'opera', 'konqueror', 'seamonkey', 'msie', 'safari' );
		//standardise agent
		$pattern = '#(?<browser>' . join('|', $browsers) . ')[/ ]+(?<version>[0-9]+(?:\.[0-9]+)?)#';
		//search for matches
		if(!@preg_match_all($pattern, strtolower($this->agent), $matches)) {
			return false;
		}
		//default count
		$i = count($matches['browser']) - 1;
		//loop through browsers
		foreach($browsers as $b) {
			//match found?
			if(in_array($b, $matches['browser'])) {
				$flip = array_flip($matches['browser']);
				$i = $flip[$b];
				break;
			}
		}
		//return browser & version
		return ucfirst($matches['browser'][$i]) . " " . $matches['version'][$i];	
	}

}

?>