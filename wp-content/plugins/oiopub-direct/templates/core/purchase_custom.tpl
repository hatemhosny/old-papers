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
<input type="hidden" name="do" value="custom" />
<table align="center" width="550" class="start" border="0" cellspacing="4" cellpadding="4">
	<tr>
		<td valign="top" width="120"><b><?php echo __oio("Select Item"); ?>:</b></td>
		<td><?php echo oiopub_zone_select("item", $item->item_type, 200, 1); ?>&nbsp;[<a href="javascript://" title="<?php echo __oio("Select the item you'd like to purchase"); ?>"><b>?</b></a>]</td>
	</tr>
<?php if($item->item_type > 0) { ?>
<?php if($sub_id = oiopub_subid($item->item_channel, $item->item_type)) { ?>
	<tr>
		<td valign="top"><b><?php echo __oio("Sub ID"); ?>:</b></td>
		<td><?php echo $sub_id; ?></td>
	</tr>
<?php } ?>
	<tr>
		<td valign="top"><b><?php echo __oio("Description"); ?>:</b></td>
		<td><?php echo (!empty($oiopub_set->{$cn}['info']) ? $oiopub_set->{$cn}['info'] : "N/A"); ?></td>
	</tr>
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
		<td><?php echo oiopub_price_select(); ?></td>
	</tr>
	<?php if($oiopub_set->general_set['subscription'] == 1 && $oiopub_set->{$cn}['duration'] > 0 && $item->item_model == 'days' && $item->payment_status != 1) { ?>
	<tr>
		<td><b><?php echo __oio("Subscription"); ?>:</b></td>
		<td><?php echo oiopub_dropmenu_kv($oiopub_set->arr_yesno, "oio_subscription", $item->item_subscription, 200, 0, 5); ?>&nbsp;[<a href="javascript://" title="<?php echo __oio("If subscribing, your purchase will be automatically renewed"); ?>"><b>?</b></a>]</td>
	</tr>
	<?php } ?>
	<tr>
		<td colspan="2" height="20"></td>
	</tr>
	<tr>
		<td colspan="2">
			<p style="margin-bottom:5px;"><b><?php echo __oio("Additional Details"); ?>:</b></p>
			<textarea tabindex="6" name="oio_notes" cols="50" rows="7"><?php echo $item->item_notes; ?></textarea>
		</td>
	</tr>
	<tr>
		<td colspan="2" height="20"></td>
	</tr>
<?php if($oiopub_set->general_set['security_question'] == 1) { ?>
	<tr>
		<td><b><?php echo __oio("Security"); ?>:</b></td>
		<td><?php echo $item->captcha['question'] ?>&nbsp;&nbsp;<input tabindex="7" type="text" name="oio_security" size="10" />&nbsp;&nbsp;[<a href="javascript://" title="<?php echo __oio("The security question is used to stop spam bots"); ?>"><b>?</b></a>]</td>
	</tr>
	<tr>
		<td colspan="2" height="10"></td>
	</tr>
<?php } ?>
	<tr>
		<td></td>
		<td><input tabindex="8" type="submit" value="<?php echo __oio("Continue to Checkout"); ?>" class="oiopaymentbutton" /></td>
	</tr>
</table>
</form>
<?php } else { ?>
<table align="center" border="0" width="550" class="start">
<tr><td>
<h3><?php echo __oio("Custom Item Pricing"); ?></h3>
<table id="custom-chart" class="chart" width="100%" border="0" cellspacing="2" cellpadding="2">
<?php echo $oiopub_purchase->chart(4, "#E0EEEE", "#FFFFFF"); ?>
</table>
</td></tr>
</table>
<?php } ?>
<?php $oiopub_hook->fire('purchase_form_footer'); ?>
