<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

//help class
class oiopub_help {

	var $page;
	var $module;
	var $show;

	//init
	function oiopub_help() {
		$this->page = oiopub_var('page', 'get');
		$this->opt = oiopub_var('opt', 'get');
		$this->module = oiopub_var('module', 'get');
		$this->show = oiopub_var('show', 'get');
	}
	
	//$_GET show?
	function show() {
		$this->db_tables();
		$this->output_funcs();
		$this->get_modules();
	}
	
	//header
	function header() {
		echo "<body style='padding:0px; margin:0px; font:13px \"Lucida Grande\", \"Lucida Sans Unicode\", Tahoma, Verdana, sans-serif;'>\n";
		echo "<div style='width:580px; line-height:17px;'>\n";
	}
	
	//footer
	function footer() {
		echo "</div>\n";
		echo "</body>\n";
		exit();
	}

	//show db tables
	function db_tables() {
		global $oiopub_set, $oiopub_tables;
		if($this->show == "tables") {
			$this->header();
			echo "<h3>Manual Database Installation</h3>";
			echo "Run the queries below on your database to install the tables. The easiest way to do this is through a database manager such as phpMyAdmin.\n";		
			//get core
			include_once($oiopub_set->folder_dir . "/include/install.php");
			//core tables
			$core = new oiopub_install();
			$core->install_db();
			//output tables
			if(oiopub_count($oiopub_tables) > 0) {
				foreach($oiopub_tables as $table) {
					echo "<br /><br />\n";
					echo str_replace("\n", "<br />\n", $table) . "\n";
				}
			} else {
				echo "<br /><br />\n";
				echo "Sorry, no database tables have been found!\n";
			}
			$this->footer();
		}
	}

