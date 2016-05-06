<?php
/**
 * Created by PhpStorm.
 * User: mallanic
 * Date: 19/04/16
 * Time: 15:58
 */

namespace Hos\Swagger;


class Request
{

    static $body;
    static $headers;
    static $forms;
    static $paths;
    static $queries;

    static public function getBody() {
        if (!self::$body)
            self::$body = json_decode(file_get_contents('php://input'), true);
        return self::$body;
    }

    static public function getHeaders() {
        if (!self::$headers)
            self::$headers = getallheaders();
        return self::$headers;
    }

    static public function getForms() {
        if (!self::$forms)
            self::$forms = $_POST;
        return self::$forms;
    }

    static public function getPaths() {
        return self::$paths;
    }

    static public function setPaths($paths) {
        self::$paths = $paths;
    }

    static public function getQueries() {
        if (!self::$queries)
            self::$queries = $_GET;
        return self::$queries;
    }

    static public function getRequestType() {
        return $_SERVER['REQUEST_METHOD'];
    }
}