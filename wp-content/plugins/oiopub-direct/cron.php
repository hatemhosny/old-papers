<?php

//ignore_user_abort(true);

//define vars
define('OIOPUB_CRON', 1);

//init
include_once(str_replace("\\", "/", dirname(__FILE__)) . "/index.php");

//run job?
if($oiopub_set->enabled == 1) {
	$oiopub_cron->run_job(1, true);
}

//done
die();