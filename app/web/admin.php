<?php
/**
 * Created by PhpStorm.
 * User: yanghua
 * Date: 2018/7/18
 * Time: 14:45
 */
define("ENVIRONMENT","dev"); // "dev" "testing" "production"
define('__WEBROOT__',__DIR__);
define("__APP_ROOT_PATH__", dirname(__WEBROOT__));
define('__FRAMEWORK_PATH__', '/var/www/html/framework');
$pathParts = pathinfo(__FILE__);
define("__PROJECT_NAME__", strtolower($pathParts['filename']));
require_once __APP_ROOT_PATH__.'/vendor/autoload.php';
require_once __FRAMEWORK_PATH__ . DIRECTORY_SEPARATOR  . "init.php";