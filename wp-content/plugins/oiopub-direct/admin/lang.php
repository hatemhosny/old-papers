<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

//language editing
function oiopub_admin_lang() {
	global $oiopub_set;
	//php5 loaded?
	if(version_compare(PHP_VERSION, '5.0.0', '<')) {
		echo '<p>You must be running <b>php5</b> on your website in order to use the languages feature. You are currently running php4.</p>' . "\n";
		echo '<p>Please contact your web host to upgrade your version of php.</p>' . "\n";
		return;
	}
	//load languages
	include_once($oiopub_set->folder_dir . "/include/countries.php");
	//get lang codes
	$lang_codes = geoip_lang_list();
	//select language file
	oiopub_admin_lang_select($lang_codes);
	//edit language file
	oiopub_admin_lang_edit($lang_codes);
	//share language file
	oiopub_admin_lang_share($lang_codes);
}

//select language (admin)
function oiopub_admin_lang_select($codes) {
	global $oiopub_set;
	//set vars
	$file = oiopub_var('file', 'post');
	$active = oiopub_lang_get_active($codes);
	//set lang?
	if(isset($_POST['process']) && $_POST['process'] == "select" && $file) {
		oiopub_update_config('lang', $file);
		if($_POST['submit'] == "Edit Text") {
			echo '<meta http-equiv="refresh" content="0;url=admin.php?page=oiopub-opts.php&opt=lang&file=' . $file . '#file"/>' . "\n";
			exit();
		}
	}
	//html output
	echo '<h2>Current Language</h2>' . "\n";
	echo 'From here you can load an existing language file to over-ride the default (english) text.' . "\n";
	echo '<p style="margin:5px 0 20px 0; color:red;"><i>If a language is not in this list, it means there is no language file present. You can create one using the "Create / Edit Language File" option below.</i></p>' . "\n";
	echo '<form method="post" action="' . $oiopub_set->request_uri . '">' . "\n";
	echo '<input type="hidden" name="process" value="select" />' . "\n";
	echo '<input type="hidden" name="csrf" value="' . oiopub_csrf_token() . '" />' . "\n";
	echo oiopub_dropmenu_kv($active, "file", $oiopub_set->lang, 170);
	echo '&nbsp; <input type="submit" name="submit" value="Update" />' . "\n";
	if($oiopub_set->lang) {
		echo '&nbsp; <input type="submit" name="submit" value="Edit Text" />' . "\n";
	}
	echo '</form>' . "\n";
	echo '<br /><br />' . "\n";
}

