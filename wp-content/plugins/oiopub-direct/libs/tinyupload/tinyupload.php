<?php

/*
Tiny Upload

upload.php

This file does the uploading

Modifications made in order to integrate with OIOpub Direct
*/


//###### Config ######

//The Absolute path (from the clients POV) to this file.
$absPthToSlf = htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES);

//The Absolute path (from the clients POV) to the destination folder.
$absPthToDst = explode("/libs/", $absPthToSlf, 2);
$absPthToDst = $absPthToDst[0] . "/uploads/";

//The Absolute path (from the servers POV) to the destination folder.
$absPthToDstSvr = dirname(dirname(dirname(__FILE__)));
$absPthToDstSvr = str_replace("\\", "/", $absPthToDstSvr);

//define OIO
if(!defined('oiopub')) define('oiopub', 1);


//###### You should not need to edit past this point ######

if(isset($_GET['poll']) && $_GET['poll']) {
	echo '[]';
} else {
	if(isset($_POST['process']) && $_POST['process'] == "upload") {
		if($_FILES['tuUploadFile']['tmp_name']) {
			//load OIO uploading class
			include_once($absPthToDstSvr . "/include/upload.php");
			//create object
			$upload = new oiopub_upload();
			$upload->name = $_FILES['tuUploadFile']['name'];
			$upload->size = $_FILES['tuUploadFile']['size'];
			//$upload->max_size = 200000;
			$upload->temp_name = $_FILES['tuUploadFile']['tmp_name'];
			$upload->upload_dir = $absPthToDstSvr . "/uploads";
			$upload->valid_exts = array( 'jpg', 'jpeg', 'gif', 'png' );
			$upload->is_image = true;
			//upload 
			$upload->upload();
		}
	}
?>
<html>
<head>
<style type="text/css">
body {
	font-size:10px;
	margin:0px;
	padding:0px;
	height:20px;
	overflow:hidden;
}
</style>
<script type="text/javascript">
window.onload = function() {
	parent.tuIframeLoaded();
}
<?php if(isset($upload) && $upload->name) { ?>
//set vars
var fileName = '<?php echo (isset($upload) ? $upload->name : ''); ?>';
var filePath = '<?php echo $absPthToDst; ?>' + fileName;
//is uploading?
if(parent.tuFileUploadStarted(filePath, fileName)) {
	window.document.body.style.cssText = 'border:none;padding-top:100px';
	document.getElementById('tuUploadFrm').submit();
}
<?php } ?>
</script>
</head>
<body style="border:none;">
	<form enctype="multipart/form-data" method="post" action="<?php echo $absPthToSlf; ?>" id="tuUploadFrm">
		<input type="hidden" name="process" value="upload" />
		<table border="0" cellspacing="0" cellpadding="0" style="height:22px; vertical-align:top;">
			<tr>
				<td>
					<input type="file" size="20" style="height:22px;" id="tuUploadFile" name="tuUploadFile" />
				</td>
				<td style="padding-left:2px;">
					<input type="submit" value="Go" style="border:1px solid #808080; background:#fff; height:20px;"/>
				</td>
			</tr>
		</table>
	</form>
</body>
</html>
<?php } ?>