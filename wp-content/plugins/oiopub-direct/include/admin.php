<?php

/*
Copyright (C) 2007  Simon Emery

This file is part of OIOpublisher Direct.
*/

//admin
class oiopub_admin {

	//wrapper
	function oiopub_admin() {
		$this->init();
	}

	//init
	function init() {
		$this->session();
		$this->csrf();
		$this->includes();
		$this->classes();
		$this->help();
	}
	
	//session
	function session() {
		oiopub_session_start();
	}
	
	//csrf
	function csrf() {
		if(defined('OIOPUB_ADMIN') || (isset($_GET['page']) && strpos($_GET['page'], "oiopub") !== false)) {
			oiopub_csrf_token();
		}
	}
	
	//includes
	function includes() {
		global $oiopub_set;
		//admin files
		include_once($oiopub_set->folder_dir . '/admin/api.php');
		include_once($oiopub_set->folder_dir . '/admin/misc.php');
		include_once($oiopub_set->folder_dir . '/admin/general.php');
	}
	
	//init classes
	function classes() {
		global $oiopub_set, $oiopub_alerts;
		//alerts class
		include_once($oiopub_set->folder_dir . '/include/alerts.php');
		$oiopub_alerts = new oiopub_alerts();
	}

	//main menu pages
	function menu_pages() {
		global $oiopub_module;
		//add core
		$res = array(
			array( 'text' => "Get Started", 'file' => "", 'method' => "overview" ),
			array( 'text' => "Settings", 'file' => "oiopub-opts.php", 'method' => "settings" ),
			array( 'text' => "Ad Zones", 'file' => "oiopub-adzones.php", 'method' => "adzones" ),
			array( 'text' => "Ad Purchases", 'file' => "oiopub-manager.php", 'method' => "purchases" ),
		);
		//add modules
		if(!empty($oiopub_module->modcount)) {
			foreach($oiopub_module->modcount as $mod) {
				if($oiopub_module->$mod[0] == 1 && $mod[6] == 'main') {
					$res[] = array( 'text' => $mod[3], 'file' => "oiopub-" . $mod[0] . ".php", 'method' => "modules" );
				}
			}
		}
		//add misc
		$res[] = array( 'text' => "Marketplace", 'file' => "oiopub-api.php", 'method' => "api" );
		//$res[] = array( 'text' => "Ad server", 'file' => "adserver", 'method' => "adserver" );
		//$res[] = array( 'text' => "Get help", 'file' => "help", 'method' => "help" );
		//return
		return $res;
	}

	//overview
	function overview($no_title=false) {
		if(empty($no_title)) {
			$no_title = false;
		}
		echo "<div class=\"wrap\">\n";
		echo "<script type=\"text/javascript\">\n";
		echo "function hidediv(id){if(document.getElementById){document.getElementById(id).style.display=\"none\";}else{if(document.layers){document.id.display=\"none\";}else{document.all.id.style.display=\"none\";}}}\n";
		echo "function showdiv(id){if(document.getElementById){document.getElementById(id).style.display=\"block\";}else{if(document.layers){document.id.display=\"block\";}else{document.all.id.style.display=\"block\";}}}\n";
		echo "</script>\n";
		oiopub_admin_general_intro($no_title);
		echo "</div>\n";
	}

	//settings
	function settings() {
		global $oiopub_set, $oiopub_module;
		$option_type = oiopub_var("opt", "get");
		echo "<div class=\"wrap\">\n";
		echo "<script type=\"text/javascript\">\n";
		echo "function hidediv(id){if(document.getElementById){document.getElementById(id).style.display=\"none\";}else{if(document.layers){document.id.display=\"none\";}else{document.all.id.style.display=\"none\";}}}\n";
		echo "function showdiv(id){if(document.getElementById){document.getElementById(id).style.display=\"block\";}else{if(document.layers){document.id.display=\"block\";}else{document.all.id.style.display=\"block\";}}}\n";
		echo "</script>\n";
		echo oiopub_admin_options_menu('oiopub-opts', $option_type);
		echo "<br />\n";
		oiopub_testmode_reminder();
		if($option_type == 'emails') {
			include_once($oiopub_set->folder_dir . '/admin/emails.php');
			oiopub_admin_emails();
		} elseif($option_type == 'templates') {
			include_once($oiopub_set->folder_dir . '/admin/templates.php');
			oiopub_admin_templates();
		} elseif($option_type == 'lang') {
			include_once($oiopub_set->folder_dir . '/admin/lang.php');
			oiopub_admin_lang();
		} elseif($option_type == 'coupons') {
			include_once($oiopub_set->folder_dir . '/admin/coupons.php');
			oiopub_admin_coupons();
		} elseif($option_type == 'geolocation') {
			include_once($oiopub_set->folder_dir . '/admin/geolocation.php');
			oiopub_admin_geolocation();
		} else {
			oiopub_admin_general_settings();
		}
		if(isset($_POST['notify']) && $_POST['notify'] > 0) {
			if(strlen($oiopub_set->api_key) == 16) {
				oiopub_update_config('api_general', $oiopub_set->api_general);
				oiopub_update_config('api_posts', $oiopub_set->api_posts);
			}
			if($_POST['notify'] == 2) {
				echo "<meta http-equiv=\"refresh\" content=\"0\" />\n";
			}
		}
		echo "</div>\n";
	}

