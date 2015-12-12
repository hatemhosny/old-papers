<script type="text/javascript">
<!--
function typechange(box) {
	<?php
	echo "	if(box.value == 4) {\n";
	echo "		showdiv('inline-link', 'block');\n";
	echo "		showdiv('inline-ad', 'none');\n";
	echo "	} else {\n";
	echo "		showdiv('inline-ad', 'block');\n";
	echo "		showdiv('inline-link', 'none');\n";	
	echo "	}\n";
	?>
}
function setChargingModel(el) {
	document.getElementById('chargingModel').innerHTML = el.value.charAt(0).toUpperCase() + el.value.slice(1);
}
//-->
</script>

<?php
if(!empty($message)) {
	echo "<br />\n";
	echo "<center>" . $message . "</center>\n";
	echo "<br /><br />\n";
}
?>

<form action="<?php echo $oiopub_set->request_uri; ?>" method="post" enctype="multipart/form-data">
<input type="hidden" name="csrf" value="<?php echo oiopub_csrf_token(); ?>" />
<input type="hidden" name="process" value="yes" />
<table align="center" class="start" width="550" cellspacing="4" cellpadding="4" style="margin-top:0px;">
	<tr>
		<td width="140"><b>Purchase Code:</b></td>
		<td><?php echo (empty($item->rand_id) ? "N/A" : $item->rand_id); ?></td>
	</tr>
	<tr>
		<td><b>Advertiser Name:</b></td>
		<td><input type="text" name="name" size="40" value="<?php echo $item->adv_name; ?>" /></td>
	</tr>
	<tr>
		<td><b>Advertiser Email:</b></td>
		<td><input type="text" name="email" size="40" value="<?php echo $item->adv_email; ?>" /></td>
	</tr>
	<tr>
		<td><b>Purchase Type:</b></td>
		<td><?php echo oiopub_dropmenu_kv($adtype_array, "adtype", $item->item_type, 200, "typechange(this)", 1); ?></td>
	</tr>
	<tr>
		<td><b>Purchase Status:</b></td>
		<td><?php echo oiopub_dropmenu_kv($adstatus_array, "adstatus", $item->item_status, 200); ?></td>
	</tr>
<?php if($nofollow) { ?>
	<tr>
		<td><b>Use Nofollow?</b></td>
		<td><?php echo oiopub_dropmenu_kv($oiopub_set->arr_yesno, "adnofollow", $item->item_nofollow, 200); ?></td>
	</tr>
<?php } ?>
<?php if($oiopub_module->tracker == 1 && $oiopub_set->tracker['enabled'] == 1) { ?>
	<tr>
		<td><b>Stats Tracking?</b></td>
		<td><?php echo oiopub_dropmenu_kv(array( 0 => "Yes", 1 => "No" ), "adtracking", $item->direct_link, 200); ?></td>
	</tr>
<?php } ?>
	<tr>
		<td colspan="2" height="20"></td>
	</tr>
	<tr>
		<td><b>Price:</b></td>
		<td><input type="text" name="adprice" size="40" value="<?php echo $item->payment_amount; ?>" /></td>
	</tr>
	<tr>
		<td><b>Currency:</b></td>
		<td><input type="text" name="adcurrency" size="40" value="<?php echo $item->payment_currency; ?>" /></td>
	</tr>
	<tr>
		<td><b>Payment Method:</b></td>
		<td><?php echo oiopub_dropmenu_kv($oiopub_set->arr_payment, "paymethod", $item->payment_processor, 200); ?></td>
	</tr>
	<tr>
		<td><b>Payment Status:</b></td>
		<td><?php echo oiopub_dropmenu_kv($paystatus_array, "paystatus", $item->payment_status, 200); ?></td>
	</tr>
