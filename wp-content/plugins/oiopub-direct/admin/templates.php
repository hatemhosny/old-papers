<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

//template editing
function oiopub_admin_templates() {
	oiopub_admin_templates_change();
	oiopub_admin_templates_mode();
	oiopub_admin_templates_editing();
}

//template editing
function oiopub_admin_templates_change() {
	global $oiopub_set;
	//get vars
	$template_name = oiopub_var("t", "get");
	$themes_dir = $oiopub_set->folder_dir . "/templates/";
	$themes = oiopub_admin_templates_readdir($themes_dir, 0, array("", ".", "..", "core"));
	//add cms theme?
	if($oiopub_set->platform != "standalone") {
		$themes[] = $oiopub_set->platform;
	}
	//sort themes
	sort($themes);
	//update template
	if(isset($_POST['process']) && $_POST['process'] == "theme") {
		if(!empty($_POST['oiopub_theme'])) {
			$oiopub_set->template = oiopub_clean($_POST['oiopub_theme']);
			oiopub_update_config('template', $oiopub_set->template);
			if($oiopub_set->template == $oiopub_set->platform) {
				oiopub_update_config('theme_mode', 1);
			} else {
				oiopub_update_config('theme_mode', 0);
			}
		}
	}
	//show output
	echo "<h2>Active Theme</h2>\n";
	echo "Your chosen theme will appear as the design on all OIOpublisher templates. [<a href='" . $oiopub_set->plugin_url . "/purchase.php' target='_blank'>check it out</a>]\n";
	echo "<br /><br />\n";
	echo "<form method='post' action='" . str_replace($template_name, "", $oiopub_set->request_uri) . "'>\n";
	echo "<input type='hidden' name='csrf' value='" . oiopub_csrf_token() . "' />\n";
	echo "<input type='hidden' name='process' value='theme' />\n";
	echo oiopub_dropmenu_k($themes, "oiopub_theme", $oiopub_set->template, 170);
	echo "&nbsp; <input type='submit' value='Change Theme' />\n";
	echo "</form>\n";
	echo "<br /><br />\n";
}

function oiopub_admin_templates_mode() {
	global $oiopub_set;
	//get vars
	$mode_array = array( 0=>'basic', 1=>'advanced' );
	//update
	if(isset($_POST['process']) && $_POST['process'] == "mode") {
		$oiopub_set->theme_mode = intval($_POST['oiopub_edit_mode']);
		oiopub_update_config('theme_mode', $oiopub_set->theme_mode);
	}
	if($oiopub_set->template != $oiopub_set->platform) {
		echo "<h2>Editing Mode</h2>\n";
		echo "Advanced mode allows you to edit the core templates (editing core templates will make upgrading less easy).\n";
		echo "<br /><br />\n";
		echo "<form method='post' action='" . $oiopub_set->request_uri . "'>\n";
		echo "<input type='hidden' name='csrf' value='" . oiopub_csrf_token() . "' />\n";
		echo "<input type='hidden' name='process' value='mode' />\n";
		echo oiopub_dropmenu_kv($mode_array, "oiopub_edit_mode", $oiopub_set->theme_mode, 170);
		echo "&nbsp; <input type='submit' value='Change Mode' />\n";
		echo "</form>\n";
	} else {
		echo "<h2>Not looking quite right?</h2>\n";
		echo "<p>If things are looking a bit out of place after choosing the 'Wordpress' theme option, check out the link below:</p>\n";
		echo "<p>&raquo; <a href='http://forum.oiopublisher.com/discussion/520/customising-the-ad-manager/#Item_4' target='_blank'>Wordpress theme integration, tweaking guide</a></p>\n";	
	}
	echo "<br /><br />\n";
}

