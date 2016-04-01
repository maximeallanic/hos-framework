<?php

/**
 * Created by PhpStorm.
 * User: mallanic
 * Date: 01/04/16
 * Time: 11:13
 */
namespace Hos;

use Zend\Config\Config;
use Zend\Config\Reader;
use Zend\Config\Writer;

class Option
{
    CONST DEFAULT_OPTIONS = [
        "environment" => "prod",
        "database" => [
            "host" => "localhost",
            "type" => "pgsql",
            "user" => "root",
            "password" => ""
        ],
        'api' => [
            'namespace' => 'Api'
        ]
    ];

    CONST ROOT_DIR = __DIR__ . "/../../../../";
    CONST APP_DIR = self::ROOT_DIR . "app/";
    CONST LOG_DIR = self::APP_DIR . "log/";
    CONST CONF_DIR = self::APP_DIR . "conf/";

    CONST CONF_FILE = self::CONF_DIR . "conf.yaml";

    static private $reader = null;
    static private $writer = null;

    static private $options = null;

    static function get() {
        if (!self::$reader) {
            self::$reader = new Reader\Yaml();
            if (!file_exists(self::CONF_FILE))
                throw new \Exception("No Conf File");
            self::$options = self::$reader->fromFile(self::CONF_FILE);
        }
        return self::$options;
    }

    static function isDev() {
        return self::get()['environment'] == 'dev';
    }

    static function set($array) {
        if (!self::$writer) {
            self::$writer = new Writer\Yaml();
        }
        $config = new Config($array, true);
        self::$writer->toFile(self::CONF_FILE, $config);
            //throw new \Exception("No Write Access");
    }
}