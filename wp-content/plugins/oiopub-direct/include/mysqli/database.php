<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

//database
class oiopub_db {

	var $dbh;
	var $connect;
	var $db_args;
	var $cache;
	var $query_num = 0;
	var $num_rows = 0;
	var $insert_id = 0;
	var $rows_affected = 0;
	var $errors = array();

	var $default_charset = '';
	var $check_charset = false;

	var $log_file = '{base_dir}/errors/sql.txt';

	//init
	function oiopub_db($db_args=array(), $dbh='', $cache_obj='') {
		if(empty($dbh) || $dbh === false) {
			$this->connect = false;
			$this->dbh = "";
		} else {
			$this->connect = true;
			$this->dbh = $dbh;
		}
		$this->db_args = $db_args;
		$this->cache = $cache_obj;
	}
	
	//collect errors
	function error($error, $sql='') {
		//add to array
		$this->errors[] = $error;
		//log error?
		if($this->log_file) {
			//format file name
			$file = str_replace("\\", "/", dirname(dirname(__FILE__)));
			$file = str_replace('{base_dir}', $file, $this->log_file);
			//can save?
			if(function_exists('file_put_contents')) {
				@file_put_contents($file, "SQL: " . $sql . "\nError: " . $error . "\n\n", FILE_APPEND | LOCK_EX);
			}
		}
	}
	
	//connect
	function connect($db_host, $db_user, $db_pass, $db_name) {
		//check for port
		$port = null;
		$socket = null;
		$port_or_socket = strstr($db_host, ':');
		if(!empty($port_or_socket)) {
			$db_host = substr($db_host, 0, strpos($db_host, ':'));
			$port_or_socket = substr( $port_or_socket, 1 );
			if(strpos($port_or_socket, '/') !== 0) {
				$port = intval($port_or_socket);
				$maybe_socket = strstr($port_or_socket, ':');
				if(!empty($maybe_socket)) {
					$socket = substr($maybe_socket, 1);
				}
			} else {
				$socket = $port_or_socket;
			}
		}
		//connect
		$this->dbh = @mysqli_connect($db_host, $db_user, $db_pass, null, $port, $socket);
		//has error?
		if($error = @mysqli_connect_error()) {
			$this->error($error, 'connect');
			return false;
		}
		//select
		@mysqli_select_db($this->dbh, $db_name);
		//has error?
		if($error = @mysqli_error($this->dbh)) {
			$this->error($error, 'select db');
			return false;
		}
		//connected
		$this->default_charset();
		$this->connect = true;
		return true;
	}

	//basic query
	function query($sql) {
		if(!$this->connect) {
			$this->connect($this->db_args['db_host'], $this->db_args['db_user'], $this->db_args['db_pass'], $this->db_args['db_name']);
		}
		$res = @mysqli_query($this->dbh, $sql);
		if($error = @mysqli_error($this->dbh)) {
			$this->error($error, $sql);
		}
		if(!$res) {
			$this->insert_id = $this->rows_affected = $this->num_rows = 0;
			return false;
		}
		$this->query_num++;
		$this->insert_id = (int) @mysqli_insert_id($this->dbh);
		$this->rows_affected = (int) @mysqli_affected_rows($this->dbh);
		$this->num_rows = isset($res->num_rows) ? (int) $res->num_rows : 0;
		return $res;
	}

	//escape
	function escape($str) {
		return mysqli_real_escape_string($this->dbh, $str);
	}

	//get error
	function LastError() {
		$count = count($this->errors);
		return $count > 0 ? $this->errors[$count-1] : '';
	}

	//get single value
	function GetOne($sql=null) {
		if(!$sql) return false;
		if($res = $this->query($sql)) {
			$row = mysqli_fetch_row($res);
			mysqli_free_result($res);
			return $row[0];
		}
		return false;
	}
	
	//cache single value
	function CacheGetOne($sql=null, $seconds=7200) {
		if(!$sql || !$this->cache) return false;
		if($this->cache) {
			$res = $this->cache->get($sql, $seconds);
		}
		if(empty($res) && !is_array($res)) {
			$res = $this->GetOne($sql);
			if($this->cache) {
				$this->cache->write($sql, $res, $seconds);
			}
		} else {
			$this->num_rows = 1;
		}
		return $res;
	}

	//get single row
	function GetRow($sql=null) {
		if(!$sql) return false;
		if($res = $this->query($sql)) {
			$row = mysqli_fetch_object($res);
			mysqli_free_result($res);
			return $row;
		}
		return false;
	}
	
	//cache single row
	function CacheGetRow($sql=null, $seconds=7200) {
		if(!$sql) return false;
		if($this->cache) {
			$res = $this->cache->get($sql, $seconds);
		}
		if(empty($res) && !is_array($res)) {
			$res = $this->GetRow($sql);
			if($this->cache) {
				$this->cache->write($sql, $res, $seconds);
			}
		} else {
			$this->num_rows = 1;
		}
		return $res;
	}

	//get all rows
	function GetAll($sql=null) {
		if(!$sql) return false;
		if($res = $this->query($sql)) {
			$obj = array();
			while($row = mysqli_fetch_object($res)) {
				$obj[] = $row;
			}
			mysqli_free_result($res);
			return $obj;
		}	
	}
	
	//cache all rows
	function CacheGetAll($sql=null, $seconds=7200) {
		if(!$sql) return false;
		if($this->cache) {
			$res = $this->cache->get($sql, $seconds);
		}
		if(empty($res) && !is_array($res)) {
			$res = $this->GetAll($sql);
			if($this->cache) {
				$this->cache->write($sql, $res, $seconds);
			}
		} else {
			$this->num_rows = empty($res) ? 0 : count($res);
		}
		return $res;
	}
	
	//cache flush
	function CacheFlush($sql=null) {
		if(!$sql) return false;
		if($this->cache) {
			return $this->cache->delete($sql);
		}
		return false;
	}

	//get default charset
	function default_charset() {
		global $oiopub_set;
		//charset check?
		if(!$this->check_charset) {
			//update flag
			$this->check_charset = true;
			//query db
			if($table = $this->query("SHOW CREATE TABLE " . $oiopub_set->prefix . "oiopub_purchases")) {
				$table = @mysqli_fetch_array($table, MYSQLI_NUM);
			}
			//is utf8?
			if(!$table || strpos($table[1], "DEFAULT CHARSET=utf8") !== false) {
				$this->default_charset = ' DEFAULT CHARACTER SET utf8';
				$this->query("SET character_set_results='utf8', character_set_client='utf8', character_set_connection='utf8', character_set_database='utf8', character_set_server='utf8'");
			}
		}
		//return charset
		return $this->default_charset;
	}

}

?>