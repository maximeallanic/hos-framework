<?php
/**
 * Created by PhpStorm.
 * User: mallanic
 * Date: 31/03/16
 * Time: 18:05
 */

$startTime = microtime(true);

require_once "../../autoload.php";

use Hos\Header;
use Hos\Option;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    if (!extension_loaded('yaml'))
        throw new Exception("No Yaml Library");

    if (Option::isDev()) {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
    }

    /** Log */
    $defaultLogger = new Logger('hos');
    $logFile = Option::LOG_DIR.Option::get()['environment'].".log";
    $defaultLogger->pushHandler(new StreamHandler($logFile, Logger::WARNING));

    /** Route */
    $route = new \Hos\Route();
    echo $route->dispatch();

} catch(Exception $e) {
    echo "error ".$e->getMessage();
    //echo (new Twig())->render("error.twig", ['error' => $e]);
}

$endTime = microtime(true);

Header::set('Time-Execution', ($endTime - $startTime)."s");
Header::set('Server', 'Hos');