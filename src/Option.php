<?php

/**
 * Created by PhpStorm.
 * User: mallanic
 * Date: 01/04/16
 * Time: 11:13
 */
namespace Hos;

use Hos\Stats\Visitor;
use ReflectionClass;
use Zend\Config\Config;
use Zend\Config\Reader;
use Zend\Config\Writer;

define("VENDOR_DIR", __DIR__ . "/../");
define("ROOT_DIR", realpath(VENDOR_DIR . "../../../") . "/");

class Option
{
    CONST DEFAULT_OPTIONS = [
        "environment" => "prod",
        "domain" => "localhost",
        "protocol" => "http",
        "database" => [
            "db" => "default",
            "host" => "localhost",
            "type" => "pgsql",
            "user" => "root",
            "password" => "",
            "generated_classes" => "src/"
        ],
        'api' => [
            'classes' => []
        ],
        'bin' => [
            'compass' => '/usr/bin/compass',
            'yuicompressor' => '/usr/bin/yui-compressor'
        ],
        'twig' => [
            'lexer' => [
                'tagcomment' => ["{#", "#}"],
                'tagblock' => ["{%", "%}"],
                'tagvariable' => ["{{", "}}"],
                'interpolation' => ["#{", "}"]
            ]
        ]
    ];

    CONST USER = "web";
    CONST ROOT_DIR = ROOT_DIR;

    CONST APP_DIR = self::ROOT_DIR . "app/";
    CONST LOG_DIR = self::APP_DIR . "log/";
    CONST CONF_DIR = self::APP_DIR . "conf/";
    CONST STAT_DIR = self::APP_DIR . "stat/";
    CONST TEMPORARY_DIR = self::APP_DIR . "tmp/";

    CONST TEMPORARY_ASSET_DIR = self::TEMPORARY_DIR ."asset/";

    CONST LOG_FILE = self::LOG_DIR . "dev.log";

    CONST NGINX_LOG_DIR = self::LOG_DIR . "nginx/";
    CONST NGINX_LOG_ACCESS_FILE = self::NGINX_LOG_DIR . "access.log";
    CONST NGINX_LOG_ERROR_FILE = self::NGINX_LOG_DIR . "error.log";

    CONST CONF_FILE = self::CONF_DIR . "parameter.yaml";

    CONST ASSET_DIR = self::ROOT_DIR . "asset/";
    CONST PROJECT_DIR = self::ROOT_DIR . "src/";

    CONST VENDOR_DIR = VENDOR_DIR;
    CONST VENDOR_COMPASS_DIR = self::VENDOR_DIR . "compass/";
    CONST VENDOR_API_DOC_DIR = self::VENDOR_DIR . "doc/";
    CONST VENDOR_WEB_DIR = self::VENDOR_DIR . "web/";
    CONST VENDOR_JAVASCRIPT_DIR = self::VENDOR_DIR . "javascript/";
    CONST VENDOR_CONF_DIR = self::VENDOR_DIR . "conf/";

    CONST WRITE_MODE = 0777;

    static private $reader = null;
    static private $writer = null;

    static private $options = null;

    static function get() {
        if (!self::$reader) {
            self::$reader = new Reader\Yaml();
            if (!file_exists(self::CONF_FILE))
                throw new ExceptionExt("No Conf File", "error/no_conf.twig", ['dir' => Option::CONF_FILE, 'parameter' => self::getDefaultYaml()]);
            self::$options = self::$reader->fromFile(self::CONF_FILE);
            self::$options = array_merge(self::DEFAULT_OPTIONS, self::$options);
        }
        return self::$options;
    }

    static function getDefaultYaml() {
        if (!self::$writer) {
            self::$writer = new Writer\Yaml();
        }
        $config = new Config(self::DEFAULT_OPTIONS, true);
        return self::$writer->toString($config);
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
