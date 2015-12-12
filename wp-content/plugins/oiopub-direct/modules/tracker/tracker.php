<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/


//define vars
define('OIOPUB_LOAD_LITE', 1);

//pixel data
$image_pixel = pack('H*', '47494638396101000100910000000000ffffffff'.'ffff00000021f90405140002002c000000000100'.'01000002025401003b');

//dont cache
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

//image type
header("Content-Type: image/gif");
header("Content-Length: " . strlen($image_pixel));

//init
include_once("../../index.php");

//log visit?
if(isset($oiopub_plugin['tracker'])) {
	$ids = oiopub_var('pids', 'get');
	$oiopub_plugin['tracker']->log_visit($ids);
}

//display pixel
echo $image_pixel;
exit();