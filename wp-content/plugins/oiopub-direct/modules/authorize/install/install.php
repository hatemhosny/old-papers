<?php

/*
Copyright (C) 2008  Simon Emery

This file is part of OIOpublisher Direct.
*/

if(!defined('oiopub')) die();

//set global vars
global $oiopub_set;

//add options
oiopub_add_config('authorize', array("install"=>0, "enable"=>0, "valid"=>0, "login_id"=>'', "transaction_key"=>'', "md5_hash"=>''));

?>