	//settings
	function adzones() {
		global $oiopub_set, $oiopub_module;
		$option_type = oiopub_var("opt", "get");
		echo "<div class=\"wrap\">\n";
		echo "<script type=\"text/javascript\">\n";
		echo "function hidediv(id){if(document.getElementById){document.getElementById(id).style.display=\"none\";}else{if(document.layers){document.id.display=\"none\";}else{document.all.id.style.display=\"none\";}}}\n";
		echo "function showdiv(id){if(document.getElementById){document.getElementById(id).style.display=\"block\";}else{if(document.layers){document.id.display=\"block\";}else{document.all.id.style.display=\"block\";}}}\n";
		echo "</script>\n";
		echo oiopub_admin_options_menu('oiopub-adzones', $option_type);
		echo "<br />\n";
		oiopub_testmode_reminder();
		if(oiopub_links && $option_type == 'link') {
			include_once($oiopub_set->folder_dir . '/admin/links.php');
			oiopub_admin_links_settings();
		} elseif(oiopub_inline && $option_type == 'inline') {
			include_once($oiopub_set->folder_dir . '/admin/inline.php');
			oiopub_admin_inline_settings();
		} elseif(oiopub_posts && $option_type == 'post') {
			include_once($oiopub_set->folder_dir . '/admin/posts.php');
			oiopub_admin_posts_settings();	
		} elseif(oiopub_custom && $option_type == 'custom') {
			include_once($oiopub_set->folder_dir . '/admin/custom.php');
			oiopub_admin_custom_settings();
		} elseif(oiopub_banners) {
			include_once($oiopub_set->folder_dir . '/admin/banners.php');
			oiopub_admin_banners_settings();
		}
		if(isset($_POST['notify']) && $_POST['notify'] > 0) {
			if(strlen($oiopub_set->api_key) == 16) {
				oiopub_update_config('api_general', $oiopub_set->api_general);
				oiopub_update_config('api_posts', $oiopub_set->api_posts);
			}
			if($_POST['notify'] == 2) {
				echo "<meta http-equiv=\"refresh\" content=\"0\" />\n";
			}
		}
		echo "</div>\n";
	}

	//purchases
	function purchases() {
		global $oiopub_set, $oiopub_alerts;
		$option_type = oiopub_var('opt', 'get');
		echo "<div class=\"wrap\">\n";
		echo oiopub_admin_options_menu('oiopub-manager', $option_type);
		echo "<br />\n";
		oiopub_testmode_reminder();
		if(oiopub_posts && $option_type == 'post') {
			include_once($oiopub_set->folder_dir . '/admin/posts.php');
			oiopub_admin_posts_purchase();
		} elseif(oiopub_links && $option_type == 'link') {
			include_once($oiopub_set->folder_dir . '/admin/links.php');
			oiopub_admin_links_purchase();
		} elseif(oiopub_inline && $option_type == 'inline') {
			include_once($oiopub_set->folder_dir . '/admin/inline.php');
			oiopub_admin_inline_purchase();
		} elseif(oiopub_banners && $option_type == 'banner') {
			include_once($oiopub_set->folder_dir . '/admin/banners.php');
			oiopub_admin_banners_purchase();
		} elseif(oiopub_custom && $option_type == 'custom') {
			include_once($oiopub_set->folder_dir . '/admin/custom.php');
			oiopub_admin_custom_purchase();
		} elseif($option_type == 'reports') {
			include_once($oiopub_set->folder_dir . '/admin/reports.php');
			oiopub_admin_reports_manager();
		} else {
			echo "<h2>New purchase alerts</h2>\n";
			$oiopub_alerts->purchases(1);
			echo "<br /><br />\n";
			echo "<h3>Create New Purchase:</h3>\n";
			echo "<div style='line-height:22px;'>\n";
			if(oiopub_posts) echo "&raquo; <a href='".$oiopub_set->plugin_url_org."/edit.php?type=post' target='_blank'>New Post</a><br />\n";
			if(oiopub_links) echo "&raquo; <a href='".$oiopub_set->plugin_url_org."/edit.php?type=link' target='_blank'>New Text Ad</a><br />\n";
			if(oiopub_inline) echo "&raquo; <a href='".$oiopub_set->plugin_url_org."/edit.php?type=inline' target='_blank'>New Inline Ad</a><br />\n";
			if(oiopub_banners) echo "&raquo; <a href='".$oiopub_set->plugin_url_org."/edit.php?type=banner' target='_blank'>New Banner Ad</a><br />\n";
			if(oiopub_custom) echo "&raquo; <a href='".$oiopub_set->plugin_url_org."/edit.php?type=custom' target='_blank'>New Custom Purchase</a><br />\n";
			echo "</div>\n";
			echo "<br /><br />\n";
			echo "<h3>Ad Purchasing Link:</h3>\n";
			echo "<a href='" . $oiopub_set->plugin_url . "/purchase.php' target='_blank'>" . $oiopub_set->plugin_url . "/purchase.php</a>";
		}
		echo "</div>\n";
	}

