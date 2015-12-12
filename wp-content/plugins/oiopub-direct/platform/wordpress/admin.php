<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

/* WORDPRESS ADMIN CLASS */

class oiopub_admin_wp extends oiopub_admin {

	var $role = "manage_options";

	//init
	function oiopub_admin_wp() {
		global $oiopub_set;
		$this->init();
		$this->actions();
		//demo mode?
		if($oiopub_set->demo) {
			$this->role = "read";
		}
	}
	
	//actions
	function actions() {
		global $oiopub_alerts;
		add_action('admin_menu', array(&$this, 'menu'));
		add_action('admin_footer', array(&$oiopub_alerts, 'display'));
		add_action('wp_dashboard_setup', array(&$this, 'dashboard_widget'));
	}
	
	//dashboard widget
	function dashboard_widget() {
		if(function_exists('wp_add_dashboard_widget') && oiopub_auth_check()) {
			wp_add_dashboard_widget('oiopublisher_alerts', 'OIOpublisher Alerts', array(&$this, 'dashboard_widget_output'));	
		}
	}
	
	//dashboard widget output
	function dashboard_widget_output() {
		global $oiopub_alerts;
		$oiopub_alerts->purchases();
	}

	//admin menu
	function menu() {
		global $oiopub_set, $oiopub_module;
		//base file
		$file = $oiopub_set->folder_dir."/wp.php";
		//add core page
		add_menu_page('OIO Ad Manager', 'OIO Ad Manager', $this->role, $file, array(&$this, 'overview'));
		//add sub pages
		foreach($this->menu_pages() as $page) {
			$f = $page['file'] ? $page['file'] : $file;
			add_submenu_page($file, $page['text'], $page['text'], $this->role, $f, array(&$this, $page['method']));
		}
	}
	
	//get role
	function get_role() {
		return $this->role;
	}

}

?>