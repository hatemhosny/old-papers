<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title><?php echo isset($templates['title_head']) ? $templates['title_head'] : $templates['title']; ?> | <?php echo $oiopub_set->site_name; ?></title>
	<link rel="stylesheet" type="text/css" href="<?php echo $oiopub_set->plugin_url; ?>/templates/<?php echo $oiopub_set->template; ?>/style.css" />
	<?php echo isset($_GET['rand']) ? '<meta name="robots" content="noindex,nofollow" />' . "\n" : ''; ?>
</head>

<body>
	<div id="wrap">
		<div id="header">
			<img src="<?php echo $oiopub_set->plugin_url; ?>/templates/<?php echo $oiopub_set->template; ?>/images/logo.gif" alt="Logo" />
			<h1><?php echo $templates['title']; ?></h1>
		</div>
		<div id="content">
