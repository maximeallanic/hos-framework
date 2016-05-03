<?php
/**
 * Created by PhpStorm.
 * User: mallanic
 * Date: 31/03/16
 * Time: 18:05
 */

$startTime = microtime(true);

require_once "../../autoload.php";

use Hos\Stats\Visitor;
use Hos\BDD;
use Hos\ExceptionExt;
use Hos\Header;
use Hos\Option;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

try {
    if (!extension_loaded('yaml'))
        throw new ExceptionExt("No Yaml Library");
    if (!extension_loaded('gd'))
        throw new ExceptionExt("No GD Library");

    if (Option::isDev()) {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
    }

    /** Stats */
    Visitor::addVisitor();

    /** Route */
    $route = new \Hos\Route();

    $out = $route->dispatch();
    $timeExecution = ((microtime(true) - $startTime));
    Header::add('Time-Execution', $timeExecution."s");
    Header::add('Server', 'Hos');

} catch(ExceptionExt $e) {
    $out = $e->render();
}

echo $out;
