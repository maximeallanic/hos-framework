<?php
/**
 * Created by PhpStorm.
 * User: mallanic
 * Date: 09/04/16
 * Time: 14:59
 */

require_once __DIR__."/../../../autoload.php";

use Hos\Command\Base\Command;
use Hos\ExceptionExt;
use Hos\Log;
use Hos\Option;

/** Create User BDD if not exist */
function createUser($user, $password) {
    $t = shell_exec("sudo -i -u postgres psql -c \"SELECT 1 FROM pg_roles WHERE rolname='$user';\"");
    preg_match("/\(([0-9]+)\s+rows?\)/m", $t, $matches);
    $nbRows = intval($matches[1]);
    if ($nbRows == 0)
        shell_exec("sudo -i -u postgres psql -c \"CREATE USER $user WITH PASSWORD '$password'; ALTER USER $user WITH SUPERUSER;\"");
}

/** Create BDD if not exist */
function createBDD($name, $user) {
    $t = shell_exec("sudo -i -u postgres psql -c \"SELECT datname FROM pg_database
 WHERE datistemplate = false AND datname = '$name';\"");
    preg_match("/\(([0-9]+)\s+rows?\)/m", $t, $matches);
    $nbRows = intval($matches[1]);
    if ($nbRows == 0) {
        $t = shell_exec("sudo -i -u postgres psql -c \"CREATE DATABASE $name WITH OWNER = $user\"");
        \Hos\Command\BuildSQL::execute();
    }
}

/** Wait Postgre Starting */
try {

    $class = new ReflectionClass(Option::class);
    foreach ($class->getConstants() as $constantName => $constantValue)
        if (preg_match("/_DIR$/", $constantName) && !file_exists($constantValue))
            mkdir($constantValue);

    /** PostgreSQL */
    Command::displayTask("Start PostgreSQL", function () {
        Log::info(shell_exec("service postgresql start"));
        do {
            sleep(1);
            exec("sudo -i -u postgres psql -c \"SELECT datname FROM pg_database\" > /dev/null 2>&1", $output, $return);
        } while ($return);
    }) ?: die();


    /** PHP */
    Command::displayTask("Start PHP 7.0", function () {
        if (!file_exists("/run/php"))
            mkdir("/run/php");
        Log::info(shell_exec("/usr/sbin/php-fpm7.0 -c ".Option::VENDOR_CONF_DIR."/php/php.ini -D"));
    }) ?: die();


    /** NGINX */
    Command::displayTask("Start NGINX", function () {
        $options = Option::get();
        $directive = [
            "DOMAIN" => $options['domain'],
            "DEV" => Option::isDev() ? '1' : '0'
        ];
        $env = implode(' ', array_map(
            function ($v, $k) { return sprintf("%s=%s", $k, $v); },
            $directive,
            array_keys($directive)
        ));
        Log::info(shell_exec("env $env /usr/local/openresty/nginx/sbin/nginx -c ".Option::VENDOR_CONF_DIR."/nginx/dev.conf"));
    });

    /*
    echo "Start Cron\n";
    Log::info(shell_exec("service cron start"));

    //$options = Option::get()['database'];
    //createUser($options['user'], $options['password']);
	//createBDD($options['db'], $options['user']);
    //echo shell_exec("lsof -nP -i | grep LISTEN");*/
} catch (ExceptionExt $e) {
    Command::error($e->getMessage());
}
