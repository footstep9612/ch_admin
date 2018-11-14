<?php
header('Access-Control-Allow-Origin:http://localhost');
header('Access-Control-Allow-Credentials:true');
//解决js跨域
define("ENVIRONMENT","dev"); // "dev" "testing" "production"
define('__WEBROOT__',__DIR__);
define('__FRAMEWORK_PATH__','D:/appserv/www/wl_framework');
define("__APP_ROOT_PATH__", dirname(__WEBROOT__));

$pathParts = pathinfo(__FILE__);define("__PROJECT_NAME__", strtolower($pathParts['filename']));

// require '../vendor/autoload.php';

require_once __FRAMEWORK_PATH__ . DIRECTORY_SEPARATOR  . "init.php";
