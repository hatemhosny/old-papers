<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

//define vars
define('OIOPUB_INSTALL', 1);
define('OIOPUB_ADMIN', 1);
define('OIOPUB_PLATFORM', 'standalone');

//noindex header
header("X-Robots-Tag: noindex, nofollow", true);

//init
include_once(str_replace('\\', '/', dirname(__FILE__)) . "/index.php");

//clear
$info = '';
$site = '';
$email = '';
$username = '';
$migration = false;
$is_wordpress = (bool) (strpos(__FILE__, '/plugins/') !== false);

//already installed
if(oiopub_install_check()) {
	if($is_wordpress || oiopub_get_config('admin_user')) {
		echo "OIOpublisher Direct has already been installed.\n";
		echo "<br /><br />\n";
		echo "<a href='" . $oiopub_set->plugin_url . "/admin.php'>Admin Login</a>";
		die();
	} else {
		$migration = true;
	}
}

//start session
oiopub_session_start();

//process install request
if(isset($_POST['process']) && $_POST['process'] == "yes") {
	$email = oiopub_clean($_POST['email']);
	$username = oiopub_clean($_POST['username']);
	$password = oiopub_clean($_POST['password']);
	$site = oiopub_clean($_POST['site']);
	if(!empty($username) && !empty($password)) {
		if(oiopub_validate_email($email)) {
			if(!empty($site)) {	
				//install script?
				if($migration === false) {
					oiopub_install_wrapper();
				}
				//set username / password
				$oiopub_admin->update_password($username, $password);
				//update other options
				oiopub_update_config('site_name', $site);
				oiopub_update_config('admin_mail', $email);
				//set admin session
				//@session_regenerate_id();
				$_SESSION['oiopub']['id'] = 1;
				$_SESSION['oiopub']['rand'] = md5(md5($oiopub_set->hash) . session_id());
				//echo $oiopub_set->hash . " - " . session_id(); exit();
				@session_write_close();
				//redirect user
				header("Location: admin.php?welcome=1");
				exit();
			} else {
				$info = "<font color='red'><b>Please enter a website name</b></font>";
			}
		} else {
			$info = "<font color='red'><b>Please enter a valid email address!</b></font>";
		}
	} else {
		$info = "<font color='red'><b>Please enter a valid username and password!</b></font>";
	}
}

//output
echo $oiopub_admin->header_template();
echo "<div class='wrap'>\n";
echo "<h1 style='text-align:center;'>OIOpublisher Installation Wizard</h1>\n";
if(!empty($info)) {
	echo "<br /><center>" . $info . "</center><br />\n";
}
echo "<br /><br />\n";
echo "<table align='center' border='0' cellspacing='4' cellpadding='4'>\n";
echo "<tr><td>\n";
echo "<font color='blue'>(1) Enter a name for your installation (eg. My Website Ads)</font>";
echo "</td></tr>\n";
echo "<tr><td>\n";
echo "<form method='post' action='install.php'>\n";
echo "<input type='hidden' name='csrf' value='" . oiopub_csrf_token() . "' />\n";
echo "<input type='hidden' name='process' value='yes' />\n";
echo "<table align='center' border='0' cellspacing='4' cellpadding='4'>\n";
echo "<tr><td width='90'><b>Install:</b></td><td><input type='text' name='site' size='30' value='" . $site . "' /></td></tr>\n";
echo "</table>\n";
echo "</td></tr>\n";
echo "<tr><td height='10'></td></tr>\n";
echo "<tr><td>\n";
echo "<font color='blue'>(2) Enter an admin contact email address</font>";
echo "</td></tr>\n";
echo "<tr><td>\n";
echo "<table align='center' border='0' cellspacing='4' cellpadding='4'>\n";
echo "<tr><td width='90'><b>Email:</b></td><td><input type='text' name='email' size='30' value='" . $email . "' /></td></tr>\n";
echo "</table>\n";
echo "</td></tr>\n";
echo "<tr><td height='10'></td></tr>\n";
echo "<tr><td>\n";
echo "<font color='blue'>(3) Enter an admin username and password</font>";
echo "</td></tr>\n";
echo "<tr><td>\n";
echo "<table align='center' border='0' cellspacing='4' cellpadding='4'>\n";
echo "<tr><td width='90'><b>Username:</b></td><td><input type='text' name='username' size='30' value='" . $username . "' /></td></tr>\n";
echo "<tr><td><b>Password:</b></td><td><input type='password' name='password' size='30' value='' /></td></tr>\n";
echo "<tr><td height='10' colspan='2'></td></tr>\n";
echo "<tr><td></td><td><input type='submit' value='Finish Installation' /></td></tr>\n";
echo "</table>\n";
echo "</form>\n";
echo "</td></tr>\n";
echo "</table>\n";
echo "</div>\n";
echo $oiopub_admin->footer_template();

?>