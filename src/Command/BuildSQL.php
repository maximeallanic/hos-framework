<?php
/**
 * Created by PhpStorm.
 * User: mallanic
 * Date: 02/04/16
 * Time: 15:28
 */

namespace Hos\Command;


use Hos\Option;
use Zend\Config\Config;
use Zend\Config\Writer\Yaml;

class BuildSQL
{

    static function generateConf() {
        $options = Option::get()['database'];
        $conf = [
            'propel' => [
                'database' => [
                    'connections' => [
                        $options['db'] => [
                            'adapter'    => $options['type'],
                            'dsn'        => "$options[type]:host=$options[host];dbname=$options[db]",
                            'user'       => $options['user'],
                            'password'   => $options['password'],
                            'attributes' => []
                        ]
                    ]
                ],
                'runtime' => [
                    'defaultConnection' => $options['db'],
                    'connections' => [$options['db']]
                ],
                'generator' => [
                    'defaultConnection' => $options['db'],
                    'connections' => [$options['db']]
                ]
            ]
        ];
        $writer = new Yaml();
        $conf = new Config($conf);
        $writer->toFile(Option::TEMPORARY_DIR."propel.yaml", $conf);
    }

    static function execute() {
        self::generateConf();
        $commands = array(
            "vendor/bin/propel sql:build --schema-dir '".Option::CONF_DIR."' --output-dir '".Option::TEMPORARY_DIR."' --overwrite --config-dir '".Option::TEMPORARY_DIR."'",
            "vendor/bin/propel model:build --schema-dir '".Option::CONF_DIR."' --output-dir '".Option::get()['database']['generated_classes']."' --config-dir '".Option::TEMPORARY_DIR."'",
            "vendor/bin/propel sql:insert --sql-dir '".Option::TEMPORARY_DIR."' --config-dir '".Option::TEMPORARY_DIR."'",
        );
        foreach ($commands as $command) {
            echo $command."\n";
            exec($command, $output, $result);
            if ($result < 0)
                return false;
        }
    }
}