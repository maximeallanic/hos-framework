<?php
/**
 * Created by PhpStorm.
 * User: mallanic
 * Date: 03/04/16
 * Time: 17:52
 */

namespace Hos\Command\Base;


class Command
{
    static function execute($e) {
        $arguments = $e->getArguments();
        $arguments_c = [];
        foreach ($arguments as $argument) {
            $row = explode('::', $argument);
            $arguments_c[$row[0]] = $row[1];
        }
        new static($arguments_c);
    }
}