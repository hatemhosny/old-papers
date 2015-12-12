<table align="center" border="0" width="550" class="start">
<tr><td align="center">
<form name="type" id="type" method="post" action="purchase.php">
<input type="hidden" name="process" value="yes" />
<?php echo oiopub_zone_select(); ?>
<div class="payment-link">
<a href="payment.php" rel="nofollow"><?php echo __oio("Need to make a payment"); ?>?</a>
</div>
</form>
</td></tr>
</table>
<?php if($oiopub_set->links_total > 0) { ?>
<!-- text ads start -->
<table align="center" border="0" width="550" class="start">
<tr><td>
<h3><?php echo __oio("Text Ad Pricing"); ?></h3>
<table id="link-chart" class="chart" width="100%" border="0" cellspacing="2" cellpadding="2">
<?php echo $oiopub_purchase->chart(2, "#E0EEEE", "#FFFFFF"); ?>
</table>
</td></tr>
</table>
<!--// text ads end -->
<?php } ?>
<?php if($oiopub_set->banners_total > 0) { ?>
<!-- banner ads start -->
<table align="center" border="0" width="550" class="start">
<tr><td>
<h3><?php echo __oio("Banner Ad Pricing"); ?></h3>
<table id="banner-chart" class="chart" width="100%" border="0" cellspacing="2" cellpadding="2">
<?php echo $oiopub_purchase->chart(5, "#E0EEEE", "#FFFFFF"); ?>
</table>
</td></tr>
</table>
<!--// banner ads end -->
<?php } ?>
<?php if($oiopub_set->inline_total > 0) { ?>
<!-- inline ads start -->
<table align="center" border="0" width="550" class="start">
<tr><td>
<h3><?php echo __oio("Inline Ad Pricing"); ?></h3>
<table id="inline-chart" class="chart" width="100%" border="0" cellspacing="2" cellpadding="2">
<?php echo $oiopub_purchase->chart(3, "#E0EEEE", "#FFFFFF"); ?>
</table>
</td></tr>
</table>
<!--// inline ad end -->
<?php } ?>
<?php if($oiopub_set->posts_total > 0) { ?>
<!-- posts start -->
<table align="center" border="0" width="550" class="start">
<tr><td>
<h3><?php echo __oio("Paid Review Pricing"); ?></h3>
<table id="post-chart" class="chart" width="100%" border="0" cellspacing="2" cellpadding="2">
<?php echo $oiopub_purchase->chart(1, "#E0EEEE", "#FFFFFF"); ?>
</table>
</td></tr>
</table>
<!--// posts end -->
<?php } ?>
<?php if($oiopub_set->custom_total > 0) { ?>
<!-- custom purchases start -->
<table align="center" border="0" width="550" class="start">
<tr><td>
<h3><?php echo __oio("Custom Item Pricing"); ?></h3>
<table id="custom-chart" class="chart" width="100%" border="0" cellspacing="2" cellpadding="2">
<?php echo $oiopub_purchase->chart(4, "#E0EEEE", "#FFFFFF"); ?>
</table>
</td></tr>
</table>
<!--// custom purchases end -->
<?php } ?>
<?php if(!empty($oiopub_set->rules)) { ?>
<!-- purchase guidelines begin -->
<table align="center" border="0" width="550" class="start">
<tr><td>
<h3><?php echo __oio("Purchasing Guidelines"); ?></h3>
<?php
$oiopub_set->rules = str_replace("\r\n", "<br />", $oiopub_set->rules);
$oiopub_set->rules = str_replace("\n", "<br />", $oiopub_set->rules);
echo stripslashes($oiopub_set->rules);
?>
</td></tr>
</table>
<!--// purchase guidelines end -->
<?php } ?>
