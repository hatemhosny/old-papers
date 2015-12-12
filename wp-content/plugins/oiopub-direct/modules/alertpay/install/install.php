<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

//set global vars
global $oiopub_set;

//add options
oiopub_add_config('alertpay', array("install"=>0, "enable"=>0, "valid"=>0, "mail"=>'', "security"=>''));

?>