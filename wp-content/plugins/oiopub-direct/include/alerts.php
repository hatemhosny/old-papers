<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

//alerts
class oiopub_alerts {

	var $auto_upgrade = 1;

	//header
	function header($args=array(), $title='', $request='', $request_text='Continue') {
		global $oiopub_set;
		echo "<style type='text/css'>\n";
		echo ".oio-overlay { display:block; width:100%; height:100%; z-index:1000; position:fixed; top:0px; left:0px; background:#000; opacity:0.3; filter:alpha(opacity=30); -ms-filter:\"alpha(opacity=30)\"; }\n";
		echo ".oio-box { display:block; z-index:1000; width:" . $args['width'] . "px; position:fixed; top:" . $args['margin'] . "px; left:50%; margin-left:-" . ($args['width'] / 2) . "px; padding:15px; background:#FFF; border:1px solid #000; text-align:left; }\n";
		echo "* html { overflow-y:hidden; }\n";
		echo "* html body { overflow-y:auto; height:100%; }\n";		
		echo "* html .oio-overlay { position: absolute; width:99%; }\n";
		echo "* html .oio-box { position:absolute; }\n";
		echo "</style>\n";
		echo "<div class='oio-overlay'></div>\n";
		echo "<div class='oio-box'>\n";
		if(!empty($title) || !empty($request)) {
			echo "<table style='width:100%; border:0px; margin-bottom:20px;' cellspacing='0' cellpadding='0'>\n";
			echo "<tr>\n";
			if(!empty($title)) {
				echo "<td valign='top'><h3 style='margin:0px; padding:0px;'><font color='red'>" . $title . "</font></h3></td>\n";
			}
			if(!empty($request)) {
				echo "<td valign='top' style='text-align:right;'>[<a href='" . $request . "'><b>" . $request_text . "</b></a>]</td>\n";
			}
			echo "</tr>\n";
			echo "</table>\n";
		}	
	}
	
	//footer
	function footer() {
		echo "</div>\n";
	}
	
