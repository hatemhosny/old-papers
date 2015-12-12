<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

//hooks
class oiopub_hooks {

	var $data;

	//add hook action
	function add($name, $function, $priority=10) {
		$this->data[$name][$priority][serialize($function)] = $function;
	}

	//remove hook action
	function remove($name, $function, $priority=10) {
		unset($this->data[$name][$priority][serialize($function)]);
	}

	//fire hook
	function fire($name, $args='') {
		if(!isset($this->data[$name])) {
			return;
		} else {
			ksort($this->data[$name]);
		}
		$params = array();
		if(!empty($args)) {
			if(!is_array($args)) {
				$params[] = $args;
			} else {
				$params = $args;
			}
		}
		do {
			foreach((array) current($this->data[$name]) as $func) {
				if(!is_null($func)) {
					@call_user_func_array($func, $params);
				}
			}
		} while(next($this->data[$name]));
	}

}

?>