<?php if($item_id > 0) { ?>
	<tr>
		<td><b>Transaction ID:</b></td>
		<td><input type="text" name="txn_id" size="40" value="<?php echo $item->payment_txid; ?>" /> &nbsp;[<a href="javascript://" title="Contains the transaction ID from the payment processor. Leave blank if no processor used."><b>?</b></a>]</td>
	</tr>
<?php } ?>
	<tr>
		<td><b>Subscription?</b></td>
		<td><?php echo oiopub_dropmenu_kv($oiopub_set->arr_yesno, "subscription", $item->item_subscription, 200); ?> &nbsp;[<a href="javascript://" title="If an ad is set as subscribed, it will continue forever until you remove the subscription."><b>?</b></a>]</td>
	</tr>
	<tr>
		<td colspan="2" height="20"></td>
	</tr>
	<tr>
		<td><b>Charging Model:</b></td>
		<td><?php echo oiopub_dropmenu_kv(array( 'days' => "Cost per day", 'clicks' => "Cost per click", 'impressions' => "Cost per impression" ), "admodel", $item->item_model, 200, 'setChargingModel(this);'); ?></td>
	</tr>
	<tr>
		<td><b>Start date:</b></td>
		<td><input type="text" name="adstart" size="40" value="<?php echo ($item->payment_time > 0 ? strftime("%m/%d/%Y  %T", $item->payment_time) : ""); ?>" /> &nbsp;[<a href="javascript://" title="Can schedule future start date, as long as purchase status is 'Approved' and payment status is 'Paid'. Date format is mm/dd/yy"><b>?</b></a>]</td>
	</tr>
	<tr>
		<td><b><span id="chargingModel"><?php echo $item->item_model ? ucfirst($item->item_model) : "Days"; ?></span> purchased</b></td>
		<td><input type="text" name="adduration" size="40" value="<?php echo  $item->item_duration; ?>" /> &nbsp;[<a href="javascript://" title="Set to zero if ad is a subscription, or should last forever"><b>?</b></a>]</td>
	</tr>
<?php if($item->item_model && $item->item_model != 'days') { ?>
	<tr>
		<td><b><?php echo ucfirst($item->item_model); ?> left</b></td>
		<td><input type="text" name="adduration_left" size="40" value="<?php echo $item->item_duration_left > 0 ? $item->item_duration_left : 0; ?>" /> &nbsp;[<a href="javascript://" title="The number of <?php echo $item->item_model; ?> left to use up"><b>?</b></a>]</td>
	</tr>
<?php } ?>
	<tr>
		<td colspan="2" height="20"></td>
	</tr>
