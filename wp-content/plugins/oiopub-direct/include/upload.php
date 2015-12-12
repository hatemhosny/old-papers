<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();


class oiopub_upload {

	var $name;
	var $size;
	var $temp_name;
	var $upload_dir;
	var $valid_exts = array();
	var $max_size;
	var $max_width;
	var $max_height;

	var $is_image = true;

	function upload() {
		//check upload dir
		if(!$this->check_dir()) {
			return false;
		}
		//check extensions
		if(!$this->check_ext()) {
			return false;
		}
		//check file size
		if(!$this->check_size()) {
			return false;
		}
		//check image
		if(!$this->check_image()) {
			return false;
		}
		//check uploaded
		if(!$this->check_uploaded()) {
			return false;
		}
		//unique name
		$this->unique_file();
		//tidy up
		move_uploaded_file($this->temp_name, $this->upload_dir . $this->name);
		@chmod($this->upload_dir . $this->name, 0644);
		//success!
		return true;
	}

	function check_dir() {
		//dir set?
		if(!$this->upload_dir) {
			return false;
		}
		//set trailing slash
		if(substr($this->upload_dir, -1) != "/") {
			$this->upload_dir .= '/';
		}
		//writable dir?
		if(!is_dir($this->upload_dir) || !is_writable($this->upload_dir)) {
			return false;
		}
		//success
		return true;
	}

	function check_ext() {
		//set vars
		$parts = pathinfo($this->name);
		$ext = strtolower($parts['extension']);
		//has ext?
		if(!$this->name || !$ext) {
			return false;
		}
		//valid extension?
		if($this->valid_exts && !in_array($ext, $this->valid_exts)) {
			return false;
		}
		//success
		return true;
	}

	function check_size() {
		//over max size?
		if($this->max_size > 0 && $this->max_size < $this->size) {
			return false;
		}
		//success
		return true;
	}

	function check_image() {
		//is image?
		if(!$this->is_image) {
			return true;
		}
		//get image size
		if($size = @getimagesize($this->temp_name)) {
			$width = $size[0];
			$height = $size[1];
		}
		//valid image?
		if($width <= 0 || $height <= 0) {
			return false;
		}
		//valid width?
		if($this->max_width > 0 && $this->max_width < $width) {
			return false;
		}
		//valid width?
		if($this->max_height > 0 && $this->max_height < $height) {
			return false;
		}
		//success
		return true;
	}

	function check_uploaded() {
		//has been uploaded?
		return is_uploaded_file($this->temp_name) ? true : false;
	}

	function unique_file() {
		//set vars
		$file = $this->upload_dir . $this->name;
		//already exists?
		if(file_exists($file)) {
			$this->name = mt_rand() . "-" . $this->name;
			$this->unique_file();
		}
	}

}

?>