<?php
/**
 * Created by PhpStorm.
 * User: mallanic
 * Date: 03/04/16
 * Time: 17:52
 */

namespace Hos\Command\Base;


use League\CLImate\CLImate;

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

    static function displayTask($taskName, $fn) {
        system("setterm -cursor off");
        echo "[\033[33;5m$taskName\033[0m]\r";

        $result = $fn();
        if ($result)
            echo "[\033[32;5m$taskName\033[0m]\n";
        else
            echo "[\033[31;5m$taskName\033[0m]\n";
        system("setterm -cursor on");
        return $result;
    }

    static function error($error) {
        echo "[\033[31;5m$error\033[0m]\n";
    }
}