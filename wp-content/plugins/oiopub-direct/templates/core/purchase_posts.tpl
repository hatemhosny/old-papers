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
<input type="hidden" name="do" value="post" />
<table align="center" width="550" class="start" border="0" cellspacing="4" cellpadding="4">
	<tr>
		<td valign="top" width="120"><b><?php echo __oio("Post Author"); ?>:</b></td>
		<td><?php echo oiopub_zone_select("author", $item->post_author, 200, 1); ?>&nbsp;[<a href="javascript://" title="<?php echo __oio("Select the post author"); ?>"><b>?</b></a>]</td>
	</tr>
<?php if($item->post_author > 0) { ?>
<?php if($sub_id = oiopub_subid($item->item_channel, $item->post_author)) { ?>
	<tr>
		<td valign="top"><b><?php echo __oio("Sub ID"); ?>:</b></td>
		<td><?php echo $sub_id; ?></td>
	</tr>
<?php } ?>
	<tr>
		<td valign="top"><b><?php echo __oio("Availability"); ?>:</b></td>
		<td><?php echo oiopub_zone_available($item->post_author) ?></td>
	</tr>
<?php } ?>
</table>
</form>
<?php if($item->post_author > 0) { ?>
<form name="process" id="process" action="<?php echo $oiopub_set->request_uri; ?>" method="post" enctype="multipart/form-data">
<input type="hidden" name="process" value="yes" />
<input type="hidden" name="oio_author" value="<?php echo $item->post_author; ?>" />
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
<?php if($item->post_author != 2 || $oiopub_set->posts['price_adv'] > 0) { ?>
	<tr>
		<td><b><?php echo __oio("Payment"); ?>:</b></td>
		<?php if($item->payment_status == 1) { ?>
		<td><?php echo $item->payment_processor; ?></td>
		<?php } else { ?>
		<td><?php echo oiopub_dropmenu_kv($oiopub_set->arr_payment, "oio_paymethod", $item->payment_processor, 200, "add_field(\"process\", \"hidden\", \"suppress_errors\", \"suppress\", \"1\"); document.process.submit();", 0, 4); ?>&nbsp;[<a href="javascript://" title="<?php echo __oio("Select your preferred payment method"); ?>"><b>?</b></a>]</td>
		<?php } ?>
	</tr>
<?php } ?>
	<tr>
		<td><b><?php echo __oio("Pricing"); ?>:</b></td>
		<td><?php echo oiopub_price_select(); ?></td>
	</tr>
	<tr>
		<td colspan="2" height="20"></td>
	</tr>
	<tr>
		<td><b><?php echo __oio("Post Title"); ?>:</b></td>
		<td><input tabindex="6" type="text" name="oio_title" size="40" value="<?php echo $item->post['title']; ?>" />&nbsp;[<a href="javascript://" title="<?php echo __oio("The title of your post"); ?>"><b>?</b></a>]</td>
	</tr>
	<?php if($oiopub_set->posts['tags'] == 1) { ?>
	<tr>
		<td><b><?php echo __oio("Post Tags"); ?>:</b></td>
		<td><input tabindex="7" type="text" name="oio_tags" size="40" value="<?php echo $item->post['tags']; ?>" />&nbsp;[<a href="javascript://" title="<?php echo __oio("You can add keywords to your article through a tagging system, separate by commas"); ?>"><b>?</b></a>]</td>
	</tr>
	<?php } if(!empty($cat_array)) { ?>
	<tr>
		<td><b><?php echo __oio("Post Category"); ?>:</b></td>
		<td><?php echo oiopub_dropmenu_kv($cat_array, "oio_category", $item->post['category'], 200, 0, 6); ?>&nbsp;[<a href="javascript://" title="<?php echo __oio("Select the most appropriate category for the review"); ?>"><b>?</b></a>]</td>
	</tr>
	<?php } ?>
	<tr>
		<td colspan="2" height="20"></td>
	</tr>
	<tr>
		<td colspan="2">
			<?php if($item->post_author == 1) { ?>
			<b><?php echo __oio("Post Description"); ?>:</b> 
			<?php } else { ?>
			<b><?php echo __oio("Your Post"); ?>:</b> 
			<br />
			<i><?php echo __oio("must be at least %s words long", array( $oiopub_set->posts['min_words'] )); ?></i>
			<?php } ?>
			<br /><br />
			<?php
			if(function_exists('oiopub_wysiwyg')) {
				oiopub_wysiwyg(stripslashes($item->post['content']), 'wysiwyg', array(
					'textarea_name' => 'oio_content',
					'textarea_rows' => 9,
					'tabindex' => 8,
					'media_buttons' => false,
				));
			} else {
			?>			
			<textarea tabindex="8" name="oio_content" cols="60" rows="9"><?php echo stripslashes($item->post['content']); ?></textarea>
			<?php } ?>
		</td>
	</tr>
	<tr>
		<td colspan="2" height="20"></td>
	</tr>
<?php if($oiopub_set->general_set['security_question'] == 1) { ?>
	<tr>
		<td><b><?php echo __oio("Security"); ?>:</b></td>
		<td><?php echo $item->captcha['question'] ?>&nbsp;&nbsp;<input tabindex="9" type="text" name="oio_security" size="10" />&nbsp;&nbsp;[<a href="javascript://" title="<?php echo __oio("The security question is used to stop spam bots"); ?>"><b>?</b></a>]</td>
	</tr>
	<tr>
		<td colspan="2" height="10"></td>
	</tr>
<?php } ?>
	<tr>
		<td></td>
		<td><input tabindex="10" type="submit" value="<?php echo __oio("Continue to Checkout"); ?>" class="oiopaymentbutton" /></td>
	</tr>
</table>
</form>
<?php } else { ?>
<table align="center" border="0" width="550" class="start">
<tr><td>
<h3><?php echo __oio("Paid Review Pricing"); ?></h3>
<table id="post-chart" class="chart" width="100%" border="0" cellspacing="2" cellpadding="2">
<?php echo $oiopub_purchase->chart(1, "#E0EEEE", "#FFFFFF"); ?>
</table>
</td></tr>
</table>
<?php } ?>
<?php $oiopub_hook->fire('purchase_form_footer'); ?>
