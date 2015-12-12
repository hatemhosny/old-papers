<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

//email storage
function oiopub_admin_emails() {
	global $oiopub_set;
	if(isset($_POST['email_store'])) {
		oiopub_update_config('mailsubject_1', oiopub_email_storage($_POST['oiopub_mailsubject_1']));
		oiopub_update_config('mailmessage_1', oiopub_email_storage($_POST['oiopub_mailmessage_1']));
		oiopub_update_config('mailsubject_2', oiopub_email_storage($_POST['oiopub_mailsubject_2']));
		oiopub_update_config('mailmessage_2', oiopub_email_storage($_POST['oiopub_mailmessage_2']));
		oiopub_update_config('mailsubject_3', oiopub_email_storage($_POST['oiopub_mailsubject_3']));
		oiopub_update_config('mailmessage_3', oiopub_email_storage($_POST['oiopub_mailmessage_3']));
		oiopub_update_config('mailsubject_4', oiopub_email_storage($_POST['oiopub_mailsubject_4']));
		oiopub_update_config('mailmessage_4', oiopub_email_storage($_POST['oiopub_mailmessage_4']));
		oiopub_update_config('mailsubject_5', oiopub_email_storage($_POST['oiopub_mailsubject_5']));
		oiopub_update_config('mailmessage_5', oiopub_email_storage($_POST['oiopub_mailmessage_5']));
		oiopub_update_config('mailsubject_6', oiopub_email_storage($_POST['oiopub_mailsubject_6']));
		oiopub_update_config('mailmessage_6', oiopub_email_storage($_POST['oiopub_mailmessage_6']));
		oiopub_update_config('mailsubject_7', oiopub_email_storage($_POST['oiopub_mailsubject_7']));
		oiopub_update_config('mailmessage_7', oiopub_email_storage($_POST['oiopub_mailmessage_7']));
		oiopub_update_config('mailsubject_8', oiopub_email_storage($_POST['oiopub_mailsubject_8']));
		oiopub_update_config('mailmessage_8', oiopub_email_storage($_POST['oiopub_mailmessage_8']));
		oiopub_update_config('mailsubject_9', oiopub_email_storage($_POST['oiopub_mailsubject_9']));
		oiopub_update_config('mailmessage_9', oiopub_email_storage($_POST['oiopub_mailmessage_9']));
		echo "<meta http-equiv=\"refresh\" content=\"0\" />\n";
	}
	oiopub_admin_email_restore();
	echo "<h2>Email Templates</h2>\n";
	echo "<form action=\"" . oiopub_clean($_SERVER["REQUEST_URI"]) . "\" method=\"post\">\n";
	echo "<input type='hidden' name='csrf' value='" . oiopub_csrf_token() . "' />\n";
	echo "<input type=\"hidden\" name=\"email_store\" />\n";
	echo "<span id='1'></span>\n";
	echo "<h3>Purchase Submitted (sent before coupon entered)</h3>\n";
	oiopub_admin_email_textarea(1, $oiopub_set->mailsubject_1, oiopub_email_readable($oiopub_set->mailmessage_1));
	echo "<span id='2'></span>\n";
	echo "<h3>Purchase Approved</h3>\n";
	oiopub_admin_email_textarea(2, $oiopub_set->mailsubject_2, oiopub_email_readable($oiopub_set->mailmessage_2));
	echo "<span id='3'></span>\n";
	echo "<h3>Purchase Rejected</h3>\n";
	oiopub_admin_email_textarea(3, $oiopub_set->mailsubject_3, oiopub_email_readable($oiopub_set->mailmessage_3));
	echo "<span id='4'></span>\n";
	echo "<h3>Purchase Payment Reminder</h3>\n";
	oiopub_admin_email_textarea(4, $oiopub_set->mailsubject_4, oiopub_email_readable($oiopub_set->mailmessage_4));
	echo "<span id='9'></span>\n";
	echo "<h3>'Free' purchase confirmation (sent if 100% off coupon used)</h3>\n";
	oiopub_admin_email_textarea(9, $oiopub_set->mailsubject_9, oiopub_email_readable($oiopub_set->mailmessage_9));
	echo "<span id='5'></span>\n";
	echo "<h3>Purchase Expiry</h3>\n";
	oiopub_admin_email_textarea(5, $oiopub_set->mailsubject_5, oiopub_email_readable($oiopub_set->mailmessage_5));
	echo "<span id='6'></span>\n";
	echo "<h3>Purchase Renewal</h3>\n";
	oiopub_admin_email_textarea(6, $oiopub_set->mailsubject_6, oiopub_email_readable($oiopub_set->mailmessage_6));
	echo "<span id='7'></span>\n";
	echo "<h3>Purchase Published</h3>\n";
	oiopub_admin_email_textarea(7, $oiopub_set->mailsubject_7, oiopub_email_readable($oiopub_set->mailmessage_7));
	echo "<span id='8'></span>\n";
	echo "<h3>Purchase Promoted from Queue</h3>\n";
	oiopub_admin_email_textarea(8, $oiopub_set->mailsubject_8, oiopub_email_readable($oiopub_set->mailmessage_8));
	echo "</form>\n";
}

//email restore
function oiopub_admin_email_restore() {
	global $oiopub_set;
	if(isset($_POST['email_restore'])) {
		oiopub_mail_templates(2);
		echo "<meta http-equiv=\"refresh\" content=\"0\" />\n";
	}
	echo "<h2>Restore Defaults</h2>\n";
	echo "<form action=\"" . oiopub_clean($_SERVER["REQUEST_URI"]) . "\" method=\"post\">\n";
	echo "<input type='hidden' name='csrf' value='" . oiopub_csrf_token() . "' />\n";
	echo "<input type=\"hidden\" name=\"email_restore\" />\n";
	echo "<table width=\"100%\" border=\"0\">\n";
	echo "<tr><td>\n";
	echo "<b>Restore Default Templates:</b>\n";
	echo "<br /><br />\n";
	echo "<i>restores email templates to their default installation settings</i>\n";
	echo "</td><td align=\"right\">\n";
	echo "<div class=\"submit\"><input type=\"submit\" value=\"Restore Defaults\" /></div>\n";
	echo "</td></tr>\n";
	echo "</table>\n";
	echo "</form>\n";
	echo "<br /><br />\n";
}

//email textarea
function oiopub_admin_email_textarea($id, $subject='', $message='') {
	echo "<input type=\"text\" name=\"oiopub_mailsubject_$id\" size=\"90\" value=\"" . $subject . "\" />\n";
	echo "<br /><br />\n";
	echo "<textarea name=\"oiopub_mailmessage_$id\" rows=\"10\" cols=\"87\">" . $message . "</textarea>\n";
	echo "<br />\n";
	echo "<div class=\"submit\"><input type=\"submit\" value=\"Update Email Templates\" /></div>\n";
	echo "<br />\n";
}

?>