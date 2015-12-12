<?php if($item->misc['error'] && !isset($_POST['suppress_errors'])) { ?>
<table align="center" border="0" style="margin-top:30px;">
	<tr>
		<td align="left">
			<ul style="margin:0px; padding:0px;">
			<?php echo $item->misc['info']; ?>
			</ul>
		</td>
	</tr>
</table>
<?php } ?>
<form name="type" id="type" action="<?php echo $oiopub_set->request_uri; ?>" method="get">
<input type="hidden" name="do" value="inline" />
<table align="center" width="550" class="start" border="0" cellspacing="4" cellpadding="4">
	<tr>
		<td valign="top" width="120"><b><?php echo __oio("Ad Type"); ?>:</b></td>
		<td><?php echo oiopub_zone_select("type", $item->item_type, 200, 1); ?>&nbsp;[<a href="javascript://" title="<?php echo __oio("Select the inline ad you'd like to purchase"); ?>"><b>?</b></a>]</td>
	</tr>
<?php if($item->item_type > 0) { ?>
<?php if($sub_id = oiopub_subid($item->item_channel, $item->item_type)) { ?>
	<tr>
		<td valign="top"><b><?php echo __oio("Sub ID"); ?>:</b></td>
		<td><?php echo $sub_id; ?></td>
	</tr>
<?php } ?>
<?php if($item->item_type != 4) { ?>
	<tr>
		<td valign="top"><b><?php echo __oio("Ad Size"); ?>:</b></td>
		<td><?php echo $oiopub_set->{$iz}['width']; ?> x <?php echo $oiopub_set->{$iz}['height']; ?></td>
	</tr>
<?php } ?>
<?php if($item->item_type == 2) { ?>
	<tr>
		<td valign="top"><b><?php echo __oio("Ad Formats"); ?>:</b></td>
		<td><?php echo implode(", ", $oiopub_purchase->allowed_exts); ?></td>
	</tr>
<?php } ?>
	<tr>
		<td valign="top"><b><?php echo __oio("Availability"); ?>:</b></td>
		<td><?php echo oiopub_zone_available($item->item_type) ?></td>
	</tr>
<?php } ?>
</table>
</form>
<?php if($item->item_type > 0) { ?>
<form name="process" id="process" action="<?php echo $oiopub_set->request_uri; ?>" method="post" enctype="multipart/form-data">
<input type="hidden" name="process" value="yes" />
<input type="hidden" name="oio_type" value="<?php echo $item->item_type; ?>" />
<table align="center" width="550" class="start" border="0" cellspacing="4" cellpadding="4">
	<tr>
		<td width="120"><b><?php echo __oio("Your Name"); ?>:</b></td>
		<td><input tabindex="2" type="text" name="oio_name" size="40" value="<?php echo $item->adv_name; ?>" />&nbsp;[<a href="javascript://" title="<?php echo __oio("Enter your full name"); ?>"><b>?</b></a>]</td>
	</tr>
	<tr>
		<td><b><?php echo __oio("Email Address"); ?>:</b></td>
		<td><input tabindex="3" type="text" name="oio_email" size="40" value="<?php echo $item->adv_email; ?>" />&nbsp;[<a href="javascript://" title="<?php echo __oio("Enter your email address"); ?>"><b>?</b></a>]</td>
	</tr>
	<tr>
		<td colspan="2" height="20"></td>
	</tr>
	<tr>
		<td><b><?php echo __oio("Payment"); ?>:</b></td>
		<?php if($item->payment_status == 1) { ?>
		<td><?php echo $item->payment_processor; ?></td>
		<?php } else { ?>
		<td><?php echo oiopub_dropmenu_kv($oiopub_set->arr_payment, "oio_paymethod", $item->payment_processor, 200, "add_field(\"process\", \"hidden\", \"suppress_errors\", \"suppress\", \"1\"); document.process.submit();", 0, 4); ?>&nbsp;[<a href="javascript://" title="<?php echo __oio("Select your preferred payment method"); ?>"><b>?</b></a>]</td>
		<?php } ?>
	</tr>
	<tr>
		<td><b><?php echo __oio("Pricing"); ?>:</b></td>
		<?php if($item->payment_status == 1) { ?>
		<td><?php echo $item->payment_amount . " " . $item->payment_currency; ?> (<?php echo $item->item_duration > 0 ? $item->item_duration . " " . $item->item_model : "permanent"; ?>)</td>
		<?php } else { ?>
		<td><?php echo oiopub_price_select("oio_pricing", $item->item_type, 200, 5); ?>&nbsp;[<a href="javascript://" title="<?php echo __oio("Select your preferred price"); ?>"><b>?</b></a>]</td>
		<?php } ?>
	</tr>
<?php if($oiopub_set->general_set['subscription'] == 1 && $oiopub_set->{$iz}['model'] == 'days' && $item->payment_status != 1) { ?>
	<tr>
		<td><b><?php echo __oio("Subscription"); ?>:</b></td>
		<td><?php echo oiopub_dropmenu_kv($oiopub_set->arr_yesno, "oio_subscription", $item->item_subscription, 200, "", 0, 6); ?>&nbsp;[<a href="javascript://" title="<?php echo __oio("If subscribing, your purchase will be automatically renewed"); ?>"><b>?</b></a>]</td>
	</tr>
<?php } ?>
	<tr>
		<td colspan="2" height="20"></td>
	</tr>
<?php if($item->item_type == 2 && $oiopub_set->general_set['upload'] == 1) { ?>
	<tr>
		<td><b><?php echo $ad_name . " " . __oio("Upload"); ?>:</b></td>
		<td>
			<input tabindex="7" type="file" name="oio_url" size="40" />&nbsp;[<a href="javascript://" title="<?php echo __oio("The banner image file to upload"); ?>"><b>?</b></a>]
			<?php if($item->item_status != 0) { ?>
			<br />
			<a href="<?php echo $item->item_url; ?>" target="_blank"><?php echo __oio("View existing banner"); ?></a>
			<?php } ?>
		</td>
	</tr>
<?php } else { ?>
	<tr>
		<td><b><?php echo $ad_name . " " . __oio("URL"); ?>:</b></td>
		<td><input tabindex="8" type="text" name="oio_url" size="40" value="<?php echo $item->item_url; ?>" />&nbsp;[<a href="javascript://" title="<?php echo __oio("The URL of the ad to display"); ?>"><b>?</b></a>]</td>
	</tr>
<?php } ?>
<?php if($item->item_type == 4) { ?>
	<tr>
		<td><b>Tooltip Text:</b></td>
		<td><input tabindex="9" type="text" name="oio_tooltip" size="40" value="<?php echo $item->item_tooltip; ?>" />&nbsp;[<a href="javascript://" title="<?php echo __oio("Description text, displayed on mouseover"); ?>"><b>?</b></a>]</td>
	</tr>
<?php } ?>
<?php if($item->item_type == 2) { ?>
	<tr>
		<td><b><?php echo __oio("Website URL"); ?>:</b></td>
		<td><input tabindex="10" type="text" name="oio_page" size="40" value="<?php echo $item->item_page; ?>" />&nbsp;[<a href="javascript://" title="<?php echo __oio("The target website your ad will point to"); ?>"><b>?</b></a>]</td>
	</tr>
	<tr>
		<td><b><?php echo __oio("Alt Text"); ?>:</b></td>
		<td><input tabindex="11" type="text" name="oio_tooltip" size="40" value="<?php echo $item->item_tooltip; ?>" />&nbsp;[<a href="javascript://" title="<?php echo __oio("Optional descriptive text to appear in the image 'alt' tag"); ?>"><b>?</b></a>]</td>
	</tr>
<?php } ?>
<?php if(($item->item_type == 2 || $item->item_type == 4) && $oiopub_set->{$iz}['nofollow'] == 2 && $item->payment_status != 1) { ?>
	<tr>
		<td><b><?php echo __oio("Use Nofollow"); ?>:</b></td>
		<td><?php echo oiopub_dropmenu_kv($oiopub_set->arr_yesno, "oio_nofollow", $item->item_nofollow, 90, "", 0, 12); ?>&nbsp;[<a href="javascript://" title="<?php echo __oio("Select whether to use the nofollow attribute"); ?>"><b>?</b></a>]</td>
	</tr>
	<tr>
		<td></td>
		<td>(<?php echo __oio("%s added to price if nofollow tag not used", array($oiopub_set->{$iz}['nfboost'] . "%")); ?>)</td>
	</tr>
<?php } ?>
<?php if($item->item_type == 4) { ?>
	<tr>
		<td colspan="2" height="20"></td>
	</tr>
	<tr>
		<td></td>
		<td>
			(1) <a href="keywords.php" target="_blank" rel="nofollow"><?php echo __oio("Perform a keyword search"); ?></a>
			<br />
			(2) <?php echo __oio("Add the resulting post ID below, and your keyword"); ?>
		</td>
	</tr>
	<tr>
		<td><b><?php echo __oio("Post ID"); ?>:</b></td>
		<td>
			<input tabindex="13" type="text" name="oio_postid" size="5" value="<?php echo $item->post_id; ?>" />&nbsp;[<a href="javascript://" title="<?php echo __oio("Get the post ID by performing the keyword search above"); ?>"><b>?</b></a>]
		</td>
	</tr>
	<tr>
		<td><b><?php echo __oio("Keyword"); ?>:</b></td>
		<td>
			<input tabindex="14" type="text" name="oio_postphrase" size="30" value="<?php echo $item->post_phrase; ?>" />&nbsp;[<a href="javascript://" title="<?php echo __oio("Pick a keyword or phrase that will contain your link"); ?>"><b>?</b></a>]
		</td>
	</tr>
<?php } ?>
	<tr>
		<td colspan="2" height="20"></td>
	</tr>
<?php if($oiopub_set->general_set['security_question'] == 1) { ?>
	<tr>
		<td><b><?php echo __oio("Security"); ?>:</b></td>
		<td><?php echo $item->captcha['question'] ?>&nbsp;&nbsp;<input tabindex="15" type="text" name="oio_security" size="10" />&nbsp;&nbsp;[<a href="javascript://" title="<?php echo __oio("The security question is used to stop spam bots"); ?>"><b>?</b></a>]</td>
	</tr>
	<tr>
		<td colspan="2" height="10"></td>
	</tr>
<?php } ?>
	<tr>
		<td></td>
		<td><input tabindex="16" type="submit" value="<?php echo __oio("Continue to Checkout"); ?>" class="oiopaymentbutton" /></td>
	</tr>
</table>
</form>
<?php } else { ?>
<table align="center" border="0" width="550" class="start">
<tr><td>
<h3><?php echo __oio("Inline Ad Pricing"); ?></h3>
<table id="inline-chart" class="chart" width="100%" border="0" cellspacing="2" cellpadding="2">
<?php echo $oiopub_purchase->chart(3, "#E0EEEE", "#FFFFFF"); ?>
</table>
</td></tr>
</table>
<?php } ?>
<?php $oiopub_hook->fire('purchase_form_footer'); ?>
