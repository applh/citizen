<?php

// process as much as possible
ignore_user_abort(true);
// don't show E_NOTICE
//error_reporting(E_ERROR | E_WARNING | E_PARSE);
error_reporting(E_ALL ^ E_NOTICE);

$curdir=dirname(__FILE__);
// try local config file
if (defined('CTZ_CONFIG_FILE') && is_file(CTZ_CONFIG_FILE)) {
   include(CTZ_CONFIG_FILE);
}
// if not found, try upper config file
if (!defined('CTZ_DATA') && is_file("$curdir/../ctz-config.php")) {
   include("$curdir/../ctz-config.php");
}
// load core functions
require_once("$curdir/functions/index.php");
// remember this folder as BASE_DIR
ctz_set('BASE_DIR', $curdir);

// dns like 1.2.sub.domain.tld is ok
// block dns like 1.2.3.4.5.6
ctz_set('dns_max', 5);

// launch the CTZ application
global $CTZ;
$CTZ=new Ctz_app();

// some processing is done at object destruction time
// so you could add extra processing here
// but it is not recommended:
// better use internal hooks mecanism :-P
?>