//edit language (Admin)
function oiopub_admin_lang_edit($codes) {
	global $oiopub_set;
	//set vars
	$file = oiopub_var('file', 'get');
	$active = oiopub_lang_get_active($codes);
	//html output
	if(empty($file)) {
		echo '<h2>Create / Edit Language File</h2>' . "\n";
		echo 'To create a new language file, or edit an existing one, please select a language from the list below.' . "\n";
		echo '<br /><br />' . "\n";
		echo '<form method="get" action="' . $oiopub_set->request_uri . '">' . "\n";
		echo '<input type="hidden" name="page" value="oiopub-opts.php" />' . "\n";
		echo '<input type="hidden" name="opt" value="lang" />' . "\n";
		echo oiopub_dropmenu_kv(array( '' => "-- select --" ) + $active + array( '---' => '---' ) + $codes, "file", $file, 170);
		echo '&nbsp; <input type="submit" value="Select" />' . "\n";
		echo '</form>' . "\n";
		echo '<br /><br />' . "\n";
	} else {
		//set vars
		$error = '';
		$lang_tr = array();
		$lang_data = oiopub_lang_get_text($oiopub_set->folder_dir);
		//get lang file
		if(is_file($oiopub_set->lang_dir . "/" . $file . "_custom.php")) {
			$action = "Update";
			$lang_file = $oiopub_set->lang_dir . "/" . $file . "_custom.php";
		} else {
			$action = "Create";
			$lang_file = $oiopub_set->lang_dir . "/" . $file . ".php";
		}
		//update translation data?
		if(isset($_POST['process']) && $_POST['process'] == "translate") {
			//set lang file
			$lang_file = $oiopub_set->lang_dir . "/" . $file . "_custom.php";
			//format data
			foreach($_POST['lang'] as $key=>$val) {
				if(!$val) continue;
				$lang_tr[stripslashes($key)] = stripslashes($val);
			}
			//anything to save?
			if(!$lang_tr) {
				@unlink($lang_file);
			} else {
				//write to file
				$res = @file_put_contents($lang_file, '<?php' . "\n\n" . 'return ' . var_export($lang_tr, true) . ';');
				//write failed?
				if($res === false) {
					$error = '<font color="red"><b>Unable to save language file. Please ensure that the OIO "lang" directory is writable.</b></font>';
				} else {
					echo '<meta http-equiv="refresh" content="0;url=' . $oiopub_set->request_uri . '#file" />' . "\n";
					exit();
				}
			}
		} elseif(is_file($lang_file)) {
			//get translations
			$lang_tr = include($lang_file);
		}
		//set action
		echo '<h2 id="file">Editing \'' . $codes[$file] . '\' Language (' . round((count($lang_tr) / count($lang_data)) * 100) . '% complete)</h2>' . "\n";
		echo 'Make sure that any %s placeholders are retained in your translation, otherwise the text shown to the user will not make sense.' . "\n";
		echo '<br /><br />' . "\n";
		echo '<form method="get" action="' . $oiopub_set->request_uri . '">' . "\n";
		echo '<input type="hidden" name="page" value="oiopub-opts.php" />' . "\n";
		echo '<input type="hidden" name="opt" value="lang" />' . "\n";
		echo oiopub_dropmenu_kv(array( '' => "-- select --" ) + $codes, "file", $file, 170);
		echo '&nbsp; <input type="submit" value="Select" />' . "\n";
		echo '</form>' . "\n";
		echo '<br />' . "\n";
		if($error) {
			echo '<br />' . "\n";
			echo $error . "\n";
		}
		echo '<form method="post" action="' . $oiopub_set->request_uri . '#file">' . "\n";
		echo '<input type="hidden" name="csrf" value="' . oiopub_csrf_token() . '" />' . "\n";
		echo '<input type="hidden" name="process" value="translate" />' . "\n";
		echo '<div class="submit"><input type="submit" value="' . $action . ' Custom Language File" /></div>' . "\n";
		echo '<table width="100%" border="0" cellpadding="0" cellspacing="0">' . "\n";
		echo '<tr><td style="width:40%; padding:5px;"><b>Default Text</b></td><td style="width:10%; padding:5px;"><b>Translated?</b></td><td style="width:50%; padding:5px;"><b>My Translation</b></td></tr>' . "\n";
		$background = "#f4f4f4";
		foreach($lang_data as $l) {
			$t = isset($lang_tr[$l]) ? $lang_tr[$l] : "";
			$style = 'padding:5px; background:' . $background . ';';
			echo '<tr><td style="' . $style . '">' . $l . '</td><td style="' . $style . '">' . ($t ? 'yes' : 'no') . '</td><td style="' . $style . '"><input type="text" name="lang[' . $l . ']" value="' . $t . '" style="width:100%;" /></td></tr>' . "\n";
			$background = $background == "#f4f4f4" ? "#e4e4e4" : "#f4f4f4";
		}
		echo '</table>'. "\n";
		echo '<div class="submit"><input type="submit" value="' . $action . ' Custom Language File" /></div>' . "\n";
		echo '</form>' . "\n";
		echo '<br /><br />' . "\n";
	}
}

