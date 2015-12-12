<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

//cache
class oiopub_cache {

	var $cache_dir;
	var $cache_type;
	var $cache_class;
	var $cache_array = array( "", "file", "memcache", "xcache", "eaccelerator" );
	var $errors = array();
	
	//init
	function oiopub_cache($args=array()) {
		if(!is_array($args)) return;
		if(!in_array($args['cache_type'], $this->cache_array)) {
			$args['cache_type'] = "file";
		}
		$this->cache_type($args['cache_type'], $args['cache_dir'], $args['memcache_host'], $args['memcache_port']);
	}
	
	//collect errors
	function error($string='') {
		$this->errors[] = $string;
	}
	
	//hash of key
	function key_hash($key) {
		$key = md5(trim($key));
		return $key;
	}
	
	//check if serialized
	function is_serialized($data) {
		if(trim($data) != "" && is_string($data)) {
			if(preg_match("/^[adobis]:[0-9]+:.*[;}]/si", $data)) {
				return unserialize($data);
			}
		}
		return $data;
	}
	
	//cache type
	function cache_type($type='file', $dir='', $mchost='', $mcport='') {
		global $oiopub_set;
		if(!in_array($type, $this->cache_array)) {
			$this->cache_type = "file";
		} else {
			$this->cache_type = $type;
		}
		if($this->cache_type == "file") {
			//file cache
			$this->cache_dir = $dir;
			if(!@is_writable($this->cache_dir)) {
				$this->error("Please make the OIOpublisher 'cache' directory writable");
			}
			if(!empty($oiopub_set->prefix)) {
				$this->cache_dir = $dir . "/" . $oiopub_set->prefix;
				if(!@is_dir($this->cache_dir)) {
					@mkdir($this->cache_dir, 0777);
				}
				if(!@is_writable($this->cache_dir)) {
					$this->cache_dir = $dir;
				}
			}
		} elseif($this->cache_type == "memcache") {
			//memcache
			if(function_exists('memcache_connect')) {
				global $memcache;
				if(!isset($memcache)) {
					$memcache = new Memcache;
					if($memcache->connect($mchost, $mcport)) {
						$this->cache_class = $memcache;
					} else {
						$this->error("Cannot connect to Memcache - check settings in the OIOpublisher config.php file");
					}
				}
			} else {
				$this->error("Memcache module not available on this server");
			}
		} elseif($this->cache_type == "xcache") {
			//xcache
			if(!function_exists('xcache_get')) {
				$this->error("Xcache module not available on this server");
			}
		} elseif($this->cache_type == "eaccelerator") {
			//eaccelerator
			if(!function_exists('eaccelerator_get')) {
				$this->error("eAccelerator module not available on this server");
			}
		}
	}
	
	//get from cache
	function get($key, $seconds=7200) {
		global $oiopub_set;
		if(!$key) return false;
		$key = $this->key_hash($key);
		$time_limit = time() - intval($seconds);
		if($this->cache_type == 'file') {
			//file cache
			if(!$this->cache_dir) return false;
			//rand cache flush
			$rand = rand(1, 500);
			if($rand == 250) {
				$this->flush(86400);
			}
			$file = $this->cache_dir . '/' . $key . '.html';
			if(@file_exists($file)) {
				$res = $seconds == 0 ? 0 : (@filemtime($file) - $time_limit);
				if($res >= 0) {
					$data = @file_get_contents($file);
					return $this->is_serialized($data);
				} else {
					$this->delete($file);
					return false;
				}
			}
		} elseif($this->cache_type == 'memcache') {
			//memcache
			if(!$this->cache_class) return false;
			if(!$data = $this->cache_class->get($key)) {
				return false;
			}
			return $this->is_serialized($data);
		} elseif($this->cache_type == 'xcache') {
			//xcache
			if(!$data = xcache_get($key)) {
				return false;
			}
			return $this->is_serialized($data);
		} elseif($this->cache_type == 'eaccelerator') {
			//eaccelerator
			if(!$data = eaccelerator_get($key)) {
				return false;
			}
			return $this->is_serialized($data);
		}
		return false;
	}

	//write to cache
	function write($key, $data, $seconds=7200) {
		global $oiopub_set;
		if(!$key) return false;
		$key = $this->key_hash($key);
		if(is_array($data) || is_object($data)) {
			$data = serialize($data);
		}
		if($this->cache_type == 'file') {
			//file cache
			if(!$this->cache_dir) return false;
			$file = $this->cache_dir . '/' . $key . '.html';
			if(!$file = @fopen($file, "wb")) {
				return false;
			}
			$length = strlen($data);
			@fwrite($file, $data, $length);
			@fclose($file);
			return true;
		} elseif($this->cache_type == 'memcache') {
			//memcache
			if(!$this->cache_class) return false;
			return $this->cache_class->set($key, $data, 0, $seconds);
		} elseif($this->cache_type == 'xcache') {
			//xcache
			return xcache_set($key, $data, $seconds);
		} elseif($this->cache_type == 'eaccelerator') {
			//eaccelerator
			return eaccelerator_put($key, $data, $seconds);
		}
		return false;
	}

	//alias for write
	function set($key, $data, $seconds=7200) {
		return $this->write($key, $data, $seconds);
	}
	
	//delete cache
	function delete($key) {
		global $oiopub_set;
		if(!$key) return false;
		$key = $this->key_hash($key);
		if($this->cache_type == 'file') {
			//file cache
			if(!$this->cache_dir) return false;
			if(empty($file)) {
				$file = $this->cache_dir . '/' . $key . '.html';
			}
			if(!@file_exists($file)) {
				return true;
			} else {
				return @unlink($file);
			}
		} elseif($this->cache_type == 'memcache') {
			//memcache
			if(!$this->cache_class) return false;
			return $this->cache_class->delete($key);
		} elseif($this->cache_type == 'xcache') {
			//xcache
			return xcache_unset($key);
		} elseif($this->cache_type == 'eaccelerator') {
			//eaccelerator
			return eaccelerator_rm($key);
		}
		return false;
	}
	
	//flush cache
	function flush($seconds=0, $type='', $dir='') {
		$ctype = !empty($type) ? $type : $this->cache_type;
		$cdir = !empty($dir) ? $dir : $this->cache_dir;
		if($ctype == 'file') {
			//file cache
			if(!$cdir) return false;
			$filenames = array();
			$delay = time() - intval($seconds);
			$handle = @opendir($cdir);
			while($file = @readdir($handle)) {
				if(strpos($file, ".html") !== false) {
					$file_path = $cdir . "/" . $file;
					$res = $seconds == 0 ? 0 : (@filemtime($file_path) - $delay);
					if($res <= 0) {
						@unlink($file_path);
					}
				}
			}
			@closedir($handle);
			return true;
		} elseif($ctype == 'memcache') {
			//memcache
			if($seconds == 0) {
				return $this->cache_class->flush();
			}
		} elseif($ctype == 'xcache') {
			//xcache
			if($seconds == 0) {
				return @xcache_clear_cache();
			}
		} elseif($ctype == 'eaccelerator') {
			//eaccelerator
			if($seconds == 0) {
				return @eaccelerator_clear();
			}
		}
		return false;
	}

}

?>