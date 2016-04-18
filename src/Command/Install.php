<?php

/**
 * Created by PhpStorm.
 * User: mallanic
 * Date: 01/04/16
 * Time: 10:59
 */

namespace Hos\Command;

use Composer\Script\Event;
use Hos\Option;

class Install
{
    static function execute(Event $event) {
        $options = Option::DEFAULT_OPTIONS;
        self::iterateDimensions($options);
        Option::set($options);
    }

    static function iterateDimensions(&$dimensions, $keys = []) {
        foreach ($dimensions as $key=>&$dimension) {
            $tKeys = $keys;
            $tKeys[] = $key;
            if (gettype($dimension) == 'array')
                self::iterateDimensions($dimension, $tKeys);
            else
                $dimension = self::getOption(implode('.', $tKeys), $dimension);
        }
    }

    static function getOption($key, $defautValue) {
        $value = readline("$key [$defautValue]:");
        return (strlen($value) == 0 ? $defautValue : $value);
    }
}