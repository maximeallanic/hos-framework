<?php
/**
 * Created by PhpStorm.
 * User: mallanic
 * Date: 01/04/16
 * Time: 19:02
 */

namespace Hos;


class Header
{
    static public function add($key, $value) {
        header("$key: $value");
    }

    static private function defaultHeader() {
        self::add('Server', 'Hos 0.0.1');
        self::add('X-Powered-By', 'Hos 0.0.1');
        self::add('X-Frame-Options', 'SAMEORIGIN');
        self::add('X-XSS-Protection', '1; mode=block');
        self::add('X-Content-Security-Policy', 'allow "self";');
        self::add('X-Content-Type-Options', 'nosniff');
        self::add('X-UA-Compatible','IE=Edge,chrome=1');
        self::add('Access-Control-Allow-Origin', Option::get()["domain"]);
    }
}