<?php
define("SEP","\\");
define("BASE_DIR", dirname(__FILE__, 2).SEP);
define("BASE_DIR_SRC", BASE_DIR."src");

require_once(BASE_DIR.'/vendor/autoload.php');
require_once(BASE_DIR.'/public/bootstrap.php');

use PangzLab\DMSMonitoring\Config\ApiSetting;
use PangzLab\DMSMonitoring\Infra\DependencyInjection;
use PangzLab\DMSMonitoring\Persistence\SqliteDatabase;
use PangzLab\DMSMonitoring\Persistence\SqliteDatabaseOperation;

$callType = (php_sapi_name() == "cli")? "cli": "web";

$appName = "";
$appType = "";
$params  = null;

if($callType == "cli") {
    $appName = $argv[1];
    $appType = "cron";
} else {
    $uri     = explode("?", $_SERVER["REQUEST_URI"]);
    $appName = $uri[0];
    $params  = $uri[1] ?? "";
    $appType = "web";
}

if(!isset(APP_COLLECTION[$appType][$appName])) {
    print json_encode(["error" => "Unregistered Appplication"]);
    exit;
}

$sqliteDb = new SqliteDatabase(ApiSetting::DB_PATH);
$di = new DependencyInjection([
    "SqliteDbOperation" => new SqliteDatabaseOperation($sqliteDb),
]);

$appName = APP_COLLECTION[$appType][$appName];
$app = new $appName($di);
$app->execute($params);