<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

//legacy banner output
function oiopub_banner_output($args='', $zone=1, $action=0) {
	$position = "center"; $title = "";
	return oiopub_banner_zone($zone, $position, $title, $action);
}

//legacy text link output
function oiopub_sidebar_links($args='', $title='', $link=1, $start='', $finish='', $action=0) {
	$zone = 1;
	return oiopub_link_zone($zone, $position, $title, $action);
}

//legacy ad slots output
function oiopub_open_slots($args='', $title='') {
	return oiopub_ad_slots($title);
}

//legacy ad badge output
function oiopub_sidebar_purchase($args='', $image='', $width='', $height='') {
	return oiopub_ad_badge($image, $width, $height);
}

?>