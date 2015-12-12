<?php

class oiopub_debug {

	var $log = array();

	function oiopub_debug($opts=array()) {
		//set options
		foreach((array) $opts as $k => $v) {
			$this->$k = $v;
		}
	}
	
	function init($ticks=false) {
		//set error level
		error_reporting(E_ALL);
		//set timer
		if(function_exists('microtime')) {
			define('OIOPUB_TIME_START', microtime(true));
		}
		//set mem usage
		if(function_exists('memory_get_usage')) {
			define('OIOPUB_MEM_START', memory_get_usage());
		}
		//set shutdown function
		register_shutdown_function(array( &$this, 'shutdown_handler' ));
		//register ticks?
		if($ticks && function_exists('register_tick_function')) {
			declare(ticks=1);
			register_tick_function(array( &$this, 'tick_handler' ));
		}
	}

	function tick_handler() {
		//set vars
		$args = array();
		$tmp = debug_backtrace();
		//chekc output
		while($tmp) {
			//external class?
			if(!isset($tmp[0]['class']) || $tmp[0]['class'] !== __CLASS__) {
				break;
			}
			//delete
			array_shift($tmp);
		}
		//continue?
		if(isset($tmp[0]) && $tmp[0]) {
			//backtrace has args?
			if(isset($tmp[0]['args']) && $tmp[0]['args']) {
				//loop through args
				foreach($tmp[0]['args'] as $arg) {
					if(is_array($arg)) {
						$args[] = 'Array';
					} elseif(is_object($arg)) {
						$args[] = get_class($arg);
					} else {
						$args[] = (string) $arg;
					}
				}
			}
			//add to log
			$this->log[] = array(
				'time' => defined('OIOPUB_TIME_START') ? number_format((microtime(true) - OIOPUB_TIME_START), 5) : 0,
				'memory' => defined('OIOPUB_MEM_START') ? number_format(memory_get_usage() - OIOPUB_MEM_START, 0, '.', ',') : 0,
				'backtrace' => "Caller: " . (isset($tmp[0]['file']) ? $tmp[0]['file'] . " (" . $tmp[0]['line'] . ")" : "n/a") . " | Calling: " . (isset($tmp[0]['class']) ? $tmp[0]['class'] . '::' : '') . $tmp[0]['function'] . "(" . ($args ? implode(', ', $args) : "") . ")",
			);
		}
	}

	function shutdown_handler() {
		global $oiopub_set;
		//hide errors
		error_reporting(0);
		//set vars
		$file = $oiopub_set->folder_dir . '/cache/DEBUG.txt';
		$data = 'Timestamp: ' . date('Y-m-d H:i:s', time()) . "\n";
		//add execution time?
		if(defined('OIOPUB_TIME_START')) {
			$data .= 'Execution time: ' . number_format((microtime(true) - OIOPUB_TIME_START), 5) . 's' . "\n";
		}
		//add memory time?
		if(defined('OIOPUB_MEM_START')) {
			$data .= 'Memory used: ' . number_format(memory_get_usage() - OIOPUB_MEM_START, 0, '.', ',') . "\n";
		}
		//loop through log
		foreach($this->log as $line) {
			$data .= $line['time'] . 's - ' . $line['memory'] . 'b - ' . str_replace(dirname($oiopub_set->folder_dir), '', $line['backtrace']) . "\n";
		}
		//write to file?
		if($this->log && $data && $fp = fopen($file, 'a')) {
			fwrite($fp, $data . "\n");
			fclose($fp);
		}
	}

}