//template editing
function oiopub_admin_templates_editing() {
	global $oiopub_set, $oiopub_module;
	//get vars
	$template_name = oiopub_var("t", "get");
	$template_path = $oiopub_set->folder_dir . "/" . $template_name;
	$template_exists = @file_exists($template_path);
	$template_writable = @is_writable($template_path);
	//save to file
	if(isset($_POST['process']) && $_POST['process'] == "edit") {
		if($template_exists && $template_writable) {
			$update = stripslashes($_POST['oio_content']);
			$update = str_replace("\r\n", "\n", $update);
			$fp = @fopen($template_path, 'w+');
			@fwrite($fp, $update);
			@fclose($fp);
		}
	}
	//get path
	$exp = explode("/", $template_name);
	unset($exp[count($exp)-1]);
	$folder_path = implode("/", $exp);
	//show output
	echo "<h2>Theme Editing</h2>\n";
	if($oiopub_set->template != $oiopub_set->platform) {
		echo "<font color='red'><b>Important:</b></font> if you modify the default theme, any changes will be over-written during an upgrade. It is recommended you create a <a href='http://forum.oiopublisher.com/discussion/520/customising-the-ad-manager/#Item_3' target='_blank'>custom theme</a> first.\n";
	} else {
		echo "<font color='red'><b>Important:</b></font> remember that any 'core' templates you edit now will be over-written if performing an automatic upgrade to a new version.\n";
	}
	echo "<br /><br />\n";
	if(!empty($template_name) && $template_exists && !$template_writable) {
		echo "<div style='margin-bottom:10px;'>\n";
		echo "<font color='red'><b>You must make this template writable (permission 666) in order to edit it!</b> (see '" . $folder_path . "' folder)</font>\n";
		echo "</div>\n";
	}
	echo "<form method='post' action='" . $oiopub_set->request_uri . "'>\n";
	echo "<input type='hidden' name='csrf' value='" . oiopub_csrf_token() . "' />\n";
	echo "<input type='hidden' name='process' value='edit' />\n";
	echo "<table width='100%'>\n";
	echo "<tr><td valign='top' rowspan='2' style='padding:10px; background:#F1F1F1; width:150px; line-height:20px;'>\n";
	if($oiopub_set->template != $oiopub_set->platform) {
		echo "<b>Theme Templates:</b>\n";
		echo "<br />\n";
		$array = array( "style.css", "header.tpl", "footer.tpl" );
		foreach($array as $f) {
			$fp = "templates/" . $oiopub_set->template . "/" . $f;
			if($fp == $template_name) {
				$style = " style='color:red;'";
			} else {
				$style = "";
			}
			echo "<a" . $style . " href='admin.php?page=oiopub-opts.php&opt=templates&t=" . $fp . "'>" . $f . "</a><br />\n";
		}
		echo "<br />\n";
	}
	if($oiopub_set->theme_mode == 1) {
		echo "<b>Core Templates:</b>\n";
		echo "<br />\n";
		$core_dir = $oiopub_set->folder_dir . "/templates/core/";
		$files = oiopub_admin_templates_readdir($core_dir, 1, array("", ".", "..", ".htaccess"));
		if(!empty($files)) {
			foreach($files as $f) {
				$fp = "templates/core/" . $f;
				if($fp == $template_name) {
					$style = " style='color:red;'";
				} else {
					$style = "";
				}
				echo "<a" . $style . " href='admin.php?page=oiopub-opts.php&opt=templates&t=" . $fp . "'>" . $f . "</a><br />\n";
			}
		}
		echo "<br />\n";
		echo "<b>Module Templates:</b>\n";
		echo "<br />\n";
		if(!empty($oiopub_module->modcount)) {
			foreach($oiopub_module->modcount as $mod) {
				if($mod[4] == 1) {
					$dir = $oiopub_set->modules_dir . "/" . $mod[0] . "/templates/";
					$files = oiopub_admin_templates_readdir($dir, 1, array("", ".", "..", ".htaccess", "main.tpl"));
					if(!empty($files)) {
						foreach($files as $f) {
							$fp = "modules/" . $mod[0] . "/templates/" . $f;
							if($fp == $template_name) {
								$style = " style='color:red;'";
							} else {
								$style = "";
							}
							echo "<a" . $style . " href='admin.php?page=oiopub-opts.php&opt=templates&t=" . $fp . "'>" . $f . "</a><br />\n";
						}
					}
				}
			}
		}
	}	
	echo "</td><td width='10'>\n";
	echo "</td><td valign='top' style='border:1px solid #C3C3C3; padding:5px;'>\n";
	if(empty($template_name) && $template_exists) {
		echo "<br /><br /><br />\n";
		echo "<center><b>Please select a template from the left hand menu to edit</b></center>\n";
	} elseif($template_exists) {
		echo "<textarea name='oio_content' style='width:100%; height:400px; border:0px;'>" . htmlspecialchars(@file_get_contents($template_path)) .  "</textarea>\n";
	} else {
		echo "<br /><br /><br />\n";
		echo "<center><b>Template could not be found!</b></center>\n";
	}
	echo "</td></tr>\n";
	if(!empty($template_name) && $template_exists) {
		echo "<tr><td></td><td style='padding-top:10px;'>\n";
		if($template_writable) {
			echo "<input type='submit' value='Update Template' />\n";
		} else {
			echo "<font color='red'><b>You must make this template writable (permission 666) in order to edit it!</b> (see '" . $folder_path . "' folder)</font>\n";
		}
		echo "</td></tr>\n";
	}
	echo "</table>\n";
	echo "</form>\n";
	echo "<br /><br />\n";
}

//read directory
function oiopub_admin_templates_readdir($dir, $find_files=1, $forbidden=array()) {
	$res = array();
	$handle = @opendir($dir);
	while($file = @readdir($handle)) {
		if(!in_array($file, $forbidden)) {
			if(($find_files == 1 && !is_dir($dir.$file)) || ($find_files == 0 && is_dir($dir.$file))) {
				$res[] = $file;
			}
		}
	}
	@closedir($handle);
	sort($res);
	return $res;
}

?>