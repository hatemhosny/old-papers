<?php

//init
include_once('../../index.php');

//include functions
include_once('functions.php');

//feed parameters
$feed_url = strip_tags($_GET['id']);
$feed_number = (isset($_GET['limit']) ? intval($_GET['limit']) : 3);
$template_name = (isset($_GET['template']) ? strip_tags($_GET['template']) : "title-only");

//clear output
$count = 0;
$output = "";
$item_array = array();

//process feed
if($cache = $oiopub_cache->get($feed_url)) {
	$output = $cache;
} else {
	//load parser
	include_once($oiopub_set->folder_dir . '/include/xml.php');
	//init parser
	$xml = oiopub_parser($feed_url);
	if(!empty($xml->channel)) {
		foreach($xml->channel->item as $item) {
			if(!in_array($item->link, $item_array)) {
				$output .= oiopub_rssbox_body($item, $template_name);
				$item_array[] = $item->link;
				$count++;
				if($count >= $feed_number) {
					break;
				}
			}
		}
	} else {
		die("<b>Error:</b> Feed did not return valid content!");
	}
	//cache it
	$oiopub_cache->write($feed_url, $output);
}

//output
echo $output;
echo "<div class=\"rssupdate\"><small>&raquo; <a href=\"" . $feed_url . "\">Get Regular Updates</a></small></div>\n";

?>