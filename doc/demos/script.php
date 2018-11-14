<?php

define("ENVIRONMENT","dev"); // "dev" "testing" "production"
define('__WEBROOT__',__DIR__);
define("RUN_MODE","cli"); // 访问模式仅限cli模式
define('__FRAMEWORK_PATH__','D:\wamp64\www\wl_framework');
define("__APP_ROOT_PATH__", dirname(__WEBROOT__));

$pathParts = pathinfo(__FILE__);define("__PROJECT_NAME__", strtolower($pathParts['filename']));

// require '../vendor/autoload.php';

require_once __FRAMEWORK_PATH__ . DIRECTORY_SEPARATOR  . "init.php";