<?php if($item_type == "post") { ?>
<?php if($item_id <= 0) { ?>
<?php if(!empty($cats_array)) { ?>
	<tr>
		<td><b>Post Category:</b></td>
		<td><?php echo oiopub_dropmenu_kv($cats_array, "postcat", $item->item_type, 200); ?></td>
	</tr>
<?php } ?>
	<tr>
		<td><b>Post Title:</b></td>
		<td><input type="text" name="posttitle" size="40" value="<?php echo $item->post_title; ?>" /></td>
	</tr>
	<tr>
		<td colspan="2" style="padding-top:15px;"><b>Post Content:</b><br /><textarea rows="8" cols="50" name="postcontent"><?php echo $item->post_content; ?></textarea></td>
	</tr>
<?php } else { ?>
	<tr>
		<td></td><td style="padding-top:15px;"><i><a href="<?php echo oiopub_post_admin_edit($item->post_id); ?>" target="_parent">Click Here</a> to edit the post itself</i></td>
	</tr>
<?php } ?>
<?php } elseif($item_type == "link") { ?>
	<tr>
		<td><b>Link URL:</b></td>
		<td><input type="text" name="adurl" size="40" value="<?php echo $item->item_url; ?>" />&nbsp;[<a href="javascript://" title="The url that your link goes to when clicked on"><b>?</b></a>]</td>
	</tr>
	<tr>
		<td><b>Link Text:</b></td>
		<td id="anchor1" ><input type="text" name="adpage" size="40" value="<?php echo $item->item_page; ?>" />&nbsp;[<a href="javascript://" title="The text that is linked to, make it short and snappy!"><b>?</b></a>]</td>
	</tr>
	<tr>
		<td><b>Link Tooltip:</b></td>
		<td><input type="text" name="adtooltip" size="40" value="<?php echo $item->item_tooltip; ?>" />&nbsp;[<a href="javascript://" title="A description (like this one!) that a user will see when hovering over your link"><b>?</b></a>]</td>
	</tr>
<?php if(!empty($cats_array)) { ?>
	<tr>
		<td><b>Category:</b></td>
		<td><?php echo oiopub_dropmenu_kv($cats_array, 'cats', $item->category_id, 180); ?>&nbsp;[<a href="javascript://" title="Select a category to associate the ad with"><b>?</b></a>]</td>
	</tr>
<?php } ?>
	<tr>
		<td valign="top"><b>Description:</b>&nbsp;[<a href="javascript://" title="You can add some descriptive text here that will sit below the link itself (like adsense)"><b>?</b></a>]</td>
		<td><textarea name="adnotes" style="width:270px; height:100px;"><?php echo stripslashes($item->item_notes); ?></textarea></td>
	</tr>
<?php } elseif($item_type == "inline") { ?>
	<tr><td colspan="2">
	<table id="inline-ad" width="100%" cellpadding="4" cellspacing="0">
	<?php if($oiopub_set->inline_ads['selection'] == 1) { ?>
	<tr>
		<td width="140"><b>YouTube URL:</b></td>
		<td><input type="text" name="adurl" size="40" value="<?php echo $item->item_url; ?>" />&nbsp;[<a href="javascript://" title="The URL of the youtube video that will be displayed"><b>?</b></a>]</td>
	</tr>
	<?php } elseif($oiopub_set->inline_ads['selection'] == 2) { ?>
	<?php if($oiopub_set->general_set['upload'] == 1) { ?>
	<tr>
		<td width="140" valign="top"><b>Banner Upload:</b>&nbsp;[<a href="javascript://" title="The banner image to upload"><b>?</b></a>]</td>
		<td>
			<input tabindex="4" type="file" name="adurl" size="40" />
			<?php
			if(!empty($item->item_url)) {
				echo "<br />\n";
				echo "[<a href=\"" . $item->item_url . "\" target=\"_target\">view current banner</a>]\n";
			}
			?>
		</td>
	</tr>
	<?php } else { ?>
	<tr>
		<td><b>Banner URL:</b></td>
		<td><input tabindex="4" type="text" name="adurl" size="40" value="<?php echo $item->item_url; ?>" />&nbsp;[<a href="javascript://" title="The url of the banner image that will be displayed"><b>?</b></a>]</td>
	</tr>
	<?php } ?>
	<tr>
		<td><b>Website URL:</b></td>
		<td><input type="text" name="adpage" size="40" value="<?php echo $item->item_page; ?>" />&nbsp;[<a href="javascript://" title="The URL of the website that the image will be linked to"><b>?</b></a>]</td>
	</tr>
	<tr>
		<td><b>Alt Text:</b></td>
		<td><input type="text" name="adtooltip" size="40" value="<?php echo $item->item_tooltip; ?>" />&nbsp;[<a href="javascript://" title="A description of the image to use in the 'alt' image tag"><b>?</b></a>]</td>
	</tr>
	<?php } elseif($oiopub_set->inline_ads['selection'] == 3) { ?>
	<tr>
		<td><b>RSS Feed URL:</b></td>
		<td><input type="text" name="adurl" size="40" value="<?php echo $item->item_url; ?>" />&nbsp;[<a href="javascript://" title="The URL of the feed that will be displayed"><b>?</b></a>]</td>
	</tr>
	<?php } ?>
	<tr>
		<td colspan="2" height="20"></td>
	</tr>
	<tr>
		<td valign="top"><b>Ad Code:</b>&nbsp;[<a href="javascript://" title="You can add code such as adsense here, to display the ads in a banner zone. You must include all script tags also."><b>?</b></a>]</td>
		<td><textarea name="adnotes" style="width:270px; height:100px;"><?php echo stripslashes($item->item_notes); ?></textarea></td>
	</tr>
	</table>
	<table id="inline-link" width="100%" cellpadding="4" cellspacing="0">
	<tr>
		<td width="140"><b>Link URL:</b></td>
		<td><input type="text" name="adurl2" size="40" value="<?php echo $item->item_url; ?>" />&nbsp;[<a href="javascript://" title="The URL of the website that the post text will be linked to"><b>?</b></a>]</td>
	</tr>
	<tr>
		<td><b>Link Tooltip:</b></td>
		<td><input type="text" name="adtooltip2" size="40" value="<?php echo $item->item_tooltip; ?>" />&nbsp;[<a href="javascript://" title="A description (like this one!) that a user will see when hovering over your link"><b>?</b></a>]</td>
	</tr>
	<tr>
		<td><b>Post ID:</b></td>
		<?php if(empty($item->post_phrase)) $item->post_phrase = "keyword(s) goes here"; ?>
		<td><input type="text" name="postid" size="2" value="<?php echo intval($item->post_id); ?>" />&nbsp;&nbsp;<input type="text" name="postphrase" size="31" value="<?php echo $item->post_phrase; ?>" />&nbsp;[<a href="javascript://" title="Pick a keyword(s) from your chosen page that will contain your link"><b>?</b></a>]</td>
	</tr>
	</table>
	</td></tr>
<?php } elseif($item_type == "banner") { ?>
	<?php if($oiopub_set->general_set['upload'] == 1) { ?>
	<tr>
		<td valign="top"><b>Banner Upload:</b>&nbsp;[<a href="javascript://" title="The banner image to upload"><b>?</b></a>]</td>
		<td>
			<input tabindex="4" type="file" name="adurl" size="40" />
			<?php
			if(!empty($item->item_url)) {
				echo "<br />\n";
				echo "[<a href=\"" . $item->item_url . "\" target=\"_target\">view current banner</a>]\n";
			}
			?>
		</td>
	</tr>
	<?php } else { ?>
	<tr>
		<td><b>Banner URL:</b></td>
		<td><input tabindex="4" type="text" name="adurl" size="40" value="<?php echo $item->item_url; ?>" />&nbsp;[<a href="javascript://" title="The url of the banner image that will be displayed"><b>?</b></a>]</td>
	</tr>
	<?php } ?>
	<tr>
		<td><b>Website URL:</b></td>
		<td><input type="text" name="adpage" size="40" value="<?php echo $item->item_page; ?>" />&nbsp;[<a href="javascript://" title="The URL of the website that the image will be linked to"><b>?</b></a>]</td>
	</tr>
	<tr>
		<td><b>Alt Text:</b></td>
		<td><input type="text" name="adtooltip" size="40" value="<?php echo $item->item_tooltip; ?>" />&nbsp;[<a href="javascript://" title="A description of the image to use in the 'alt' image tag"><b>?</b></a>]</td>
	</tr>
	<tr>
		<td colspan="2" height="20"></td>
	</tr>
<?php if(!empty($cats_array)) { ?>
	<tr>
		<td><b>Category:</b></td>
		<td><?php echo oiopub_dropmenu_kv($cats_array, 'cats', $item->category_id, 180); ?>[<a href="javascript://" title="Select a category to associate the ad with"><b>?</b></a>]</td>
	</tr>
<?php } ?>
	<tr>
		<td><b>SubID:</b></td>
		<td><input type="text" name="subid" size="40" value="<?php echo $item->item_subid; ?>" />&nbsp;[<a href="http://forum.oiopublisher.com/discussion/1100/different-ads-on-each-page-ie-buddypresswpmu/#Item_2" title="Click to find out more" target="_blank"><b>?</b></a>]</td>
	</tr>
	<tr>
		<td colspan="2" height="20"></td>
	</tr>
	<tr>
		<td valign="top"><b>Ad Code:</b>&nbsp;[<a href="javascript://" title="You can add code such as adsense here, to display the ads in a banner zone. You must include all script tags also."><b>?</b></a>]</td>
		<td><textarea name="adnotes" style="width:270px; height:100px;"><?php echo $item->item_notes; ?></textarea></td>
	</tr>
<?php } ?>
	<tr>
		<td colspan="2" style="padding-top:20px;">
			<center><input type="submit" name="submit" value="Update Changes" /></center>
		</td>
	</tr>
</table>
</form>
<br />
<form action="edit.php?type=<?php echo $item_type; ?>&id=<?php echo $item_id; ?>" method="post" style="text-align:center;">
<input type="hidden" name="csrf" value="<?php echo oiopub_csrf_token(); ?>" />
<input type="hidden" name="delete" value="yes" />
<input type="submit" name="submit" value="Delete Item?" />
</form>

<script type="text/javascript">
<!--
<?php
if($item->item_type > 0) {
	$item_type = $item->item_type;
} elseif(isset($_POST['adtype']) && $_POST['adtype'] > 0) {
	$item_type = intval($_POST['adtype']);
} else {
	$item_type = $oiopub_set->inline_ads['selection'];
}
if($item_type == 4) {
	echo "showdiv('inline-ad', 'none');\n";
	echo "showdiv('inline-link', 'block');\n";
} else {
	echo "showdiv('inline-link', 'none');\n";
	echo "showdiv('inline-ad', 'block');\n";
}
?>
//-->
</script>