//share language (admin)
function oiopub_admin_lang_share($codes) {
	global $oiopub_set;
	//set vars
	$error = '';
	$credits = oiopub_var('credits', 'post');
	$file = $file_org = oiopub_var('file', 'post');
	$active = oiopub_lang_get_active($codes, true);
	//format credits?
	if(!empty($credits)) {
		$credits = strip_tags($credits);
		$credits = str_replace("\r\n", "\n", $credits);
		$credits = trim($credits);
	}
	//send email?
	if(isset($_POST['process']) && $_POST['process'] == "share") {
		if($file && isset($active[$file])) {
			$file = $file . "_custom";
			$lang_file = $oiopub_set->lang_dir . "/" . $file . ".php";
			if(is_file($lang_file)) {
				$lang_tr = include($lang_file);
				$lang_data = oiopub_lang_get_text($oiopub_set->folder_dir);
				$ratio = round((count($lang_tr) / count($lang_data)) * 100);
				if($ratio < 80) {
					$error = '<font color="red"><b>Unable to accept language file at this time. Only ' . $ratio . '% has been translated, 80% required.</b></font>';
				} else {
					$details = "Website: " . $oiopub_set->site_url . "\nEmail: " . $oiopub_set->admin_mail . "\nCredits: " . $credits . "\n\n\n";
					oiopub_mail_client('admin@oiopublisher.com', "Language File - $active[$file_org]", $details . file_get_contents($lang_file), '', true);
					$error = '<font color="green"><b>Thanks! Language file sent to OIO admin</b></font>';
					$file = '';
				}
			}
		}
	}
	//html output
	echo '<h2 id="share">Share Language File?</h2>' . "\n";
	echo 'Choosing to share your language file will enable it to be distributed to other users in future updates to OIO. At least 80% of the text must be translated.' . "\n";
	if($error) {
		echo '<br /><br />' . "\n";
		echo $error . "\n";
	}
	echo '<br /><br />' . "\n";
	if($active) {
		echo '<form method="post" action="' . $oiopub_set->request_uri . '#share">' . "\n";
		echo '<input type="hidden" name="process" value="share" />' . "\n";
		echo '<input type="hidden" name="csrf" value="' . oiopub_csrf_token() . '" />' . "\n";
		echo  oiopub_dropmenu_kv(array( '' => "-- select --" ) + $active, "file", $file, 170);
		echo '&nbsp; <input type="submit" value="Share" />' . "\n";
		echo '<br /><br />' . "\n";
		echo 'Credit Text:' . "\n";
		echo '<p style="margin:3px 0 6px 0;"><i>You can use this to let others know who created the language file (optional)</i></p>' . "\n";
		echo '<textarea name="credits" rows="3" cols="40">' . $credits . '</textarea>' . "\n";
		echo '</form>' . "\n";
		echo '<br /><br />' . "\n";
	} else {
		echo '<i>The sharing option will only become available once you have created at least one language file of your own.</i>' . "\n";
		echo '<br /><br />' . "\n";
	}
}

//get active languages
function oiopub_lang_get_active($input, $custom=false) {
	global $oiopub_set;
	//set vars
	$res = array();
	//scan lang dir
	if($locales = glob($oiopub_set->lang_dir . "/*")) {
		foreach($locales as $l) {
			$l = str_replace('.php', '', pathinfo($l, PATHINFO_BASENAME));
			$exp = explode('_', $l, 2);
			if(!$custom || isset($exp[1])) {
				if(isset($input[$exp[0]])) {
					$res[$exp[0]] = $input[$exp[0]];
				}
			}
		}
	}
	//add english?
	if(!$custom && !isset($res['en'])) {
		$res['en'] = "English";
	}
	//sort
	asort($res);
	//return
	return $res;
}

//get translatable text
function oiopub_lang_get_text($dir, $func='__oio') {
	//set vars
	$result = array();
	//get files
	$files = new RecursiveDirectoryIterator($dir);
	$files = new RecursiveIteratorIterator($files);
	//loop through files
	foreach($files as $file) {
		//file info
		$info = pathinfo($file);
		//extension exists?
		if(!isset($info['extension'])) {
			$info['extension'] = "";
		}
		//valid file?
		if(!$file->isFile() || !$info['extension'] || $info['basename'][0] == ".") {
			continue;
		}
		//open file
		$data = "";
		$fp = $file->openFile();
		//loop through data
		while(!$fp->eof()) {
			$data .= $fp->fgets();
		}
		//check for function matches
		if($data && preg_match_all('/' . $func . '\((.*)\)\s?[\.|\;|\:|\,|\)]/Um', $data, $matches)) {
			//loop through matches
			foreach($matches[1] as $match) {
				//format name
				$match = explode(', array', $match);
				$match = trim(trim($match[0]), '"');
				//add to array?
				if($match && strpos($match, 'array(') === false && $match[0] !== '$') {
					$result[] = $match;
				}
			}
		}
	}
	//remove dupes
	$result = array_unique($result);
	//sort
	natcasesort($result);
	//return
	return $result;
}

?>