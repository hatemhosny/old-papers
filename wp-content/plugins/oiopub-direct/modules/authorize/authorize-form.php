<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

//nocache
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

//init
include_once('../../index.php');

//rand ID
$rand_id = oiopub_clean($_POST['rand']);

//payment class
$oiopub_payment =& oiopub_payment('authorize');

//process form
$oiopub_payment->process_form($rand_id);

?>