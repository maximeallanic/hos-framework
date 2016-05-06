<?php
/**
 * Created by PhpStorm.
 * User: mallanic
 * Date: 03/05/16
 * Time: 13:26
 */

namespace Hos\Stats;


class Stats
{
    function visitor($name) {
        return (new Visitor())->getStats()[$name]();
    }
}