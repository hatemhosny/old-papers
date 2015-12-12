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

//payment class
$oiopub_payment =& oiopub_payment('2checkout');

//ipn handler
$oiopub_payment->ipn_validate();

?>