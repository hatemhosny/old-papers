<div style="text-align:left; padding:20px;">
<?php if(!$allow_access) { ?>
	<center>
	<b><?php echo __oio("Please enter a valid email address and purchase code."); ?></b>
	<br /><br />
	<form method="post" action="<?php echo $oiopub_set->request_uri; ?>">
	<input type="hidden" name="process" value="credentials" />
	<table width="500" border="0" cellspacing="4" cellpadding="4">
		<tr>
			<td><?php echo __oio("Email Address"); ?></td>
			<td><input type="text" name="email" size="30" value="<?php echo $email; ?>" /></td>
		</tr>
		<tr>
			<td><?php echo __oio("Purchase Code"); ?></td>
			<td><input type="text" name="rand" size="30" value="<?php echo $rand_id; ?>" /></td>
		</tr>
		<tr>
			<td></td>
			<td><input type="submit" value="<?php echo __oio("Continue"); ?>" /></td>
		</tr>
	</table>
	</form>
	</center>
<?php } else { ?>
	<h3 style="margin-top:-15px;"><i><?php echo $pdata['type']; ?></i></h3>
	<table width="100%" border="0" cellspacing="4" cellpadding="4">
<?php if($sub_id = oiopub_subid($purchase->item_channel, $purchase->item_type)) { ?>
	<tr>
		<td valign="top"><b><?php echo __oio("Sub ID"); ?>:</b></td>
		<td><?php echo $sub_id; ?></td>
	</tr>
<?php } ?>
		<tr>
			<td width="130"><b><?php echo __oio("Ad Cost"); ?>:</b></td>
			<td><?php echo $purchase->payment_amount; ?> <?php echo $purchase->payment_currency; ?> / <?php echo (empty($purchase->item_duration) ? __oio("permanent") : number_format($purchase->item_duration, 0) . " " . __oio($purchase->item_model)); ?></td>
		</tr>
		<tr>
			<td><b><?php echo __oio("Ad Status"); ?>:</b></td>
			<td>
				<?php echo $pdata['istatus']; ?>, <?php echo $pdata['pstatus']; ?> &nbsp;&nbsp;
				<?php if($purchase->item_status == 3) { ?>
				(<a href="payment.php?rand=<?php echo $rand_id; ?>" style="color:red;"><?php echo __oio("renew now"); ?></a>)
				<?php } elseif($purchase->payment_status == 0) { ?>
				(<a href="payment.php?rand=<?php echo $rand_id; ?>" style="color:red;"><?php echo __oio("make payment"); ?></a>)
				<?php } ?>
			</td>
		</tr>
<?php if($purchase->payment_status == 1 && $purchase->item_status == 1) { ?>
		<tr>
			<td><b><?php echo __oio("Start Date"); ?>:</b></td>
			<td><?php echo date('jS M, Y', $purchase->payment_time); ?></td>
		</tr>
<?php if($purchase->item_model == 'days' && $purchase->item_duration > 0) { ?>
		<tr>
			<td><b><?php echo __oio("Expiry Date"); ?>:</b></td>
			<td><?php echo $pdata['expire']; ?></td>
		</tr>
<?php } ?>
<?php } ?>
		<tr>
			<td><b><?php echo __oio("Target URL"); ?>:</b></td>
			<td><a href="<?php echo $pdata['url']; ?>" target="_blank"><?php echo $pdata['url']; ?></a></td>
		</tr>
<?php if(!empty($pdata['image'])) { ?>
		<tr>
			<td><b><?php echo __oio("Image URL"); ?>:</b></td>
			<td><a href="<?php echo $pdata['image']; ?>" target="_blank"><?php echo __oio("click here to view image"); ?></a></td>
		</tr>
<?php } ?>
	</table>
	<br /><br />
	<h3><i><?php echo __oio("Update this ad"); ?></i></h3>
	<?php
	if($oiopub_set->general_set['edit_ads'] == 1 && in_array($purchase->item_channel, array( 2, 3, 5 ))) {
		echo __oio("Please click here to") . ' <a href="purchase.php?rand=' . $purchase->rand_id . '"><b>' . __oio("edit your ad") . '</b></a>.';
	} else {
		echo __oio("Please contact us to update your ad") . ': <a href="mailto:' . $oiopub_set->admin_mail . '">' . $oiopub_set->admin_mail . '</a>';
	}
	?>
	<br /><br /><br />
<?php $oiopub_hook->fire('stats_page', $purchase->item_id); ?>
<?php } ?>
</div>