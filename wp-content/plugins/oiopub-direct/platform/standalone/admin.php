<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

/* STANDALONE ADMIN CLASS */

class oiopub_admin_standalone extends oiopub_admin {

	var $admin_id = 0;

	//init
	function oiopub_admin_standalone() {
		$this->init();
		$this->actions();
	}
	
	//actions
	function actions() {
		global $oiopub_hook, $oiopub_alerts;
		$oiopub_hook->add('admin_footer', array(&$oiopub_alerts, 'display'));
	}
	
	//admin display
	function display() {
		global $oiopub_set;
		//admin ID
		if(isset($_SESSION['oiopub']['id'])) {
			$this->admin_id = intval($_SESSION['oiopub']['id']);
		}
		//demo login?
		if($oiopub_set->demo && $this->admin_id == 0) {
			$_SESSION['oiopub']['id'] = $this->admin_id = 1;
			$_SESSION['oiopub']['rand'] = md5(md5($oiopub_set->hash) . session_id());
			@session_write_close();
		}
		//login required?
		if($this->admin_id <= 0) {
			if(isset($_GET['do']) && $_GET['do'] == "reset") {
				return $this->reset();
			} else {
				return $this->login();
			}
		}
		//logout requested?
		if(isset($_GET['do']) && $_GET['do'] == "logout") {
			return $this->logout();
		}
		//authentication check
		if(!oiopub_auth_check()) {
			return $this->logout();
		}
		//admin view
		return $this->main_view();
	}

	//login form
	function login() {
		global $oiopub_set;
		$info = '';
		//process login request
		if(isset($_POST['process']) && $_POST['process'] == "yes") {
			$username = oiopub_var('username', 'post');
			$password = md5(md5(oiopub_var('password', 'post')) . $oiopub_set->admin_user['salt']);
			if($oiopub_set->admin_user['username'] == $username) {
				if($oiopub_set->admin_user['password'] == $password) {
					$_SESSION['oiopub']['id'] = $this->admin_id = 1;
					$_SESSION['oiopub']['rand'] = md5(md5($oiopub_set->hash) . session_id());
					@session_write_close();
					header("Location: admin.php");
					exit();
				} else {
					$info = "<b>Incorrect Login Details</b>";
				}
			} else {
				$info = "<b>Incorrect Login Details</b>";
			}
		}
		//login check
		$_SESSION['oiopub']['process'] = $rand = oiopub_rand(8);
		//header
		$this->header_template();
		//login form
		echo "<div class='wrap'>\n";
		if(!empty($info)) {
			echo "<center><font color='red' size='+2'>" . $info . "</font></center>\n";
		} else {
			echo "<center><font color='blue' size='+2'><b>OIOpublisher Admin Login</b></font></center>\n";
		}
		echo "<br /><br />\n";
		echo "<form method='post' action='admin.php'>\n";
		echo "<input type='hidden' name='csrf' value='" . oiopub_csrf_token() . "' />\n";
		echo "<input type='hidden' name='process' value='yes' />\n";
		echo "<table align='center' border='0' cellspacing='4' cellpadding='4'>\n";
		echo "<tr><td><b>Username:</b></td><td><input type='text' name='username' size='30' value='' /></td></tr>\n";
		echo "<tr><td><b>Password:</b></td><td><input type='password' name='password' size='30' value='' /></td></tr>\n";
		echo "<tr><td></td><td><input type='submit' value='Login' /></td></tr>\n";
		echo "</table>\n";
		echo "</form>\n";
		echo "<div style='text-align:center; margin:20px 0 0 0;'>\n";
		echo "<a href='admin.php?do=reset'>Forgotten Password?</a>\n";
		echo "</div>\n";
		echo "</div>\n";
		//footer
		$this->footer_template();
		exit();
	}

	//admin logout
	function logout() {
		//get vars
		$time = time() - 42000;
		$session_name = session_name();
		//unset session data
		$_SESSION = array();
		//unset session cookie
		if(isset($_COOKIE[$session_name])) {
			setcookie($session_name, '', $time, '/');
		}
		//destroy session
		session_destroy();
		//redirect user
		header("Location: admin.php");
		exit();
	}
	
