<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

//define vars
define('OIOPUB_KEYWORDS', 1);

//init
include_once(str_replace("\\", "/", dirname(__FILE__)) . "/index.php");

//is plugin enabled?
if($oiopub_set->enabled != 1) {
	die("Plugin Currently Offline");
}

//$_POST request (make search)
if(isset($_POST['process']) && $_POST['process'] == 'yes') {
	$keywords = oiopub_clean($_POST['keywords']);
	$limit = (int) ($_POST['limit'] ? $_POST['limit'] : 10);
	$output = oiopub_keyword_data($keywords, $limit);
}

//template vars
$templates = array();
$templates['page'] = "purchase_keywords";
$templates['title'] = __oio("Keyword Selection");
$templates['output'] = $output;

//load template
include_once($oiopub_set->folder_dir . "/templates/core/main.tpl");

?>