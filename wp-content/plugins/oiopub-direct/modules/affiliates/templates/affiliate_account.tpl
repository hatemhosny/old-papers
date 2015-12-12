<?php if($_GET['do'] == '' && $affiliate_id <= 0) { ?>
<table class="start" align="center" width="500">
<tr><td>
<h2 style="text-align:center;"><?php echo __oio("Affiliate Login"); ?></h2>
<?php echo $templates['failed_login']; ?>
<form action="<?php echo $oiopub_set->request_uri; ?>" method="post">
<input type="hidden" name="process" value="login" />
<table width="100%" align="center" cellpadding="4" cellspacing="4">
<tr><td width="100"><b><?php echo __oio("Email"); ?>:</b></td><td><input type="text" size="40" name="email" /></td></tr>
<tr><td><b><?php echo __oio("Password"); ?>:</b></td><td><input type="password" size="40" name="password" /></td></tr>
<tr><td></td><td><a href="account.php?do=lost"><b><?php echo __oio("Forgotten Password?"); ?></b></a></td></tr>
<tr><td></td><td><input type="submit" value="<?php echo __oio("Login"); ?>" /></td></tr>
</table>
</form>
</td></tr>
<tr><td>
<h2 id="reg" style="text-align:center; margin-top:60px;"><?php echo __oio("Create an affiliate account"); ?></h2>
<?php echo $templates['error']; ?>
<form action="<?php echo $oiopub_set->request_uri; ?>#reg" method="post">
<input type="hidden" name="process" value="register" />
<table width="100%" align="center" cellpadding="4" cellspacing="4">
<tr><td width="100"><b><?php echo __oio("Name"); ?>:</b></td><td><input type="text" size="40" name="name" value="<?php echo $name; ?>" /></td></tr>
<tr><td><b><?php echo __oio("Email"); ?>:</b></td><td><input type="text" size="40" name="email" value="<?php echo $email; ?>" /></td></tr>
<tr><td><b><?php echo __oio("Password"); ?>:</b></td><td><input type="password" size="40" name="password" value="" /></td></tr>
<tr><td><b><?php echo __oio("PayPal"); ?>:</b></td><td><input type="text" size="40" name="paypal" value="<?php echo $paypal; ?>" /></td></tr>
<?php if($oiopub_set->general_set['security_question'] == 1) { ?>
<tr><td><b><?php echo __oio("Security"); ?>:</b></td><td><?php echo $captcha['question'] ?>&nbsp;&nbsp;&nbsp;<input type="text" size="5" name="security" /></td></tr>
<?php } if($oiopub_set->affiliates['terms']) { ?>
<tr><td></td><td style="font-size:11px;"><?php echo __oio("By registering, you accept our <a href='account.php?do=terms' target='_blank'>Terms and Conditions</a>"); ?></td></tr>
<?php } ?>
<tr><td></td><td><input type="submit" value="<?php echo __oio("Register"); ?>" /></td></tr>
</table>
</form>
</td></tr>
</table>
<?php } ?>

