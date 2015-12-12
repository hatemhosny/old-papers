<?php
//header include
function oiopub_header_inc($templates) {
	global $oiopub_set;
	if(!defined('NO_HEADER') && $oiopub_set) {
		include_once($oiopub_set->template_header);
	}
}
//show header?
if(!isset($_GET['embed'])) {
	oiopub_header_inc($templates);
} else {
	echo '<link rel="stylesheet" type="text/css" href="' . $oiopub_set->plugin_url . '/templates/' . $oiopub_set->template . '/style.css" />' . "\n";
}
?>

<link type="text/css" href="<?php echo $oiopub_set->plugin_url; ?>/libs/bubble/bubble.css" rel="stylesheet" />
<script type="text/javascript" src="<?php echo $oiopub_set->plugin_url; ?>/libs/bubble/bubble.js"></script>
<script type="text/javascript" src="<?php echo $oiopub_set->plugin_url; ?>/libs/misc/oiopub.js"></script>
<script type="text/javascript">window.onload = function(){ enableTooltip('oiopub-container'); }</script>

<div id="oiopub-container" style="padding:20px 0;">

<?php
//content paths
$paths = array(
	'custom' => $oiopub_set->folder_dir . "/templates/core_custom",
	'standard' => $templates['path'] ? $templates['path'] : $oiopub_set->folder_dir . "/templates/core",
);
//loop through paths
foreach($paths as $key => $path) {
	//get file
	$file = $path . "/" . $templates['page'] . ".tpl";
	//does file exist?
	if($key == 'standard' || is_file($file)) {
		include_once($file);
		break;
	}
}
?>

<?php
//javascript insert
function oiopub_js_inc($templates) {
	global $oiopub_hook;
	//show js?
	if($templates) {
		echo $templates['javascript'];
	}
	//fire hook?
	if($oiopub_hook) {
		$oiopub_hook->fire('content_end');
	}
}
oiopub_js_inc($templates);
?>

</div>

<?php
//footer include
function oiopub_footer_inc($templates) {
	global $oiopub_set;
	if(!defined('NO_FOOTER') && $oiopub_set) {
		include_once($oiopub_set->template_footer);
	}
}
//show footer?
if(!isset($_GET['embed'])) {
	oiopub_footer_inc($templates);
}
?>