	//adserver
	function adserver() {
		global $oiopub_set, $oiopub_module;
		//output page
		echo "<style type=\"text/css\">.example { color:#675D1C; border:1px solid #FFE95A; background:#FFFBE0; padding:10px; overflow:visible; font-size:13px; font-weight:bold; }</style>\n";
		echo "<div class=\"wrap\">\n";
		echo "<h2>OIOpublisher Ad Server (javascript)</h2>\n";
		echo "Show banner and text ads on other websites easily using one line of javascript.\n";
		if($oiopub_module->tracker == 1 && $oiopub_set->tracker['referer_filter'] == "whitelist") {
			echo "<br /><br />\n";
			echo "<font color='red'>If you place javascript code on other domains, and want to track stats, you will need to add those domains to the <a href='admin.php?page=oiopub-modules.php&module=tracker&opt=settings#filters'>referer url whitelist</a></font>\n";
			echo "<br />\n";
		}
		echo "<br /><br /><br />\n";
		echo "<b>Text Ad Code</b> &nbsp[<a href='admin.php?page=oiopub-opts.php&opt=link'>configure settings</a>]\n";
		echo "<br />\n";
		echo "<i>Replace X with the zone number you want to display.</i>\n";
		echo "<br /><br />\n";
		echo "<div class=\"example\">\n";
		echo htmlspecialchars('<script type="text/javascript" src="' . $oiopub_set->plugin_url . '/js.php?type=link&align=center&zone=X"></script>') . "\n";
		echo "</div>\n";
		echo "<br /><br /><br />\n";
		echo "<b>Banner Ad Code</b> &nbsp[<a href='admin.php?page=oiopub-opts.php&opt=banner'>configure settings</a>]\n";
		echo "<br />\n";
		echo "<i>Replace X with the zone number you want to display.</i>\n";
		echo "<br /><br />\n";
		echo "<div class=\"example\">\n";
		echo htmlspecialchars('<script type="text/javascript" src="' . $oiopub_set->plugin_url . '/js.php?type=banner&align=center&zone=X"></script>') . "\n";
		echo "</div>\n";
		echo "<br /><br />\n";
		echo "</div>\n";
	}

	//api services
	function api() {
		global $oiopub_set;
		echo "<div class=\"wrap\">\n";
		echo "<form action=\"" . $oiopub_set->request_uri . "\" method=\"post\">\n";
		echo "<input type='hidden' name='csrf' value='" . oiopub_csrf_token() . "' />\n";
		echo "<h2>OIOpublisher API Key</h2>\n";
		echo "The API key is used to authenticate your website when adding it to the <a href='http://www.oiopublisher.com/market.php' target='_blank'>marketplace</a>. It also allows you to use OIO's automatic upgrade feature.";
		echo "<br /><br />\n";
		echo "To get your website's API Key, you must <a href='http://www.oiopublisher.com/submit.php' target='_blank'>submit your site</a> to the marketplace. Once approved, a key will be generated for you and made available <a href='http://www.oiopublisher.com/account_data.php' target='_blank'><b>here</b></a>.";
		echo "<br /><br />\n";
		echo "<font color='red'>&raquo; Please note that an API key is <b>not required</b> to use the script, only for marketplace participation.</font>\n";
		echo "<br /><br /><br />\n";
		oiopub_admin_api_key();
		echo "<div class=\"submit\"><input type=\"submit\" value=\"Update API Key\" /></div>\n";
		echo "</form>\n";
		echo "</div>\n";
	}
	
	//my modules
	function modules() {
		global $oiopub_hook;
		echo "<div class=\"wrap\">\n";
		$oiopub_hook->fire('my_modules');
		echo "</div>\n";
	}
	
	//get help
	function help() {
		global $oiopub_set;
		if(isset($_GET['page']) && $_GET['page'] == "oiopub-help.php") {
			if(!empty($_GET['redirect'])) {
				$location = "admin.php?page=" . oiopub_clean($_GET['redirect']) . "&help=1";
				$location = str_replace("&amp;", "&", $location);
				header("Location: " . $location);
				exit();
			} elseif(!empty($_SESSION['oiopub']['help_link'])) {
				$url = parse_url($_SESSION['oiopub']['help_link']);
				$location = (empty($url['query']) ? $_SESSION['oiopub']['help_link'] . "?help=1" : $_SESSION['oiopub']['help_link'] . "&help=1");
				$location = str_replace("&amp;", "&", $location);
				header("Location: " . $location);
				exit();
			}
		} else {
			if(!defined('OIOPUB_NEWS') && !defined('OIOPUB_HELP')) {
				$request = str_replace(array("?help=1", "&amp;help=1"), array("", ""), $oiopub_set->request_uri);
				$_SESSION['oiopub']['help_link'] = $request;
			}
		}
	}
	
}

?>