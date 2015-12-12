<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();


class oiopub_translate {

	protected $lang;
	protected $lang_dir = '';

	protected $dirs = array();
	protected $values = array();
	protected $table = array();

	protected $cache = null;
	protected $cache_timeout = 7200;

	//constructor
	public function __construct(array $options=array()) {
		//set options
		foreach($options as $key=>$val) {
			if(property_exists($this, $key)) {
				$this->$key = $val;
			}
		}
	}

	//cache object
	public function cache() {
		if(func_num_args() > 0 && !$this->cache) {
			$this->cache = func_get_arg(0);
			if(func_num_args() > 1) {
				$this->cache_timeout = (int) func_get_arg(1);
			}
			return $this;
		}
		return $this->cache;
	}

	//lang property
	public function lang() {
		if(func_num_args() > 0) {
			$this->lang = func_get_arg(0);
			return $this;
		}
		return $this->lang;
	}

	//get translated text
	public function get($text, array $params=array(), $lang=null) {
		//set lang
		$lang = $lang ? $lang : $this->lang;
		//lazy loading
		if($lang && !isset($this->table[$lang])) {
			$this->load_lang($lang);
		}
		//search for translation
		if($lang && isset($this->table[$lang][$text]) && $this->table[$lang][$text]) {
			$text = $this->table[$lang][$text];
		}
		//dynamic params
		foreach($params as $key=>$val) {
			//translate param?
			if($lang && isset($this->table[$lang][$val]) && $this->table[$lang][$val]) {
				$val = $this->table[$lang][$val];
			}
			//format key
			$key = is_numeric($key) ? $key + 1 : $key;
			//replace param
			$text = preg_replace('/%s/', $val, $text, 1);
			$text = preg_replace('/%' . $key . '/', $val, $text);
		}
		//return result
		return $text;
	}
	
	//add translation data
	public function add_translation($lang, $data) {
		//too late to add?
		if(isset($this->table[$lang]) || empty($data)) {
			return false;
		}
		//set arrays
		$this->dirs[$lang] = isset($this->dirs[$lang]) ? $this->dirs[$lang] : array();
		$this->values[$lang] = isset($this->values[$lang]) ? $this->values[$lang] : array();
		//select method
		if(is_array($data)) {
			//add values
			$this->values[$lang] = array_merge($this->values[$lang], $data);
		} elseif(is_file($data)) {
			//add file
			$data = (array) require($data);
			$this->values[$lang] = array_merge($this->values[$lang], $data);
		} else {
			//add dir
			$this->dirs[$lang][] = (string) $data;
		}
	}

	//load language files
	protected function load_lang($lang) {
		//check cache
		if($this->read_cache($lang)) {
			return;
		}
		//set vars
		$values = array();
		$lang_exp = explode("_", $lang);
		//array exists?
		if(!isset($this->dirs[$lang])) {
			$this->dirs[$lang] = array();
		}
		//add primary dir?
		if($this->lang_dir) {
			array_unshift($this->dirs[$lang], $this->lang_dir);
		}
		//loop through directories
		foreach($this->dirs[$lang] as $lang_dir) {
			//language files search
			if(!$files = glob($lang_dir . "/" . $lang_exp[0] . "*")) {
				continue;
			}
			//localised files search
			if(isset($lang_exp[1])) {
				if($locales = glob($lang_dir . "/" . $lang . "*")) {
					$files = array_merge($files, $locales);
				}
			}
			//loop through files
			foreach($files as $file) {
				//load file
				$v = (array) require($file);
				//add to array
				$values = array_merge($values, $v);
			}
		}
		//merge general values
		if(isset($this->values[$lang_exp[0]])) {
			$values = array_merge($values, $this->values[$lang_exp[0]]);
		}
		//merge localised values
		if(isset($lang_exp[1]) && isset($this->values[$lang])) {
			$values = array_merge($values, $this->values[$lang]);
		}
		//update cache
		$this->write_cache($lang, $values);
	}
	
	//get from cache
	protected function read_cache($lang) {
		//external cache?
		if($this->cache && $res = $this->cache->get("lang_$lang")) {
			$this->lang_cache[$lang] = $res;
			$this->clean_temp($lang);
			return true;
		}
		return false;
	}
	
	//update cache
	protected function write_cache($lang, array $values) {
		//set local cache
		$this->table[$lang] = $values;
		//remove temp vars
		$this->clean_temp($lang);
		//external cache?
		if($this->cache) {
			$this->cache->set("lang_$lang", $values, $this->cache_timeout);
		}
	}
	
	//clean temp vars
	protected function clean_temp($lang) {
		//remove dirs
		if(isset($this->dirs[$lang])) {
			$this->dirs[$lang] = array();
		}
		//remove values
		if(isset($this->values[$lang])) {
			$this->values[$lang] = array();
		}
	}

}