<?php

/**
 * Created by PhpStorm.
 * User: mallanic
 * Date: 03/04/16
 * Time: 17:27
 */

namespace Hos\Twig;

use Twig_Extension;

class Extensions extends Twig_Extension
{

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
       return 'hos';
    }

    public function getFilters()
    {
        return array(
            'image' => new \Twig_Filter_Method($this, 'image')
        );
    }

    public function image($path, $parameters) {
        return $path."?".implode('&', array_map(function ($v, $k) {
            return sprintf("%s=%s", $k, $v); },
            $parameters,
            array_keys($parameters)
        ));
    }
}