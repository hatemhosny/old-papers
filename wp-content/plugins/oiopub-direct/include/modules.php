<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

//modules
class oiopub_modules {

	var $exclude = array( "admanager", "conversation", "geolocation", "demographics" );
	
	//init
	function oiopub_modules() {
		global $oiopub_set, $oiopub_plugin, $oiopub_version;
		if(empty($oiopub_set->version) || $oiopub_version > $oiopub_set->version) {
			return;
		}
		$filenames = array();
		$this->modcount = array();
		$handle = @opendir($oiopub_set->modules_dir);
		while($file = @readdir($handle)) {
			$file = trim($file);
			if(in_array($file, $this->exclude)) {
				continue;
			}
			if(@file_exists($oiopub_set->modules_dir . "/" . $file . "/oiopub-load.php")) {
				if(!empty($file) && $file != "." && $file != ".." && $file != ".htaccess") {
					if(!$this->load_now($file)) {
						continue;
					}
					include_once($oiopub_set->modules_dir . "/" . $file . "/oiopub-load.php");
					if(!empty($oio_version) && !empty($oio_name)) {
						if(@is_dir($oiopub_set->modules_dir . "/" . $file . "/templates/")) {
							$templates = 1;
						} else {
							$templates = 0;
						}
						if(isset($oiopub_plugin[$oio_module])) {
							$this->modcount[] = array($file, $oio_enabled, $oio_version, $oio_name, $templates, $oio_module, $oio_menu);
							$this->$file = 1;
						} else {
							$this->modcount[] = array($file, -1, $oio_version, $oio_name, $templates, $oio_module, $oio_menu);
							$this->$file = 0;
						}
					}
					unset($file, $oio_enabled, $oio_version, $oio_name, $templates, $oio_module, $oio_menu);
				}
			}
		}
		@closedir($handle);
	}
	
	//conditional loading
	function load_now($file) {
		global $oiopub_set;
		//admin area?
		if(oiopub_is_admin()) {
			return true;
		}
		//enable set to zero?
		if(isset($oiopub_set->{$file}['enable']) && $oiopub_set->{$file}['enable'] != 1) {
			return false;
		}
		//enabled set to zero?
		if(isset($oiopub_set->{$file}['enabled']) && $oiopub_set->{$file}['enabled'] != 1) {
			return false;
		}
		return true;
	}

}

?>