<form action="keywords.php" method="post">
<input type="hidden" name="process" value="yes" />
<table width="500" align="center" border="0" cellspacing="4" cellpadding="4" class="start">
	<tr>
		<td width="100"><b><?php echo __oio("Search Term"); ?>:</b></td>
		<td><input type="text" name="keywords" size="30" value="<?php echo $keywords; ?>" /></td>
	</tr>
	<tr>
		<td><b><?php echo __oio("# of Results"); ?>:</b></td>
		<td><?php echo oiopub_dropmenu_k(array("10", "20", "30", "40", "50"), "limit", $_POST['limit'], 150); ?></td>
	</tr>
	<tr>
		<td></td>
		<td><input type="submit" value="<?php echo __oio("Submit Keywords"); ?>" /></td>
	</tr>
</table>
</form>
<?php if(!isset($_POST['process'])) { ?>
<br />
<p><?php echo __oio("Enter a search term above to receive a list of posts that most closely match that term."); ?></p>
<p><?php echo __oio("You can then browse the posts to select the exact phrase you'd like to link over."); ?></p>
<p><?php echo __oio("You should then enter the post id, and phrase to link, in the purchase form."); ?></p>
<br />
<?php } ?>
<?php echo $templates['output']; ?>
