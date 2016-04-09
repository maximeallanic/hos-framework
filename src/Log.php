<?php
/**
 * Created by PhpStorm.
 * User: mallanic
 * Date: 08/04/16
 * Time: 18:41
 */

namespace Hos;


use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Log
{
    /** @var  Logger */
    private static $logger;

    private static function init() {
        /** Log */
        self::$logger = new Logger('hos');
        $logFile = Option::LOG_DIR.Option::get()['environment'].".log";
        self::$logger->pushHandler(new StreamHandler($logFile, Logger::WARNING));
    }

    static function alert($message, $params = []) {
        if (!self::$logger)
            self::init();
        self::$logger->addAlert($message, $params);
    }

    static function error($message, $params = []) {
        if (!self::$logger)
            self::init();
        self::$logger->addError($message, $params);
    }
}