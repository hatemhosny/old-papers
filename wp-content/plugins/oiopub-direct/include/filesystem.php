<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

//filesystem class
class oiopub_filesystem {

	var $file_system;
	var $temp_dir;

	//init function
	function oiopub_filesystem($temp_dir='') {
		global $oiopub_set;
		if(function_exists('getmyuid')) {
			$success = false;
			$this->file_system = "temp";
			$rand_string = oiopub_rand(8);
			$test_file = $oiopub_set->folder_dir . "/" . $rand_string . ".php";
			if($this->make_file($test_file, "test", "w+")) {
				if($this->get_owner($test_file) == getmyuid()) {
					$success = true;
				}
			}
			if($success === true) {
				$this->file_system = "direct";
				$this->temp_dir = $temp_dir;
			} else {
				$this->file_system = "";
				$this->temp_dir = "";
			}
			$this->delete_file($test_file);
		}
	}
	
	//get file permissions
	function get_perms($file) {
		if(!$this->file_system) {
			return false;
		}
		if(function_exists('fileperms')) {
			if($perms = @fileperms($file)) {
				return substr(sprintf('%o', $perms), -4);
			}
		}
		return false;	
	}
	
	//get file owner
	function get_owner($file) {
		if(!$this->file_system) {
			return false;
		}
		if(function_exists('fileowner')) {
			return @fileowner($file);
		}
		return false;
	}
	
	//chmod file / dir
	function chmod($file, $perms) {
		if(!$this->file_system) {
			return false;
		}
		return @chmod($file, $perms);
	}
	
	//rename file / dir
	function rename($old, $new) {
		if(!$this->file_system) {
			return false;
		}
		return @rename($old, $new);
	}
	
	//make dir
	function make_file($file, $data, $type="w+") {
		if(!$this->file_system) {
			return false;
		}
		if($fp = @fopen($file, $type)) {
			@fwrite($fp, $data);
			@fclose($fp);
			return true;
		}
		return false;
	}
	
	//copy file
	function copy_file($source, $dest) {
		if(!$this->file_system) {
			return false;
		}
		return @copy($source, $dest);
	}
	
	//delete file
	function delete_file($file) {
		if(!$this->file_system) {
			return false;
		}
		return @unlink($file);
	}
	
	//make dir
	function make_dir($dir, $perms) {
		if(!$this->file_system) {
			return false;
		}
		return @mkdir($dir, $perms);
	}
	
	//copy dir
	function copy_dir($source, $dest) {
		if(!$this->file_system) {
			return false;
		}
		if(!@is_dir($dest)) {
			$this->make_dir($dest, 0755);
		}
		if(!$dh = @opendir($source)) {
			return false;
		}
		while(($file = @readdir($dh)) !== false) {
			if($file == '.' || $file == '..') {
				continue;
			}
			$this->copy_file($source . '/' . $file, $dest . '/' . $file);
		}
		@closedir($dh);
		return true;
	}

	//delete dir
	function delete_dir($dir, $DeleteMe=TRUE) {
		if(!$this->file_system) {
			return false;
		}
		if(!$dh = @opendir($dir)) {
			return false;
		}
		while(($file = @readdir($dh)) !== false) {
			if($file == '.' || $file == '..') {
				continue;
			}
			if(!$this->delete_file($dir . '/' . $file)) {
				$this->delete_dir($dir . '/' . $file, true);
			}
		}
		@closedir($dh);
		if($DeleteMe) {
			@rmdir($dir);
		}
		return true;
	}
	
	//create zip file
	function create_archive($zip_file, $dir_archive) {
		global $oiopub_set;
		if(!$this->file_system) {
			return false;
		}
		if(!defined('PCLZIP_TEMPORARY_DIR')) {
			define('PCLZIP_TEMPORARY_DIR', $this->temp_dir);
		}
		include_once($oiopub_set->folder_dir . "/include/pclzip.php");
		$archive = new PclZip($zip_file);
		return $archive->create($dir_archive);
	}
	
	//extract archive
	function extract_archive($zip_file) {
		global $oiopub_set;
		if(!$this->file_system) {
			return false;
		}
		if(!defined('PCLZIP_TEMPORARY_DIR')) {
			define('PCLZIP_TEMPORARY_DIR', $this->temp_dir);
		}
		include_once($oiopub_set->folder_dir . "/include/pclzip.php");
		$archive = new PclZip($zip_file);
		return $archive->extract(PCLZIP_OPT_EXTRACT_AS_STRING);
	}
	
	//write archive
	function write_archive($data, $dir_to) {
		if(!empty($data)) {
			foreach($data as $file) {
				$path = explode('/', $file['filename']);
				$tmppath = '';
				for($j=0; $j < count($path)-1; $j++) {
					$tmppath .= '/' . $path[$j];
					if(!@is_dir($dir_to . $tmppath)) {
						if(!$this->make_dir($dir_to . $tmppath, 0755)) {
							return false;
						}
					}
				}
				if(!$file['folder']) {
					if(!$this->make_file($dir_to . '/' . $file['filename'], $file['content'], "w+")) {
						return false;
					}
					$this->chmod($dir_to . '/' . $file['filename'], 0644);
				}
			}
			return true;
		}
		return false;
	}

}

?>