	//show output funcs
	function output_funcs() {
		global $oiopub_set;
		if($this->show == "output") {
			$this->header();
			echo "<script type='text/javascript' src='" . $oiopub_set->plugin_url . "/libs/misc/oiopub.js'></script>\n";
			echo "<style type='text/css'>\n";
			echo ".php_code { margin:15px 0 5px 0; color:#800000; font-weight:bold; }\n";
			echo ".js_code { margin:15px 0 5px 0; color:#CC1100; font-weight:bold; }\n";
			echo "</style>\n";
			echo "<h3>Displaying ads, using OIO's ad code</h3>";
			echo "Listed below is the code required to integrate and display different types of ads.\n";
			echo "<br />\n";
			if($oiopub_set->platform == 'standalone') {
				echo "<br /><br />\n";
				echo "<b>* <a href=\"javascript://\" onclick=\"togglediv('integrate1');\">Click here to integrate OIOpublisher with a 3rd party website</a></b>\n";
				echo "<div id='integrate1' style='display:none';>\n";
				echo "<div style='padding:10px; margin-top:20px; border:1px solid #999; background:#DBFEF8;'>\n";
				echo "<div style='margin-bottom:5px;'><b>(1) PHP Integration Code:</b></div>\n";
				echo "If you want to integrate OIOpublisher into an existing website, you'll need to add a line of php code to your site. You'll then be able to use the php output functions to show ads (otherwise, you'll need to use javascript)\n";
				echo "<div class='php_code'>" . htmlspecialchars("<?php include_once('" . $oiopub_set->folder_dir . "/index.php'); ?>") . "</div>\n";
				echo "</div>\n";
				echo "<div style='padding:10px; margin-top:20px; border:1px solid #999; background:#DBFEF8;'>\n";
				echo "<div style='margin-bottom:5px;'><b>(2) Theme Hooks PHP Code:</b></div>\n";
				echo "To complete php integration with your website, you can add header and footer hooks code to your site. This will save you having to make manual code edits to your website template files in the future.\n";
				echo "<div class='php_code'>" . htmlspecialchars("<?php if(function_exists('oiopub_header')) oiopub_header(); ?>") . "</div>\n";
				echo "<i>put this above the " . htmlspecialchars("</head>") . " tag</i>\n";
				echo "<div class='php_code'>" . htmlspecialchars("<?php if(function_exists('oiopub_footer')) oiopub_footer(); ?>") . "</div>\n";
				echo "<i>put this above the " . htmlspecialchars("</body>") . " tag</i>\n";
				echo "</div>\n";
				echo "</div>\n";
			} elseif($oiopub_set->platform == 'wordpress') {
				echo "<br /><br />\n";
				echo "<b>* <a href=\"javascript://\" onclick=\"togglediv('integrate1');\">Click here to make sure you have Wordpress template hooks in place</a></b>\n";
				echo "<div id='integrate1' style='display:none';>\n";
				echo "<div style='padding:10px; margin-top:20px; border:1px solid #999; background:#DBFEF8;'>\n";
				echo "<div style='margin-bottom:5px;'><b>(1) WP Head Hook:</b></div>\n";
				echo "Make sure your header.php theme file contains the code below:\n";
				echo "<div class='php_code'>" . htmlspecialchars("<?php wp_head(); ?>") . "</div>\n";
				echo "<i>this should be just above the " . htmlspecialchars("</head>") . " tag</i>\n";
				echo "</div>\n";
				echo "<div style='padding:10px; margin-top:20px; border:1px solid #999; background:#DBFEF8;'>\n";
				echo "<div style='margin-bottom:5px;'><b>(2) WP Footer Hook:</b></div>\n";
				echo "Make sure your footer.php theme file contains the code below:\n";
				echo "<div class='php_code'>" . htmlspecialchars("<?php wp_footer(); ?>") . "</div>\n";
				echo "<i>this should be just above the " . htmlspecialchars("</body>") . " tag</i>\n";
				echo "</div>\n";
				echo "</div>\n";
			}
			if(oiopub_links) {
				echo "<br /><br />\n";
				echo "<b>* <a href=\"javascript://\" onclick=\"togglediv('integrate2');\">Click here to include text ad code on your website</a></b>\n";
				echo "<div id='integrate2' style='display:none';>\n";
				echo "<div style='padding:10px; margin-top:20px; border:1px solid #999; background:#DBFEF8;'>\n";
				echo "<div style='margin-bottom:5px;'><b>(1) Using Javascript:</b></div>\n";
				echo "Place this javascript code on any website you want to display ads on. Remember to replace 'X' with the zone number you want to show.\n";
				echo "<div class='js_code'>" . htmlspecialchars("<script type='text/javascript' src='" . $oiopub_set->plugin_url . "/js.php#type=link&align=center&zone=X'></script>") . "</div>\n";			
				echo "</div>\n";
				echo "<div style='padding:10px; margin-top:20px; border:1px solid #999; background:#DBFEF8;'>\n";
				echo "<div style='margin-bottom:5px;'><b>(2) Using PHP:</b></div>\n";
				if($oiopub_set->platform == 'standalone') {
					echo "You must have completed the 3rd party website integration steps to use php output code. Remember to replace 'X' with the zone number you want to show.\n";
				} else {
					echo "Add this php code to your website. Remember to replace 'X' with the zone number you want to show.\n";
				}
				echo "<div class='php_code'>" . htmlspecialchars("<?php if(function_exists('oiopub_link_zone')) oiopub_link_zone(X, 'center'); ?>") . "</div>\n";
				echo "</div>\n";
				echo "</div>\n";
			}
			if(oiopub_banners) {
				echo "<br /><br />\n";
				echo "<b>* <a href=\"javascript://\" onclick=\"togglediv('integrate3');\">Click here to display banner ad code on your website</a></b>\n";
				echo "<div id='integrate3' style='display:none';>\n";
				echo "<div style='padding:10px; margin-top:20px; border:1px solid #999; background:#DBFEF8;'>\n";
				echo "<div style='margin-bottom:5px;'><b>(1) Using Javascript:</b></div>\n";
				echo "Place this javascript code on any website you want to display ads on. Remember to replace 'X' with the zone number you want to show.\n";
				echo "<div class='js_code'>" . htmlspecialchars("<script type='text/javascript' src='" . $oiopub_set->plugin_url . "/js.php#type=banner&align=center&zone=X'></script>") . "</div>\n";			
				echo "</div>\n";
				echo "<div style='padding:10px; margin-top:20px; border:1px solid #999; background:#DBFEF8;'>\n";
				echo "<div style='margin-bottom:5px;'><b>(2) Using PHP:</b></div>\n";
				if($oiopub_set->platform == 'standalone') {
					echo "You must have completed the 3rd party website integration steps to use php output code. Remember to replace 'X' with the zone number you want to show.\n";
				} else {
					echo "Add this php code to your website. Remember to replace 'X' with the zone number you want to show.\n";
				}
				echo "<div class='php_code'>" . htmlspecialchars("<?php if(function_exists('oiopub_banner_zone')) oiopub_banner_zone(X, 'center'); ?>") . "</div>\n";
				echo "</div>\n";
				echo "</div>\n";
			}
			echo "<br /><br />\n";
			echo "<b>* <a href=\"javascript://\" onclick=\"togglediv('integrate4');\">Click here to include other useful output functions</a></b>\n";
			echo "<div id='integrate4' style='display:none';>\n";
			echo "<div style='padding:10px; margin-top:20px; border:1px solid #999; background:#DBFEF8;'>\n";
			echo "<div style='margin-bottom:5px;'><b>(1) Showing Available Ad Spots:</b></div>\n";
			echo "This php code will display available ad slots on your website.\n";
			echo "<div class='php_code'>" . htmlspecialchars("<?php if(function_exists('oiopub_ad_slots')) oiopub_ad_slots(); ?>") . "</div>\n";			
			echo "</div>\n";
			echo "<div style='padding:10px; margin-top:20px; border:1px solid #999; background:#DBFEF8;'>\n";
			echo "<div style='margin-bottom:5px;'><b>(2) Showing Ad Badge:</b></div>\n";
			echo "This php code will display a badge on your site where people can purchase advertising.\n";
			echo "<div class='php_code'>" . htmlspecialchars("<?php if(function_exists('oiopub_ad_badge')) oiopub_ad_badge(); ?>") . "</div>\n";
			echo "</div>\n";
			echo "</div>\n";
			if($oiopub_set->platform != 'standalone') {
				echo "<br /><br />\n";
				echo "<b>* <a href=\"javascript://\" onclick=\"togglediv('integrate5');\">Click here to integrate OIOpublisher with a 3rd party website</a></b>\n";
				echo "<div id='integrate5' style='display:none';>\n";
				echo "<div style='padding:10px; margin-top:20px; border:1px solid #999; background:#DBFEF8;'>\n";
				echo "<div style='margin-bottom:5px;'><b>(1) PHP Integration Code:</b></div>\n";
				echo "If you want to integrate OIOpublisher into an existing website, you'll need to add a line of php code to your site. You'll then be able to use the php output functions to show ads (otherwise, you'll need to use javascript)\n";
				echo "<div class='php_code'>" . htmlspecialchars("<?php include_once('" . $oiopub_set->folder_dir . "/index.php'); ?>") . "</div>\n";
				echo "</div>\n";
				echo "<div style='padding:10px; margin-top:20px; border:1px solid #999; background:#DBFEF8;'>\n";
				echo "<div style='margin-bottom:5px;'><b>(2) Theme Hooks PHP Code:</b></div>\n";
				echo "To complete php integration with your website, you can add header and footer hooks code to your site. This will save you having to make manual code edits to your website template files in the future.\n";
				echo "<div class='php_code'>" . htmlspecialchars("<?php if(function_exists('oiopub_header')) oiopub_header(); ?>") . "</div>\n";
				echo "<i>put this above the " . htmlspecialchars("</head>") . " tag</i>\n";
				echo "<div class='php_code'>" . htmlspecialchars("<?php if(function_exists('oiopub_footer')) oiopub_footer(); ?>") . "</div>\n";
				echo "<i>put this above the " . htmlspecialchars("</body>") . " tag</i>\n";
				echo "</div>\n";
				echo "</div>\n";
				echo "<br /><br /><br />\n";
				echo "<i>NB: Inline Ads & Paid Reviews do not require you to add any extra code to your website.</i>\n";
			}
			$this->footer();
		}
	}
	
	//get modules
	function get_modules() {
		global $oiopub_hook;
		if(!empty($this->module)) {
			$this->header();
			$oiopub_hook->fire('help_desk');
			$this->footer();
		}
	}
	
}

?>