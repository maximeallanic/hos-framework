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

    }
}