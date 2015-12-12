<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

//shared mem
class oiopub_shmem {

	var $tmp;
	var $size;
	var $sem;
	var $sem_use;
	var $shm;
	var $key;
  
	//init
	function oiopub_shmem($db=1, $size=1048576, $sem_use=1, $tmp='') {
		global $oiopub_set;
		$this->db = $db;
		$this->tmp = (empty($tmp) ? $oiopub_set->cache_dir : $tmp);
		$this->size = $size;
		$this->sem_use = $sem_use;
		return true;
	}

	//check if serialized
	function is_serialized($data) {
		if(!empty($data) && is_string($data)) {
			$data = trim($data);
			if(preg_match("/^(i|s|a|o|d)(.*)(;|{})/si", $data)) {
				return unserialize($data);
			}
		}
		return $data;
	}
	
	//sem lock
	function lock($lock=1) {
		if(!empty($lock)) {
			if($this->sem_use == 1) {
				if(!@sem_acquire($this->sem)) {
					return false;
				}
			}
		}
		return true;
	}
	
	//sem unlock
	function unlock($lock=1) {
		if(!empty($lock)) {
			if($this->sem_use == 1) {
				if(!@sem_release($this->sem)) {
					return false;
				}
			}
		}
		return true;
	}
	
	//get key
	function get_key($id) {
		$my_key = 'pcshm_' . $id;
		if(!@file_exists($this->tmp . DIRECTORY_SEPARATOR . $my_key)) {
			@touch($this->tmp . DIRECTORY_SEPARATOR . $my_key);
		}
		return ftok($this->tmp . DIRECTORY_SEPARATOR . $my_key, 'R');
	}

	//open cache
	function open($id) {
		if(!empty($id)) {
			$this->key = $this->get_key($id);
			if($this->sem_use == 1) {
				if(!$this->sem = @sem_get($this->key, 1, 0666)) {
					return false;
				}
			}
			if(!$this->shm = @shmop_open($this->key, 'w', 0644, 0)) {
				if(!$this->shm = @shmop_open($this->key, 'n', 0644, $this->size)) {
					return false;
				}
			}
		}
		return true;
	}
	
	//close cache
	function close($id) {
		if(!empty($id)) {
			@shmop_close($this->shm);
			if($this->sem_use == 1) {
				@sem_remove($this->sem);
			}
		}
		return true;
	}
	
	//read from cache
	function read($lock=1) {
		$this->lock($lock);
		$data = @trim(shmop_read($this->shm, 0, $this->size));
		$this->unlock($lock);
		return $this->is_serialized($data);
	}

	//write to cache
	function write($data, $lock=1) {
		$this->lock($lock);
		if(is_array($data)) {
			$data = trim(serialize($data));
		} else {
			$data = trim($data);
		}
		$data_size = strlen($data);
		if($data_size >= $this->size && $this->db == 1) {
			$res = $this->db_update();
		} else {
			$bytes = @shmop_write($this->shm, $data, 0);
			if($bytes != $data_size) {
				return false;
			}
			$res = $this->is_serialized($data);
		}
		$this->unlock($lock);
		return $res;
	}

	//delete cache
	function delete($id=0, $lock=1) {
		$this->open($id);
		$this->lock($lock);
		$res = @shmop_delete($this->shm);
		if($res) {
			$my_key = 'pcshm_' . $id;
			if(@file_exists($this->tmp . DIRECTORY_SEPARATOR . $my_key)) {
				@unlink($this->tmp . DIRECTORY_SEPARATOR . $my_key);
			}
		}
		$this->unlock($lock);
		$this->close($id);
		return $res;
	}
	
	//fetch cache
	function fetch($id) {
		if($this->open($id)) {
			if($data = $this->read()) {
				$this->close($id);
				return $data;
			}
		}
		return;
	}
	
	//add to cache
	function add($id, $data) {
		if($this->open($id)) {
			$write = $this->read();
			$counter = (!empty($write) ? count($write) : 0);
			if(is_array($data)) {
				if(!is_array($write)) {
					$write = array();
				}
				foreach($data as $d) {
					$write[$counter] = $d;
					$counter++;
				}
			} else {
				$write .= $data;
			}
			unset($data, $counter);
			$result = $this->write($write);
			$this->close($id);
			return $result;
		}
		return false;
	}
	
	//overwrite cache
	function replace($id, $data) {
		if($this->open($id)) {
			if($result = $this->write($data)) {
				$this->close($id);
				return $result;
			}
		}
		return false;
	}
	
	//mem to db
	function mem_to_db($id) {
		if($this->open($id)) {
			return $this->db_update();
		}
		return false;
	}
	
	//update database
	function db_update() { }

}

?>