	//password reset
	function reset() {
		global $oiopub_set;
		$info = '';
		//reset password
		$admin_key = oiopub_var('key', 'get');
		if(isset($_GET['key'])) {
			if(isset($oiopub_set->admin_key) && !empty($admin_key) && $admin_key == $oiopub_set->admin_key) {
				//delete admin key
				oiopub_delete_config('admin_key');
				//set new password
				$password = oiopub_rand(8);
				$this->update_password($oiopub_set->admin_user['username'], $password);
				//send email
				$email = $oiopub_set->admin_mail;
				$subject = "Password Reset Complete - OIOpublisher Admin";
				$message = "A new OIOpublisher admin password was issued at " . $oiopub_set->site_url . "\n\nYour new login details are below:\n\nUsername: " . $oiopub_set->admin_user['username'] . "\nPassword: " . $password;
				oiopub_mail_client($email, $subject, $message);
				echo "Reset Complete, please check your email for your new password!\n";
				exit();
			} else {
				echo "Invalid Password Reset attempt!\n";
				exit();
			}
		}
		//process login request
		if(isset($_POST['process']) && $_POST['process'] == "yes") {
			$email = oiopub_var('email', 'post');
			if(!empty($email) && $oiopub_set->admin_mail == $email) {
				oiopub_update_config('admin_key', oiopub_rand(8));
				$subject = "Password Reset Request - OIOpublisher Admin";
				$message = "A request was made to reset the OIOpublisher admin password at " . $oiopub_set->site_url . "\n\nPlease click on the link below to complete the reset request:\n\n" . $oiopub_set->admin_url . "/admin.php?do=reset&key=" . $oiopub_set->admin_key;
				oiopub_mail_client($email, $subject, $message);
				$info = "<b>Please check your email!</b>";
			} else {
				$info = "<b>Incorrect Email Address</b>";
			}
		}
		//header
		$this->header_template();
		//login form
		echo "<div class='wrap'>\n";
		if(!empty($info)) {
			echo "<center><font color='red' size='+2'>" . $info . "</font></center>\n";
		} else {
			echo "<center><font color='blue' size='+2'><b>OIOpublisher Password Reset</b></font></center>\n";
		}
		echo "<br /><br />\n";
		echo "<form method='post' action='admin.php?do=reset'>\n";
		echo "<input type='hidden' name='csrf' value='" . oiopub_csrf_token() . "' />\n";
		echo "<input type='hidden' name='process' value='yes' />\n";
		echo "<table align='center' border='0' cellspacing='4' cellpadding='4'>\n";
		echo "<tr><td><b>Email:</b></td><td><input type='text' name='email' size='30' value='' /></td></tr>\n";
		echo "<tr><td></td><td><input type='submit' value='Send Password' /></td></tr>\n";
		echo "</table>\n";
		echo "</form>\n";
		echo "</div>\n";
		//footer
		$this->footer_template();
		exit();
	}

	//admin view
	function main_view() {
		//current page
		$current = oiopub_var('page', 'get');
		//header
		$this->header_template();
		//get content
		foreach($this->menu_pages() as $page) {
			if($page['file'] == $current) {
				$method = $page['method'];
				$this->$method();
				break;
			}
		}
		//footer
		$this->footer_template();
	}
	
	//load times
	function load_times() {
		global $oiopub_db;
		echo "<div style='margin-top:5px;'><i>Time: " . number_format(microtime(true) - OIOPUB_TIME_START, 5) . "s | DB: " . $oiopub_db->query_num . " calls</i></div>";
	}

	//admin header
	function header_template() {
		global $oiopub_set, $oiopub_hook;
		echo "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>\n";
		echo "<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>\n";
		echo "<head>\n";
		echo "<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />\n";
		echo "<meta name='robots' content='noindex,nofollow' />\n";
		echo "<title>OIOpublisher Direct - Admin</title>\n";
		echo "<link rel='stylesheet' type='text/css' href='" . $oiopub_set->plugin_url_org . "/images/style/admin.css' />\n";
		$oiopub_hook->fire('admin_head');
		echo "</head>\n";
		echo "<body>\n";
		echo "<div id='wrap'>\n";
		echo "<div id='header'>\n";
		if($this->admin_id > 0) {
			$this->menu();
		}
		echo "</div>\n";
	}

	//admin footer
	function footer_template() {
		global $oiopub_hook, $time_start;
		$time_end = microtime();
		echo "<div id='footer'>\n";
		echo "Powered by: <a href='http://www.oiopublisher.com' target='_blank'>OIOpublisher Direct</a>\n";
		echo $this->load_times();
		echo "</div>\n";
		echo "</div>\n";
		$oiopub_hook->fire('admin_footer');
		echo "</body>\n";
		echo "</html>\n";
	}

	//menu display
	function menu() {
		global $oiopub_set;
		//build html
		echo "<div class='menu'>\n";
		echo "<ul>\n";
		foreach($this->menu_pages() as $page) {
			$path = $page['file'] ? 'admin.php?page=' . $page['file'] : 'admin.php';
			echo '<li' . $this->menu_active($page['file']) . '><a href="' . $path . '">' . $page['text'] . '</a></li>' . "\n";
		}
		echo "</ul>\n";
		echo "<div class='menu-login'>\n";
		echo "<b>OIOpublisher:</b> &nbsp;<a href='admin.php?do=logout'>Logout</a>\n";
		echo "</div>\n";
		echo "</div>\n";
	}

	//menu check
	function menu_active($page='') {
		if(isset($_GET['page']) && $_GET['page'] == $page) {
			return " id='menu-active'";
		}
		return false;
	}
	
	//update password
	function update_password($username, $password) {
		global $oiopub_set;
		//get vars
		$username = empty($username) ? $oiopub_set->admin_user['username'] : $username;
		$password = empty($password) ? $oiopub_set->admin_user['username'] : $password;
		//add vars to array
		$array['username'] = $username;
		$array['salt'] = oiopub_rand(6);
		$array['password'] = md5(md5($password) . $array['salt']);
		//update vars
		oiopub_update_config('admin_user', $array);
	}	
	

}

?>