<?php if($_GET['do'] == '' && $affiliate_id > 0) { ?>
<?php if($oiopub_set->affiliates['help']) { ?>
<table align="center" width="90%">
	<tr>
		<td align="right">
			&raquo; <a href="account.php?do=help" style="color:red;"><?php echo __oio("Affiliate Help & FAQs"); ?></a>
		</td>
	</tr>
</table>
<?php } ?>
<table class="start" align="center" width="90%">
	<tr>
		<td>
			<h3 style='margin-bottom:2px;'><?php echo __oio("Affiliate Link"); ?> &nbsp;<small>[<a href="account.php?do=logout"><?php echo __oio("Logout"); ?></a>]</small></h3>
			<?php echo "http://" . $templates['aff_url']; ?>
		</td>
	</tr>
</table>
<table class="start" align="center" width="90%">
<tr><td>
<h3 style='margin-bottom:2px;'><?php echo __oio("Account Settings"); ?></h3>
<?php echo __oio("You can update your email address or paypal address below."); ?>
<br /><br /><br />
<form action="<?php echo $oiopub_set->request_uri; ?>" method="post">
<input type="hidden" name="process" value="update_settings" />
<table width="60%" align="center" cellpadding="4" cellspacing="4" style="background:#F0FFFF; padding:15px;">
<tr><td colspan="2" align="center"><?php echo $templates['error']; ?></td></tr>
<tr><td><b><?php echo __oio("Email"); ?>:</b></td><td><input type="text" size="40" name="email" value="<?php echo $aff->email; ?>" /></td></tr>
<tr><td><b><?php echo __oio("PayPal"); ?>:</b></td><td><input type="text" size="40" name="paypal" value="<?php echo $aff->paypal; ?>" /></td></tr>
<tr><td><b><?php echo __oio("Password"); ?>:</b></td><td><input type="text" size="40" name="password" value="" /></td></tr>
<tr><td></td><td><input type="submit" value="<?php echo __oio("Update"); ?>" /></td></tr>
</table>
</form>
</td></tr>
</table>
<table class="start" align="center" width="90%">
<tr><td>
<h3 style='margin-bottom:2px;'><?php echo __oio("Discount Settings"); ?></h3>
<?php echo __oio("The Affiliate Discount will allow you to offer customers a better deal, by transferring some of the money you would make as an affiliate to a customer discount. If you earn 20% per purchase for example, setting the discount value to 5 would give the customer a 5% discount, while you earn 15%."); ?>
<br /><br /><br />
<form action="<?php echo $oiopub_set->request_uri; ?>" method="post">
<input type="hidden" name="process" value="update_discount" />
<table align="center" cellpadding="4" cellspacing="4" style="background:#F0FFFF; padding:15px;">
<tr><td><b><?php echo __oio("My Earnings"); ?>:</b></td><td><?php echo $templates['discount_text']; ?></td></tr>
<tr><td><b><?php echo __oio("Dicsount Value"); ?>:</b></td><td><input type="text" size="30" name="coupon" value="<?php echo $aff->coupon; ?>" /> <?php echo $templates['discount_type']; ?></td></tr>
<tr><td></td><td><input type="submit" value="<?php echo __oio("Update"); ?>" /></td></tr>
</table>
</form>
</td></tr>
</table>
<table class="start" align="center" width="90%">
<tr><td>
<h3 style='margin-bottom:2px;'><?php echo __oio("My Sales Stats"); ?></h3>
<?php echo __oio("Below are your affiliate sales stats. There is a %s day waiting period before sales commission can be paid out, which is displayed as unverified commission. The verified commission is eligible to be paid out, but has yet to be processed.", array( $oiopub_set->affiliates['maturity'] )); ?>
<br /><br /><br />
<table width="60%" align="center" cellpadding="4" cellspacing="4" style="background:#F0FFFF; padding:15px;">
<tr><td colspan="2" style="padding-bottom:15px;"><b><?php echo __oio("Conversion Ratio"); ?>:</b></td></tr>
<tr><td><?php echo __oio("Total Hits Received"); ?>:</td><td><?php echo $total_hits; ?></td></tr>
<tr><td><?php echo __oio("Total Sales Generated"); ?>:</td><td><?php echo $total_sales; ?></td></tr>
<tr><td><?php echo __oio("Conversion Rate"); ?>:</td><td><?php echo number_format($conversion_ratio, 2); ?>%</td></tr>
<tr><td colspan="2" height="30"></td></tr>
<tr><td colspan="2" style="padding-bottom:15px;"><b><?php echo __oio("Sales Commission"); ?>:</b></td></tr>
<tr><td><?php echo __oio("Unverified Commission"); ?>:</td><td><?php echo number_format($unverified_commission, 2); ?> <?php echo $oiopub_set->general_set['currency']; ?></td></tr>
<tr><td><?php echo __oio("Verified Commission"); ?>:</td><td><?php echo number_format($verified_commission, 2); ?> <?php echo $oiopub_set->general_set['currency']; ?></td></tr>
<tr><td><?php echo __oio("Paid Commission"); ?>:</td><td><?php echo number_format($paid_commission, 2); ?> <?php echo $oiopub_set->general_set['currency']; ?></td></tr>
</table>
<?php if($fraud_commission > 0) { ?>
<br />
<table align="center">
<tr><td align="center"><?php echo __oio("Commission removed due to sales discrepency"); ?>: <?php echo number_format($fraud_commission, 2); ?> <?php echo $oiopub_set->general_set['currency']; ?></td></tr>
</table>
<?php } ?>
</td></tr>
</table>
<?php } ?>

<?php if($_GET['do'] == 'lost') { ?>
<table class="start" align="center" width="500">
<tr><td>
<h2 style="text-align:center;"><?php echo __oio("Resend Password"); ?></h2>
<?php echo $templates['error']; ?>
<form action="<?php echo $oiopub_set->request_uri; ?>" method="post">
<input type="hidden" name="process" value="lost_password" />
<table align="center" cellpadding="4" cellspacing="4">
<tr><td width="100"><b><?php echo __oio("Email"); ?>:</b></td><td><input type="text" size="40" name="email" /></td></tr>
<?php if($oiopub_set->general_set['security_question'] == 1) { ?>
<tr><td><b><?php echo __oio("Security"); ?>:</b></td><td><?php echo $captcha['question'] ?>&nbsp;&nbsp;&nbsp;<input type="text" size="5" name="security" /></td></tr>
<?php } ?>
<tr><td></td><td><input type="submit" value="<?php echo __oio("Send Password"); ?>" /></td></tr>
</table>
</form>
<br /><br />
<center>
<a href="account.php"><b><?php echo __oio("Back to Affiliate Page"); ?></b></a>
</center>
</td></tr>
</table>
<?php } ?>

<?php if($_GET['do'] == 'terms') { ?>
<table align="center" width="90%">
	<tr>
		<td align="right">
			&raquo; <a href="javascript:history.go(-1);" style="color:red;"><?php echo __oio("Back"); ?></a>
		</td>
	</tr>
</table>
<h2><?php echo __oio("Affiliate Terms and Conditions"); ?></h2>
<table class="start" align="center" width="500">
<tr><td align="left">
<?php echo str_replace(array("\r\n", "\n"), "<br />", stripslashes($oiopub_set->affiliates['terms'])); ?>
</td></tr>
</table>
<?php } ?>

<?php if($_GET['do'] == 'help') { ?>
<table align="center" width="90%">
	<tr>
		<td align="right">
			&raquo; <a href="javascript:history.go(-1);" style="color:red;"><?php echo __oio("Back"); ?></a>
		</td>
	</tr>
</table>
<h2><?php echo __oio("Affiliate help & FAQs"); ?></h2>
<table class="start" align="center" width="500">
<tr><td align="left">
<?php echo str_replace(array("\r\n", "\n"), "<br />", stripslashes($oiopub_set->affiliates['help'])); ?>
</td></tr>
</table>
<?php } ?>