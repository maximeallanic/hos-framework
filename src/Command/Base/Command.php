<?php
/**
 * Created by PhpStorm.
 * User: mallanic
 * Date: 03/04/16
 * Time: 17:52
 */

namespace Hos\Command\Base;


use Hos\Log;
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
        $term = getenv("TERM");

        !$term ?: system("setterm -cursor off");

        echo "[\033[33;5m$taskName\033[0m]";
        echo $term ? "\r" : "\t";
    
        $result = $fn();

        if ($result)
            echo $term ? "[\033[32;5m$taskName\033[0m]\n" : "\e[32;5mOK\e[0m\n";
        else
            echo $term ? "[\033[31;5m$taskName\033[0m]\n" : "\e[31;5mERROR\e[0m\n";

        !$term ?: system("setterm -cursor on");

        if (!$result) {
            Log::error($result);
            die(1);
        }

        return $result;
    }

    static function error($error) {
        echo "[\033[31;5m$error\033[0m]\n";
    }
}