	//allow overlay?
	function display_allow() {
		global $oiopub_set;
		//block: no version status set
		if(!isset($oiopub_set->version_status)) {
			return false;
		}
		//block: ajax request
		if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH']) {
			if(strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
				return false;
			}
		}
		//block: invalid user account
		if(!oiopub_is_admin() || !oiopub_auth_check() || (isset($oiopub_set->upgrade_alert) && $oiopub_set->upgrade_alert == 1)) {
			return false;
		}
		//allow: OIO pages
		if(strpos($oiopub_set->request_uri, "oiopub") !== false) {
			return true;
		}
		//allow: admin url base
		$page_url = str_replace(array($oiopub_set->query_string, "?"), array("", ""), $oiopub_set->page_url);
		$page_url = str_replace(array("admin.php", "index.php", "install.php"), array("", "", ""), $page_url);
		$page_url = rtrim($page_url, "/");
		if($page_url === $oiopub_set->admin_url) {
			return true;
		}
		//failed
		return false;
	}
	
	//display alerts
	function display() {
		if($this->display_allow()) {
			if(!$this->help()) {
				if(!$this->errors()) {
					if(!$this->welcome()) {
						if(!$this->complete()) {
							if(!$this->popup()) {
								if(!$this->auto_upgrade()) {
									$this->announce();
								}
							}
						}
					}
				}
			}
		}
	}
	
	//welcome
	function welcome() {
		global $oiopub_set;
		if(isset($_GET['welcome']) && $_GET['welcome'] == 1) {
			//double-check install
			if(!oiopub_install_check()) {
				oiopub_install_wrapper();
			}
			//show welcome message
			$args = array( 'width'=>600, 'height'=>400, 'margin'=>100 );
			$title = "Welcome to OIOpublisher 2.6";
			$request = str_replace(array("?welcome=1", "&amp;welcome=1"), array("", ""), $oiopub_set->request_uri);
			$this->header($args, $title, $request);
			//fsockopen check
			$parse = parse_url($oiopub_set->plugin_url_org);
			$fp = @fsockopen(gethostbyname($parse['host']), 80, $errno, $errstr, 5);
			//any error?
			if(!$fp && $errno > 0) {
				echo "<h3>Scheduled Tasks Error Detected</h3>\n";
				echo "It appears that your server does not support OIO task scheduling. To rectify this problem, please setup a cron job to run once every 30 minutes on your server, using the following script location.\n";
				echo "<br /><br />\n";
				echo "<i>" . $oiopub_set->folder_dir . "/cron.php</i>\n";
				echo "<br /><br />\n";
				echo "If you are unsure how to setup a cron job on your server, please contact your web host.\n";
				echo "<br /><br />\n";
				//test failed
				oiopub_update_config('cron_disabled', 1);
			} else {
				//test passed
				oiopub_update_config('cron_disabled', 0);
			}
			//install complete message
			echo "<h3>Installation Complete</h3>\n";
			echo "<p>You have just completed your installation of the OIOpublisher Ad Manager. Below are a few links you might find helpful, in case you run into any problems:</p>\n";
			echo "<ul style='margin:20px 0 40px 0; line-height:24px;'>\n";
			echo "<li><a href='http://forum.oiopublisher.com' target='_blank'>Dedicated Support Forum</a></li>\n";
			echo "<li><a href='http://docs.oiopublisher.com' target='_blank'>Installation Documentation</a></li>\n";
			echo "</ul>\n";
			echo "<h3>Quick Setup Guide</h3>\n";
			echo "To setup your ads, please <a href='" . $request . "'>click here</a> and follow the 'quick start guide' instructions.\n";
			$this->footer();
			return true;
		}
		return false;
	}
	
	//get help
	function help() {
		global $oiopub_set;
		if(isset($_GET['help']) && $_GET['help'] == 1) {
			//get vars
			$page = oiopub_var('page', 'get');
			$opt = oiopub_var('opt', 'get');
			$module = oiopub_var('module', 'get');
			$show = oiopub_var('show', 'get');
			//header args
			$args = array( 'width'=>600, 'height'=>400, 'margin'=>100 );
			$title = "OIOpublisher Help Desk";
			$request = str_replace(array("?help=1", "&amp;help=1"), array("", ""), $oiopub_set->request_uri);
			$request = str_replace(array("?show=".$show, "&amp;show=".$show), array("", ""), $request);
			$this->header($args, $title, $request);
			echo "<iframe src='" . $oiopub_set->plugin_url_org . "/iframe/help-desk.php?page=" . $page . "&opt=" . $opt . "&module=" . $module . "&show=" . $show . "' frameborder='no' allowtransparency='true' style='width:100%; height:350px; border:0px;'></iframe>\n";
			$this->footer();
			return true;
		}
		return false;
	}
	
	//action complete
	function complete() {
		global $oiopub_set, $oiopub_cron, $oiopub_db;
		$complete = oiopub_var('complete', 'get');
		if(!empty($complete)) {
			//refresh cron jobs
			$oiopub_cron->refresh_all();
			//process request
			$args = array( 'width'=>600, 'height'=>400, 'margin'=>100 );
			$title = "OIOpublisher Successfully Upgraded to v" . $complete . "!";
			$request = str_replace(array("?complete=".$complete, "&amp;complete=".$complete), array("", ""), $oiopub_set->request_uri);
			$this->header($args, $title, $request);
			//display any notable changes
			if(strpos($complete, "2.01") !== false) {
				//v2 announcement
				echo "<b>Important Changes in OIOpublisher 2.0:</b> (please read carefully)\n";
				echo "<br /><br />\n";
				echo "<table border='0'>\n";
				echo "<tr><td align='justify'>\n";
				echo "<b>Text Link Zones</b>\n";
				echo "<br />\n";
				echo "Text links now use zones in the same way as Banner Ads. Any active text ads you had prior to this upgrade should all have been moved to 'Zone 1'. Once you have defined your preferred Text Link zones, you can put an existing link in another zone using the <a href='admin.php?page=oiopub-manager.php&opt=link'>purchase manager</a>.\n";
				echo "<br /><br />\n";
				echo "<b>Enhanced Ad Server</b>\n";
				echo "<br />\n";
				echo "The <a href='admin.php?page=oiopub-adserver.php'>Ad Server</a> tab now allows you to display both Text Ad and Image based zones.\n";
				echo "<br /><br />\n";
				echo "<b>My Modules</b>\n";
				echo "<br />\n";
				echo "The module extensions have now been consolidated under a single tab, <a href='admin.php?page=oiopub-modules.php'>My Modules</a>. You can view and configure all installed modules from that screen.\n";
				echo "<br /><br />\n";
				echo "<b>Use Anywhere</b>\n";
				echo "<br />\n";
				echo "You're now no longer restricted just to Wordpress! You can install this script as a standalone tool. As time goes on we'll also provide enhanced integration with other CMS scripts, just as we have with Wordpress.\n";			
				echo "</td></tr>\n";
				echo "</table>\n";
			} elseif(strpos($complete, "2.55") !== false && $oiopub_set->platform == 'wordpress') {
				//update widgets requirement
				echo "<b>Important: you must re-create your OIO widgets</b>\n";
				echo "<br /><br />\n";
				echo "This version of OIO introduces new, more flexible WordPress widgets for displaying ad zones.\n";
				echo "<br /><br />\n";
				echo "If you were using the old widgets, these will no longer work - please <a href='widgets.php'>click here</a> to replace them now.\n";
			} else {
				echo "Upgrade successfully completed!\n";
			}
			//db errors?
			if($oiopub_db->errors) {
				echo "<br /><br />\n";
				echo "The following database errors were detected during the upgrade. If you are unsure what to do, just post the errors to the <a href='http://forum.oiopublisher.com' target='_blank'>support forum</a>.";
				echo "<br /><br />\n";
				foreach($oiopub_db->errors as $error) {
					echo $error . "\n";
					echo "<br /><br />\n";
				}
			}
			if(strpos($complete, ".b") !== false) {
				echo "<br /><br />\n";
				echo "<font color='blue'><b>&raquo; Thanks for helping to test out this beta version of OIOpublisher...</b></font>\n";
				echo "<br />\n";
				echo "<i>Please report any bugs or issues to the <a href='http://forum.oiopublisher.com' target='_blank'>forum</a>.</i>";
			}
			$this->footer();
			return true;
		}
		return false;
	}
	
	//settings popup
	function popup() {
		global $oiopub_set, $oiopub_hook;
		$popup = oiopub_var('popup', 'get');
		if(!empty($popup)) {
			$args = array( 'width'=>600, 'height'=>480, 'margin'=>90 );
			$title = "OIOpublisher Settings: " . $popup;
			$request = str_replace(array("?popup=".$popup, "&amp;popup=".$popup), array("", ""), $oiopub_set->request_uri);
			$this->header($args, $title, $request);
			$oiopub_hook->fire('settings_popup');
			$this->footer();
			return true;
		}
		return false;
	}
	
	//errors
	function errors() {
		global $oiopub_set, $oiopub_db, $oiopub_cache;
		$res = '';
		//get args
		$args = array($oiopub_db->errors, $oiopub_cache->errors);
		//check input
		if(!empty($args)) {
			foreach($args as $error) {
				if(!empty($error)) {
					foreach($error as $err) {
						$res .= "<li>" . $err . "</li>\n";
					}
				}
			}
		}
		//upload directory check
		if(!@is_writable($oiopub_set->folder_dir . "/uploads")) {
			$res .= "<li>Please make the OIOpublisher 'uploads' directory writable</li>\n";
		}
		//plugin url check
		if(empty($oiopub_set->plugin_url)) {
			$res .= "<li>Please edit the OIOpublisher config.php file and fill in the 'PLUGIN URL' variable</li>\n";
		}
		/*
		//db table check
		if(!defined('OIOPUB_INSTALL')) {
			if(!oiopub_checktable($oiopub_set->dbtable_purchases)) {
				if(empty($_SERVER['QUERY_STRING'])) {
					$request = $oiopub_set->request_uri . "?help=1&show=tables";
				} else {
					$request = $oiopub_set->request_uri . "&help=1&show=tables";
				}
				$res .= "<li>Database tables not found, please add them manually by <a href='" . $request . "'>clicking here</a></li>\n";
			}
		}
		*/
		//output errors
		if(!empty($res)) {
			$args = array( 'width'=>600, 'height'=>400, 'margin'=>100 );
			$title = "OIOpublisher Setup Errors";
			$this->header($args, $title, $oiopub_set->request_uri);
			echo "For an explanation of how to deal with the errors below, please <a href='http://www.oiopublisher.com/install_errors.php' target='_blank'><b>click here</b></a>.\n";
			echo "<br /><br /><br />\n";
			echo "<ul style='line-height:20px;'>\n";
			echo $res;
			echo "<li><b>Once you have dealt with the errors, please press the continue link</b></li>";
			echo "</ul>\n";
			if($oiopub_set->platform == "wordpress") {
				if(empty($_SERVER['QUERY_STRING'])) {
					$request = $oiopub_set->request_uri . "?oio-deactivate=1";
				} else {
					$request = $oiopub_set->request_uri . "&oio-deactivate=1";
				}
				echo "<br /><br /><br />\n";
				echo "<i>NB: If the errors persist, you can also <a href='" . $request . "'>click here</a> to deactivate the script if required.</i>\n";
			}
			$this->footer();
			return true;
		}
		return false;
	}

	//announcements
	function announce() {
		global $oiopub_set;
		//new version notify
		if($oiopub_set->version_status == 1 && $oiopub_set->overlay_notify == 1) {
			if(isset($_GET['page']) && $_GET['page'] == 'oiopub-api.php') {
				return;
			}
			if(isset($_GET['notify']) && $_GET['notify'] == 'stop1') {
				oiopub_update_config('overlay_notify', 0);
			} else {
				if(empty($_SERVER['QUERY_STRING'])) {
					$request = $oiopub_set->request_uri . "?notify=stop1";
				} else {
					$request = $oiopub_set->request_uri . "&notify=stop1";
				}
				$args = array( 'width'=>600, 'height'=>400, 'margin'=>100 );
				$title = "OIOpublisher Update Alert";
				$request_text = "Remove Notice";
				$this->header($args, $title, $request, $request_text);
				echo "<h3 style='margin-top:10px;'>New version now available! &nbsp;&nbsp;<font size='2'>[<a href='http://download.oiopublisher.com' target='_blank'>download now</a>]</font></h3>\n";
				echo "Below is a list of features and updates from the previous OIOpublisher version:\n";
				echo "<br /><br />\n";
				$data = oiopub_file_contents("http://api.oiopublisher.com/2.0/version-changes.txt");
				if(!empty($data)) {
					echo $data;
				} else {
					echo "* No data available - apologies for the mix up!\n";
				}
				echo "<br /><br /><br />\n";
				echo "<b>Choose Upgrade Method:</b>\n";
				echo "<br /><br />\n";
				if(!empty($oiopub_set->query_string)) {
					$upgrade_url = $oiopub_set->request_uri . "&oiopub-auto-upgrade=1";
				} else {
					$upgrade_url = $oiopub_set->request_uri . "?oiopub-auto-upgrade=1";
				}
				echo "<div style='line-height:24px;'>\n";
				if($this->auto_upgrade == 1) {
					$fs = oiopub_filesystem();
					if(!empty($fs->file_system)) {
						if(strlen($oiopub_set->api_key) == 16 && $oiopub_set->api_valid == 1) {
							echo "* <a href='" . $upgrade_url . "'><b>Auto Upgrade</b></a> - please backup your database before continuing\n";
						} else {
							echo "* <b>Auto Upgrade</b> - please enter your <a href='admin.php?page=oiopub-api.php'>API key</a> to use auto-upgrade\n";
						}
						echo "<br />\n";
					}
				}
				echo "* <a href='http://www.oiopublisher.com/installer/step1.php?mode=upgrade&platform=" . $oiopub_set->platform . "' target='_blank'><b>Web Installer</b></a> - click and go upgrade, no downloads, no hassle\n";
				echo "<br />\n";
				echo "* <a href='http://download.oiopublisher.com' target='_blank'><b>Manual Upgrade</b></a> - download OIOpublisher zip file manually\n";
				echo "</div>\n";
				$this->footer();
				return true;
			}
		}
		//new alert notify
		if($oiopub_set->alert_current > $oiopub_set->alert_last || isset($_GET['alert']) && $_GET['alert'] == 1) {
			if(isset($_GET['notify']) && $_GET['notify'] == 'stop2') {
				oiopub_update_config('alert_current', $oiopub_set->alert_current);
				oiopub_update_config('alert_last', $oiopub_set->alert_current);
			} else {
				if(empty($_SERVER['QUERY_STRING'])) {
					$request = $oiopub_set->request_uri . "?notify=stop2";
				} else {
					$request = $oiopub_set->request_uri . "&notify=stop2";
				}
				$args = array( 'width'=>600, 'height'=>400, 'margin'=>100 );
				$title = "OIOpublisher Message Alert";
				$request_text = "Remove Notice";
				$this->header($args, $title, $request, $request_text);
				$data = oiopub_file_contents("http://api.oiopublisher.com/2.0/alerts/oio-" . $oiopub_set->alert_current . ".txt");
				if(!empty($data)) {
					echo $data;
				} else {
					echo "No data available - apologies for the mix up!";
				}
				$this->footer();
				return true;
			}
		}
		return false;
	}
	
	//purchases
	function purchases() {
		global $oiopub_db, $oiopub_set, $oiopub_module;
		$alert = false;
		$posts = array( "pending"=>0, "unpaid"=>0, "unpublished"=>0, "badpay"=>0 );
		$links = array("pending"=>0, "unpaid"=>0, "badpay"=>0 );
		$inline = array("pending"=>0, "unpaid"=>0, "badpay"=>0 );
		$custom = array("pending"=>0, "unpaid"=>0, "badpay"=>0 );
		$banners = array("pending"=>0, "unpaid"=>0, "badpay"=>0 );
		$purchases = $oiopub_db->CacheGetAll("SELECT item_channel,item_status,payment_status,published_status FROM " . $oiopub_set->dbtable_purchases . " WHERE item_status < 2");
		if(!empty($purchases)) {
			foreach($purchases as $p) {
				if($p->item_channel == 1) {
					if($p->item_status == 0 || $p->item_status == -2) $posts['pending']++;
					if($p->payment_status == 0) $posts['unpaid']++;
					if($p->item_status == 1 && $p->published_status == 0) $posts['unpublished']++;
					if($p->item_status <= 1 && $p->payment_status == 2) $posts['badpay']++;
				}
				if($p->item_channel == 2) {
					if($p->item_status == 0 || $p->item_status == -2) $links['pending']++;
					if($p->payment_status == 0) $links['unpaid']++;
					if($p->item_status <= 1 && $p->payment_status == 2) $links['badpay']++;
				}
				if($p->item_channel == 3) {
					if($p->item_status == 0 || $p->item_status == -2) $inline['pending']++;
					if($p->payment_status == 0) $inline['unpaid']++;
					if($p->item_status <= 1 && $p->payment_status == 2) $inline['badpay']++;
				}
				if($p->item_channel == 4) {
					if($p->item_status == 0 || $p->item_status == -2) $custom['pending']++;
					if($p->payment_status == 0) $custom['unpaid']++;
					if($p->item_status <= 1 && $p->payment_status == 2) $custom['badpay']++;
				}
				if($p->item_channel == 5) {
					if($p->item_status == 0 || $p->item_status == -2) $banners['pending']++;
					if($p->payment_status == 0) $banners['unpaid']++;
					if($p->item_status <= 1 && $p->payment_status == 2) $banners['badpay']++;
				}
			}
		}
		echo "<table border='0' cellpadding='2' cellspacing='2'>\n";
		echo "<tr><td>\n";
		if($oiopub_set->version_status == 1) {
			echo "<font color='red'><b>New Version Available - <a href='http://download.oiopublisher.com' target='_blank'>download now</a></b></font>\n";
			echo "<br /><br />\n";
			$alert = true;
		}
		if(isset($oiopub_set->posts_total) && $oiopub_set->posts_total >= 0) {
			$display = oiopub_alert_output($posts, "post");
			if(!empty($display)) $alert = true;
			echo $display;
		}
		if(isset($oiopub_set->links_total) && $oiopub_set->links_total >= 0) {
			$display = oiopub_alert_output($links, "link");
			if(!empty($display)) $alert = true;
			echo $display;
		}
		if(isset($oiopub_set->inline_total) && $oiopub_set->inline_total >= 0) {
			$display = oiopub_alert_output($inline, "inline");
			if(!empty($display)) $alert = true;
			echo $display;
		}
		if(isset($oiopub_set->custom_total) && $oiopub_set->custom_total >= 0) {
			$display = oiopub_alert_output($custom, "custom");
			if(!empty($display)) $alert = true;
			echo $display;
		}
		if(isset($oiopub_set->banners_total) && $oiopub_set->banners_total >= 0) {
			$display = oiopub_alert_output($banners, "banner");
			if(!empty($display)) $alert = true;
			echo $display;
		}
		if(isset($oiopub_module->socialposts) && $oiopub_module->socialposts == 1) {
			$count = $oiopub_db->GetOne("SELECT COUNT(*) FROM " . $oiopub_set->dbtable_groups_replies . " WHERE status='0' AND response='0'");
			if($count > 0) {
				echo "<a href=\"admin.php?page=oiopub-socialposts.php\">" . $count . "</a> new conversations require moderation";
				$alert = true;
			}
		}
		if($alert == false) {
			echo "No alerts at the moment. Everything is at peace.\n";
		}
		echo "</td></tr>\n";
		echo "</table>\n";
	}
	
	//module alerts
	function modules($title=true) {
		global $oiopub_module, $oiopub_set;
		$mod_links = ""; $output = ""; $number = 0;
		if($title == true) {
			$output = "<h2>OIOpublisher Modules &nbsp;<small>[ <a href=\"http://download.oiopublisher.com/modules.php?do=check&core=" . $oiopub_set->version . "%mod_links%\" target=\"_blank\">check for updates</a> ]</small></h2>\n";
		}
		if(oiopub_count($oiopub_module->modcount) > 0) {
			$output .= "<table border='0'>\n";
			$output .= "<tr><td valign='top' style='padding-right:30px;'>\n";
			$output .= "<table border='0'>\n";
			if(!empty($oiopub_module->modcount)) {
				asort($oiopub_module->modcount);
				foreach($oiopub_module->modcount as $mod) {
					$number++;
					if($mod[1] == 1) {
						$status = "<a href=\"admin.php?page=oiopub-" . $mod[0] . ".php\">enabled</a>";
					} elseif($mod[1] == 0) {
						$status = "<a href=\"admin.php?page=oiopub-" . $mod[0] . ".php\" style=\"color:red;\">disabled</a>";
					} else {
						$status = "<a href=\"admin.php?page=oiopub-" . $mod[0] . ".php\" style=\"color:black;\" onclick=\"alert('A module is unavailable when the platform OIOpublisher is installed on does not support an element required by that module - for example posts.'); return false;\">unavailable</a>";
					}
					$output .= "<tr><td>" . $mod[3] . "</td><td style='padding-left:10px;'>[v" . $mod[2] . "]</td><td style='padding-left:10px;'>[" . $status . "]</td></tr>\n";
					$mod_links .= "&" . $mod[0] . "=" . $mod[2];
					if(($number % 5) == 0) {
						$output .= "</table>\n";
						$output .= "</td><td valign='top' style='padding-right:30px;'>\n";
						$output .= "<table border='0'>\n";
					}
				}
			}
			$output .= "</table>\n";
			$output .= "</td></tr>\n";
			$output .= "</table>\n";
		} else {
			$output .= "<i>no modules currently installed - <a href=\"http://download.oiopublisher.com/modules.php\" target=\"_blank\">view available modules</a></i>\n";
		}
		echo str_replace("%mod_links%", $mod_links, $output);
	}

	//latest news
	function news($number=5) {
		global $oiopub_set, $oiopub_cache;
		$feed = 'feeds.feedburner.com/SimonEmery/OIOpublisher';
		echo "<iframe src='" . $oiopub_set->plugin_url_org . "/iframe/news-feed.php?feed=" . urlencode($feed) . "&num=" . $number . "' frameborder='no' style='width:100%; height:100%; border:0;'></iframe>\n";
	}
	
	//upgrading
	function upgrade() {
		global $oiopub_set, $oiopub_version;
		if(oiopub_is_admin()) {
			if(isset($_GET['page']) && $_GET['page'] == "stats") {
				return;
			}
			if(empty($_SERVER['QUERY_STRING'])) {
				$request = $oiopub_set->request_uri . "?oio-upgrade=1";
			} else {
				$request = $oiopub_set->request_uri . "&oio-upgrade=1";
			}
			$args = array( 'width'=>600, 'height'=>400, 'margin'=>100 );
			$title = "OIOpublisher Upgrading";
			$this->header($args, $title, $request);
			echo "<h3 style='margin-top:10px;'>You are about to upgrade from v" . $oiopub_set->version . " to v" . $oiopub_version . "</h3>";
			echo "<ul style='margin:30px 0 30px 0;'>\n";
			echo "<li>Have you remembered to <u>backup your database</u>?</li>\n";
			echo "<li>Have you saved any customisations you might have made?</li>\n";
			echo "</ul>\n";
			echo "<a href='" . $request . "'><b>Continue with Upgrade Now</b></a>\n";
			$this->footer();
			$oiopub_set->upgrade_alert = 1;
			return true;
		}
		return false;
	}
	
	//auto-upgrading
	function auto_upgrade() {
		global $oiopub_set, $oiopub_version;
		if(oiopub_is_admin() && $this->auto_upgrade == 1) {
			if(isset($_GET['oiopub-auto-upgrade']) && $_GET['oiopub-auto-upgrade'] == 1) {
				$fs = oiopub_filesystem();
				if(!empty($fs->file_system)) {
					$errors = '';
					if(strlen($oiopub_set->api_key) != 16 || $oiopub_set->api_valid != 1) {
						$errors .= "<li>Please enter your <a href='admin.php?page=oiopub-api.php' target='_blank'>API key</a> to use the auto-upgrade feature</li>\n";
					}
					$args = array( 'width'=>600, 'height'=>400, 'margin'=>100 );
					$title = "OIOpublisher Auto Upgrade Preparation";
					$request = str_replace(array("?oiopub-auto-upgrade=1", "&amp;oiopub-auto-upgrade=1"), array("", ""), $oiopub_set->request_uri);
					$request_text = "Cancel Upgrade";
					$this->header($args, $title, $request, $request_text);
					echo "<br />\n";
					if(!empty($errors)) {
						echo "<b>Please do the following to continue:</b>\n";
						echo "<ul>\n";
						echo $errors;
						echo "</ul>\n";
						echo "<a href=''><b>Then click here to continue the upgrade process</b></a>\n";
					} else {
						oiopub_flush();
						oiopub_upgrade_script("auto");
					}
					$this->footer();
					return true;
				}
			}
		}
		return false;
	}
	
}

?>