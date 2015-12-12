<?php

//summary text function
function oiopub_rssbox_summary($content, $limit) {		
	$content = strip_tags($content);
	$content = explode(' ', $content);
	for($i=0; $i<$limit; $i++){
		$summary[$i] = $content[$i];
	}
	$summary = implode(' ', $summary) . '..';
	return $summary;
}

//rssbox body function
function oiopub_rssbox_body($item, $template="") {
	global $oiopub_set;
	//get ID
	$pid = intval($_GET['pid']);
	//get pub time
	$time = strtotime($item->pubDate);
	//get url
	if($oiopub_set->tracker['enabled'] == 1) {
		$url = $oiopub_set->tracker_url . "/go.php?id=" . $pid . "&url=" . $item->link;
	} else {
		$url = $item->link;
	}
	//format output
	$output = '';
	$output .= "<div class=\"rsscontainer\">\n";
	if($template == 'title-date') {
		$output .= "<div class=\"rsstitle\"><a href=\"" . $url . "\">" . $item->title . "</a></div>\n";
		$output .= "<div class=\"rssdate\">" . date('m/d/y g:i a', $time) . " | Category: " . $item->category[0] . "</div>\n";
	} else if($template == 'title-only') {
		$output .= "<div class=\"rsstitle\"><a href=\"" . $url . "\">" . $item->title . "</a></div>\n";
		$output .= "<div class=\"rsscategory\">Category: " . $item->category[0] . "</div>\n";
	} elseif($template == 'title-desc') {
		$output .= "<div class=\"rsstitle\"><a href=\"" . $url . "\">" . $item->title . "</a></div>\n";
		$output .= "<div class=\"rssdate\">" . date('d M Y g:i a', $time) . "</div>\n";
		$output .= "<div class=\"rssdesc\">" . oiopub_rssbox_summary($item->description, 12) . "</div>\n";
	} else {
		die ("<b>Error:</b> No template exists with such name!");
	}
	$output .= "</div>\n";
	return $output;
}

?>