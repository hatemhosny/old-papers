<div style="text-align:center;">

<?php if(!$oio_data || !$oio_data->rand_id) { ?>

<br /><br />
<?php if(!empty($rand_id)) { ?>
	<b><?php echo __oio("No payment information for this purchase code available!"); ?></b>
	<br /><br /><br /><br />
<?php } ?>
<form method="get" action="payment.php">
<?php echo __oio("Purchase Code"); ?> &nbsp;<input type="text" name="rand" size="20" /> <input type="submit" value="<?php echo __oio("Go"); ?>" />
</form>
<br /><br />

<?php } elseif($_GET['do'] == 'failed') { ?>

<b><?php echo __oio("Your payment has been marked as invalid or incomplete, and not recorded!"); ?></b>

<?php } elseif($_GET['do'] == "success" && $oio_data->payment_status == 0) { ?>

<b><?php echo __oio("Waiting for a response from the payment processor"); ?>...</b>
<meta http-equiv="refresh" content="5" />

<?php } elseif($_GET['do'] == "success" && $oio_data->payment_status != 0) { ?>

<?php if($oiopub_set->posts['price_free'] == 1 && $oiopub_set->posts['price_adv'] == 0 && $oio_data->post_author == 2) { ?>
	<b><?php echo __oio("Your free post has been submitted, please check your e-mail for confirmation!"); ?></b>
<?php } elseif($oio_data->payment_status == 1) { ?>
	<b><?php echo __oio("Your payment has been recorded, please check your e-mail for confirmation!"); ?></b>
<?php } else { ?>
	<b><?php echo __oio("Your payment has been recorded, but failed the verification checks, and will be reviewed manually!"); ?></b>
<?php } if($oio_data->item_status == 0) { ?>
	<br /><br />
	<?php echo __oio("Your purchase must be approved before it is finalised."); ?>
<?php } ?>
<br /><br />
<?php echo __oio("Please note down your unique purchase ID for future reference") . ": <b>" . $rand_id . "</b>\n"; ?>
<?php
if($oio_data->item_status == 1 && $oio_data->payment_status == 1) {
	if($oio_data->item_channel == 4) {
		$cn = "custom_" . $oio_data->item_type;
		if(!empty($oiopub_set->{$cn}['download'])) {
			$url = $oiopub_set->plugin_url . "/download.php?id=" . $oio_data->item_id . "&rand=" . $rand_id;
			echo "<meta http-equiv='refresh' content='5;URL=" . $url . "' />\n";
			echo "<br /><br /><br />\n";
			echo "<a href='" . $url . "' rel='nofollow'>" . __oio("You will shortly be redirected to the download page!") . "</a>\n";
		}
	} elseif($oio_data->item_channel != 1) {
		echo "<br /><br /><br />\n";
		echo __oio("You should now see your purchase displayed on this site.") . "\n";
	}
} elseif($oio_data->item_status == -1 && $oio_data->payment_status == 1) {
	echo "<br /><br /><br />\n";
	echo "<font color='red'><b>" . __oio("Queued") . ":</b> " . __oio("you will receive an email once your ad becomes active") . ".</font>\n";
	echo "<br />\n";
	$est = oiopub_queue_estimate($oio_data->item_channel, $oio_data->item_type, $oio_data->payment_time);
	echo "<i>" . __oio("The estimated publishing date is %s", array($est['date'])) . "</i>";
}
if(($oio_data->payment_status == -1 || $oio_data->payment_status == 1) && !empty($oiopub_set->feedback)) {
	echo "<br /><br /><br />\n";
	echo "<a href='" . $oiopub_set->feedback . "' rel='nofollow'><b>" . __oio("Please leave feedback on my marketplace profile") . "</b></a>\n";
}
?>

<?php } else { ?>

<?php if($oiopub_set->general_set['paytime'] == 1 && $oiopub_set->api['paytime'] == 0 && $oio_data->payment_status == 0 && ($oio_data->item_status == 0 || $oio_data->item_status == -2)) { ?>
	<b><?php echo __oio("The website owner has selected not to allow payment until approval has been made."); ?></b>
	<br /><br />
	<?php echo __oio("You will receive an email (and payment link) once approval has taken place."); ?>
<?php } elseif($oio_data->item_status == 3 && !$free_space) { ?>
	<b><?php echo __oio("You cannot renew your purchase at this time, as the available spaces have already been filled."); ?></b>
<?php } elseif($oio_data->item_status == 2) { ?>
	<b><?php echo __oio("This purchase has been rejected for use on this site!"); ?></b>
<?php } elseif($oio_data->payment_status == 0 || ($type_check[0] != 'p' && $oio_data->item_status == 3 && $free_space)) { ?>
	<h3><?php echo __oio("Payment For %s", array($item_title)); ?></h3>
	<?php if($oio_data->item_status == -1 || $oio_data->item_status == -2) { ?>
		<br />
		<font color="red"><?php echo __oio("Just a reminder that your ad will be placed in the queue"); ?></font>
		<br />
	<?php } ?>
	<br />
	<center>
	<table align="center" border="0" cellspacing="6" cellpadding="6" style="text-align:left;">
		<tr>
			<td valign="top" width="140"><b><?php echo __oio("Payment Method:"); ?></b></td>
			<td valign="top"><?php echo $oio_data->payment_processor; ?></td>
		</tr>
		<tr>
			<td valign="top"><b><?php echo __oio("Purchase Price:"); ?></b></td>
			<?php if($oio_data->coupon_discount > 0) { ?>
			<td valign="top">
				<s><?php echo oiopub_amount($oio_data->payment_amount + $oio_data->coupon_discount, $oio_data->payment_currency); ?></s>
				&nbsp;&nbsp;
				<?php echo oiopub_amount($oio_data->payment_amount, $oio_data->payment_currency); ?>
			</td>
			<?php } else { ?>
			<td valign="top"><?php echo oiopub_amount($oio_data->payment_amount, $oio_data->payment_currency); ?></td>
			<?php } ?>
		</tr>
		<?php if($oiopub_set->coupons['enabled'] == 1) { ?>
		<tr>
			<td valign="top"><b><?php echo __oio("Coupon Code?"); ?></b></td>
			<td valign="top">
				<form method="post" action="<?php echo $oiopub_set->request_uri; ?>">
				<input type="hidden" name="process" value="coupon_code" />
				<input type="text" name="coupon" value="<?php echo ($coupon ? $coupon : $oio_data->coupon); ?>" />
				&nbsp;
				<input type="submit" value="<?php echo __oio("Update"); ?>" />
				</form>
				<?php if($coupon_error && $coupon) { ?>
				<?php echo '<p style="color:red;">' . $coupon_error . '</p>' . "\n"; ?>
				<?php } elseif($oio_data->coupon_discount > 0) { ?>
				<?php echo '<p style="color:green;">' . oiopub_amount($oio_data->coupon_discount, $oio_data->payment_currency) . ' ' . __oio("discount applied") . '</p>' . "\n"; ?>
				<?php } elseif($oiopub_set->demo == 1) { ?>
				<?php echo '<p style="font-size:11px;">Use the coupon <b>100FREE</b> to make a free purchase</p>' . "\n"; ?>
				<?php } ?>
			</td>
		</tr>
		<?php } ?>
		<tr>
			<td colspan="2">
			<?php
			$proc = strtolower($oio_data->payment_processor);
			if(!empty($oiopub_plugin[$proc])) {
				echo '<div id="processor">' . "\n";
				$oiopub_plugin[$proc]->form($rand_id);
				echo '</div>' . "\n";
			} else {
				echo __oio("An error has occurred. Unable to load the required payment module.");
				die();
			}
			?>
			</td>
		</tr>
	</table>
	</center>
<?php } else { ?>
	<b><?php echo __oio("This purchase code exists, but the transaction is already complete!"); ?></b>
	<br /><br />
	<a href="stats.php?rand=<?php echo $rand_id; ?>"><?php echo __oio("Login to your ad dashboard"); ?></a>
<?php } ?>

<?php } ?>

</div>