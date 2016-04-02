<?php
/**
 * Created by PhpStorm.
 * User: mallanic
 * Date: 02/04/16
 * Time: 15:35
 */

namespace Hos;


use Propel\Runtime\Connection\ConnectionManagerSingle;
use Propel\Runtime\Propel;

class BDD
{
    function __construct()
    {
        $options = Option::get()['database'];

        $serviceContainer = Propel::getServiceContainer();
        $serviceContainer->checkVersion('2.0.0-dev');
        $serviceContainer->setAdapterClass('default', $options['type']);
        $manager = new ConnectionManagerSingle();

        $manager->setConfiguration(array (
            'dsn' => "$options[type]:host=$options[host];dbname=$options[db]",
            'user' => $options['user'],
            'password' => $options['password'],
            'settings' =>
                array (
                    'charset' => 'utf8',
                    'queries' =>
                        array (
                            'utf8' => 'SET NAMES \'UTF8\'',
                        ),
                ),
            'model_paths' =>
                array (
                    0 => 'src',
                    1 => 'vendor',
                ),
        ));
        $manager->setName('default');
        $serviceContainer->setConnectionManager('default', $manager);
        $serviceContainer->setDefaultDatasource('default');
    }
}