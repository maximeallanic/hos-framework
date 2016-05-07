<?php
/**
 * Created by PhpStorm.
 * User: mallanic
 * Date: 08/04/16
 * Time: 18:41
 */

namespace Hos;


use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Log
{
    /** @var  Logger */
    private static $logger;

    private static function init() {
        /** Log */
        self::$logger = new Logger('hos');
        $logFile = Option::LOG_DIR."dev.log";

        $dateFormat = "Y-m-d G:i:s";
        $output = "[%datetime%](%level_name%)\t%message%\n";
        $formatter = new LineFormatter($output, $dateFormat);
        $streamHandler = new StreamHandler($logFile, Option::isDev() ? Logger::INFO : Logger::ALERT);
        $streamHandler->setFormatter($formatter);
        self::$logger->pushHandler($streamHandler);
    }

    static function alert($message, $params = []) {
        if (!self::$logger)
            self::init();
        self::$logger->alert(sprintf($message, $params));
    }

    static function error($message, $params = []) {
        if (!self::$logger)
            self::init();
        self::$logger->error(sprintf($message, $params));
    }

    static function info($message, $params = []) {
        if (!self::$logger)
            self::init();
        self::$logger->info(sprintf($message, $params));
    }
}