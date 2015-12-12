<?php

if(!isset($_GET['mp3_path'])) {
	die();
}

require_once(dirname(__FILE__).'/../getid3/getid3.php');

$getID3 = new getID3;
#$getID3->option_tag_id3v2 = true; # Don't know what this does yet
$getID3->analyze($_GET['mp3_path']);
if (isset($getID3->info['id3v2']['APIC'][0]['data'])) {
	$cover = $getID3->info['id3v2']['APIC'][0]['data'];
} elseif (isset($getID3->info['id3v2']['PIC'][0]['data'])) {
	$cover = $getID3->info['id3v2']['PIC'][0]['data'];
} else {
	$cover = null;
}
if (isset($getID3->info['id3v2']['APIC'][0]['image_mime'])) {
	$mimetype = $getID3->info['id3v2']['APIC'][0]['image_mime'];
} else {
	$mimetype = 'image/jpeg'; // or null; depends on your needs
}

if (!is_null($cover)) {
	// Send file
	header("Content-Type: " . $mimetype);

	if (isset($getID3->info['id3v2']['APIC'][0]['image_bytes'])) {
		header("Content-Length: " . $getID3->info['id3v2']['APIC'][0]['image_bytes']);
	}

	echo $cover;
} 

?>