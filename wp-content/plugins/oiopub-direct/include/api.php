<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

//api
class oiopub_api {

	var $oiohost = "http://api.oiopublisher.com";
	
	//socket connection
	function fsconn($host, $request, $buffer=1024, $headers=0) {
		//set vars
		$timeout = 3;
		$blocking = 1;
		$result = false;
		//connection made?
		if(!$fp = @fsockopen($host, 80, $errno, $errstr, $timeout)) {
			return $result;
		}
		//set stream options?
		if(function_exists('stream_set_timeout')) {
			@stream_set_timeout($fp, $timeout);
			@stream_set_blocking($fp, $blocking);
		}
		//write to stream
		@fputs($fp, $request, strlen($request));
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
			//add line
			$result .= $tmp;
		}
		//close
		@fclose($fp);
		//get data?
		if($result && is_string($result)) {
			list($h, $b) = explode("\r\n\r\n", $result, 2);
			$result = $headers ? $h : $b;
		}
		//return
		return $result;
	}

	//send data
	function send($url, $data=array(), $method="POST", $buffer=1024, $headers=0) {
		global $oiopub_set;
		//set vars
		$res = false;
		$parse = parse_url($url);
		if(!empty($parse['host']) && !empty($parse['path'])) {
			//get rand key
			$rand_key = oiopub_rand(16);
			//build vars
			$vars  = "api_key=" . urlencode($oiopub_set->api_key);
			$vars .= "&api_rand_key=" . urlencode($rand_key);
			$vars .= "&api_version=" . urlencode($oiopub_set->version);
			$vars .= "&api_plugin=" . urlencode($oiopub_set->plugin_url_org . "/purchase.php");
			$vars .= "&api_data=" . urlencode(serialize($data));
			$rand_val = md5($vars);
			oiopub_add_config($rand_key, $rand_val);
			$vars .= "&api_rand_val=" . urlencode($rand_val);
			//build headers
			$header  = $method . " " . $parse['path'] . " HTTP/1.0\r\n";
			$header .= "Host: " . $parse['host'] . "\r\n";
			$header .= "User-agent: OIOpub Direct API: client v" . $oiopub_set->version . "\r\n";
			$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
			$header .= "Content-Length: " . strlen($vars) . "\r\n";
			$header .= "Connection: Close\r\n\r\n";
			$request = $header . $vars;
			//make call
			$res = $this->fsconn($parse['host'], $request, $buffer, $headers);
			//delete rand key
			oiopub_delete_config($rand_key);
		}
		return $res;
	}
	
	//make postback call
	function postback_call($method='POST', $buffer=1024, $headers=0) {
		global $oiopub_set;
		//set vars
		$res = "FAILED";
		//post vars
		$api_rand_key = oiopub_decode($_POST['api_rand_key']);
		$api_rand_val = oiopub_decode($_POST['api_rand_val']);
		if(strlen($api_rand_key) == 16 && strlen($api_rand_val) == 32) {
			//get api url
			$url = $this->oiohost . "/postback.php";
			$parse = parse_url($url);
			//request vars
			$vars  = "action=" . urlencode('postback_check');
			$vars .= "&api_rand_key=" . urlencode($api_rand_key);
			$vars .= "&api_rand_val=" . urlencode($api_rand_val);
			//header vars
			$header  = $method . " " . $parse['path'] . " HTTP/1.0\r\n";
			$header .= "Host: " . $parse['host'] . "\r\n";
			$header .= "User-Agent: OIOpub Direct API: client v" . $oiopub_set->version . "\r\n";
			$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
			$header .= "Content-Length: " . strlen($vars) . "\r\n";
			$header .= "Connection: Close\r\n\r\n";
			$request = $header . $vars;
			//make call
			$res = $this->fsconn($parse['host'], $request, $buffer, $headers);
		}
		return $res;
	}
	
	//postback check
	function postback_check($api_rand_key, $api_rand_val) {
		$check = oiopub_get_config($api_rand_key);
		if(strlen($check) == 32) {
			if($check === $api_rand_val) {
				return "SUCCESS";
			}
		}
		return "FAILED";
	}

	//api validate key
	function validate_key($key) {
		global $oiopub_set;
		$data = array();
		$data['action'] = "validate";
		$data['validate'] = "key";
		$data['validate_key'] = $key;
		$url = $this->oiohost . "/validate.php";
		$res = $this->send($url, $data);
		return $res;
	}
	
	//check api key
	function check_key($key, $echo=0) {
		global $oiopub_set;
		$res = true;
		if(strlen($oiopub_set->api_key) != 16 || $oiopub_set->api_valid != 1) {
			$res = "NOAPI";
		} elseif(strlen($key) != 16 || strcmp($key, $oiopub_set->api_key) != 0) {
			$res = "BADAPI";
		}
		if($echo == 1 && $res !== true) {
			echo $res;
			exit();
		}
		return $res;
	}

	//check global password
	function check_pass($pass, $echo=0) {
		global $oiopub_set;
		$res = true;
		if(strlen($oiopub_set->global_pass) != 32) {
			$res = "NOPASS";
		} elseif(strlen($pass) != 32 || strcmp($pass, $oiopub_set->global_pass) != 0) {
			$res = "BADPASS";
		}
		if($echo == 1 && $res !== true) {
			echo $res;
			exit();
		}
		return $res;
	}

	//settings send
	function settings_send($header=0) {
		global $oiopub_set;
		if(strlen($oiopub_set->api_key) == 16 && $oiopub_set->api_valid == 1) {
			if($header == 1) {
				oiopub_header_redirect();
			}
			$data = array();
			$data['api_action'] = "settings";
			$data['plugin_url'] = $oiopub_set->plugin_url_org . "/api.php?do=settings";
			$url = $this->oiohost . "/settings.php";
			$res = $this->send($url, $data);
		}
		return $res;
	}

	//status send
	function status_send($id, $action, $status, $pid='') {
		global $oiopub_db, $oiopub_set;
		$id = intval($id);
		if(strlen($oiopub_set->api_key) == 16 && $oiopub_set->api_valid == 1) {
			$query = $oiopub_db->GetRow("SELECT item_channel,submit_api,rand_id FROM ".$oiopub_set->dbtable_purchases." WHERE item_id='$id'");
			if(!empty($query->rand_id) && $query->submit_api > 0) {
				if($query->submit_api == 1) $file = "/settings.php";
				if($query->submit_api == 2) $file = "/jobs.php";
				$data = array();
				$data['api_action'] = $action;
				$data['purchase_type'] = oiopub_type_check($query->item_channel);
				$data['purchase_status'] = $status;
				$data['purchase_rand'] = $query->rand_id;
				$data['purchase_channel'] = $query->item_channel;
				$data['published_id'] = $pid;
				$url = $this->oiohost . $file;
				$res = $this->send($url, $data);
			}
		}
		return $res;
	}
	
	//get settings (xml)
	function get_settings($settings, $forbidden=array()) {
		global $oiopub_set;
		$res = "";
		if(oiopub_count($settings) > 0) {
			@header('content-type: text/xml');
			$res .= '<?xml version="1.0"?>' . "\n";
			$res .= "<settings>\n";
			foreach($settings as $s) {
				if(!in_array($s->name, $forbidden)) {
					$zone_max = 0;
					$name = oiopub_stripslashes($s->name);
					$value = oiopub_unserialize($s->value);
					if(strpos($name, "banners_") !== false) {
						if(strpos($name, "banners_zones") === false) {
							$zone_max = $oiopub_set->banners_zones;
							$exp = explode("_", $name);
							$zone = $exp[1];
						}
					} elseif(strpos($name, "links_") !== false) {
						if(strpos($name, "links_zones") === false) {
							$zone_max = $oiopub_set->banners_zones;
							$exp = explode("_", $name);
							$zone = $exp[1];
						}
					} elseif(strpos($name, "custom_") !== false) {
						if(strpos($name, "custom_num") === false) {
							$zone_max = $oiopub_set->custom_num;
							$exp = explode("_", $name);
							$zone = $exp[1];
						}
					}
					if($zone_max == 0 || $zone <= $zone_max) {
						if(is_array($value)) {
							$res .= "	<" . $name . ">\n";
							foreach($value as $k => $v) {
								$res .= "		<" . $k . ">" . $v . "</" . $k . ">\n";
							}
							$res .= "	</" . $name . ">\n";
						} else {
							$res .= "	<" . $name . ">" . $value . "</" . $name . ">\n";
						}
					}
				}
			}
			//custom entries
			$res .= "	<posts_allowed>" . (oiopub_posts === false ? 0 : 1) . "</posts_allowed>\n";
			$res .= "	<links_allowed>" . (oiopub_links === false ? 0 : 1) . "</links_allowed>\n";
			$res .= "	<banners_allowed>" . (oiopub_banners === false ? 0 : 1) . "</banners_allowed>\n";
			$res .= "	<inline_allowed>" . (oiopub_inline === false ? 0 : 1) . "</inline_allowed>\n";
			$res .= "	<custom_allowed>" . (oiopub_custom === false ? 0 : 1) . "</custom_allowed>\n";
			$res .= "	<plugin_url>" . $oiopub_set->plugin_url_org . "/purchase.php" . "</plugin_url>\n";
			$res .= "	<platform>" . $oiopub_set->platform . "</platform>\n";
			$res .= "	<api>" . ($this->check_key($oiopub_set->api_key) === true ? 1 : 0) . "</api>\n";
			$res .= "</settings>\n";
		} else {
			$res = "NODATA";
		}
		return $res;
	}
	
	//get purchases (xml)
	function get_purchases($purchases) {
		global $oiopub_set;
		$res = "";
		if(oiopub_count($purchases) > 0) {
			@header('content-type: text/xml');
			$res .= '<?xml version="1.0"?>' . "\n";
			$res .= '<purchases>' . "\n";
			foreach($purchases as $p) {
				$item = oiopub_adtype_info($p);
				$res .= '<item>' . "\n";
				$res .= '	<id>' . $p->item_id . '</id>' . "\n";
				$res .= '	<site>' . $oiopub_set->plugin_url_org . '</site>' . "\n";
				$res .= '	<channel>' . $p->item_channel . '</channel>' . "\n";
				$res .= '	<item-status>' . $p->item_status . '</item-status>' . "\n";
				$res .= '	<payment-status>' . $p->payment_status . '</payment-status>' . "\n";
				$res .= '	<item-type>' . $item["type"] . '</item-type>' . "\n";
				$res .= '	<api-type>' . $p->submit_api . '</api-type>' . "\n";
				$res .= '	<cost>' . $p->payment_amount . '</cost>' . "\n";
				$res .= '	<currency>' . $p->payment_currency . '</currency>' . "\n";
				$res .= '	<expire>' . $item["expire"] . '</expire>' . "\n";
				$res .= '	<name>' . $p->adv_name . '</name>' . "\n";
				$res .= '	<email>' . $p->adv_email . '</email>' . "\n";
				$res .= '	<url>' . $item["url"] . '</url>' . "\n";
				$res .= '	<image>' . $item["image"] . '</image>' . "\n";
				$res .= '	<author>' . $p->post_author . '</author>' . "\n";
				$res .= '</item>' . "\n";
			}
			$res .= '</purchases>' . "\n";
		} else {
			$res = "NODATA";
		}
		return $res;
	}
	
	//update settings
	function update_settings($data) {
		$res = "INVALID";
		if(oiopub_count($data) > 0) {
			$data['action'] = "update_settings";
			$url = $this->oiohost . "/global.php";
			$check = $this->send($url, $data);
			if($check == "VALID") {
				foreach($data as $key => $val) {
					oiopub_update_config($key, $val);
				}
				$res = "VALID";
			}
		}
		return $res;
	}
	
	//update purchases
	function update_purchases($id, $status) {
		$res = "INVALID";
		$data = array();
		$data['action'] = "update_purchases";
		$data['status'] = $status;
		$data['id'] = $id;
		$url = $this->oiohost . "/global.php";
		$check = $this->send($url, $data);
		if($check == "VALID") {
			oiopub_approve($status, $id);
			$res = "VALID";
		}
		return $res;
	}

	//jobs matcher
	function jobs_match() {
		global $oiopub_set;
		if(strlen($oiopub_set->api_key) == 16 && $oiopub_set->api_valid == 1) {
			$data = array();
			$data['api_action'] = "process";
			$url = $this->oiohost . "/jobs-match.php";
			$res = $this->send($url, $data);
			if(!empty($res) && $res != "NONE") {
				$res = unserialize($res);
			}
		}
		return $res;
	}
	
	//auto upgrade
	function auto_upgrade() {
		$data = array();
		$data['action'] = "pro_upgrade";
		$url = $this->oiohost . "/auto-upgrade.php";
		return $this->send($url, $data);
	}
	
}

?>