<?php
/**
 * Created by PhpStorm.
 * User: mallanic
 * Date: 01/04/16
 * Time: 17:08
 */

namespace Hos\Command;


use Luracast\Restler\Resources;
use Luracast\Restler\Restler;
use Luracast\Restler\Routes;
use Luracast\Restler\Util;

class GenerateJSApi
{
    static function execute() {
        $routes
            = Util::nestedValue(Routes::toArray(), "v1")
            ? : array();
        print_r($routes);
    }
}