<?php

/**
 * Created by PhpStorm.
 * User: mallanic
 * Date: 20/04/16
 * Time: 11:20
 */

namespace Hos\Auth;

abstract class Basic extends \Hos\Model\Collection
{
    /**
     * @return mixed
     */
    static public function getType() {
        return 'basic';
    }

    public abstract function authenticate($user, $password);
}