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

define("ROOT_DIR", realpath(__DIR__ . "/../../../../") . "/");

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
        ],
        'bin' => [
            'compass' => '/usr/bin/compass',
            'yuicompressor' => '/usr/bin/yui-compressor'
        ],
        'twig' => [
            'lexer' => [
                'tagcomment' => ["<%#", "%>"],
                'tagblock' => ["<%", "%>"],
                'tagvariable' => ["<%=", "%>"],
                'interpolation' => ["#{", "}"]
            ]
        ]
    ];

    CONST ROOT_DIR = ROOT_DIR;
    CONST APP_DIR = self::ROOT_DIR . "app/";
    CONST LOG_DIR = self::APP_DIR . "log/";
    CONST CONF_DIR = self::APP_DIR . "conf/";
    CONST ASSET_DIR = self::ROOT_DIR . "asset/";
    CONST TEMPORARY_DIR = self::APP_DIR . "tmp/";
    CONST TEMPORARY_ASSET_DIR = self::TEMPORARY_DIR ."asset/";

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
            self::$options = array_merge(self::DEFAULT_OPTIONS, self::$options);
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

    static function getBaseUrl() {
        return Option::get()['protocol']."://".Option::get()['domain'];
    }
}