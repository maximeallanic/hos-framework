<?php
/**
 * Created by PhpStorm.
 * User: mallanic
 * Date: 01/04/16
 * Time: 17:29
 */

namespace Hos;


use Exception;
use Twig_Environment;
use Twig_Loader_Filesystem;

class ExceptionExt extends Exception
{

    private $block;
    private $options;

    public function __construct($message = null, $block = null, $options = [], $code = 0, Exception $previous = null)
    {
        $this->options = $options;
        $this->block = $block;
        parent::__construct($message, $code, $previous);

    }

    function render() {
        $twigLoader = new Twig_Loader_Filesystem(Option::VENDOR_WEB_DIR);
        $twig = new Twig_Environment($twigLoader);
        return $twig->render("error.twig", [
            'options' => $this->options,
            'block_error' => $this->block,
            'message' => $this->message,
            'code' => $this->code
        ]);
    }
}