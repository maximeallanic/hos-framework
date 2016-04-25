<?php
/**
 * Created by PhpStorm.
 * User: mallanic
 * Date: 01/04/16
 * Time: 15:04
 */

namespace Hos;

use League\Flysystem\Util\MimeType;
use League\Glide\ServerFactory;
use League\Glide\Responses\SymfonyResponseFactory;
use Luracast\Restler\Restler;

class Route
{
    static $DEFAULT_ROUTE = [];
    private $query;

    public function __construct()
    {
        $this->query = strtok($_SERVER['REQUEST_URI'], '?');

        self::$DEFAULT_ROUTE = [
            '/^\/api\/doc\/(.*)/' => function ($matches) {
                return $this->getFile(Option::VENDOR_API_DOC_DIR.($matches[1] ? $matches[1] : "index.html"));
            },
            '/^\/api\/(.*)/' => function ($matches) {
                return $this->initiateAPI($matches[1]);
            },
            '/\/(.*)\.html$/' => function ($matches) {
                return $this->renderTwig($matches[1]);
            },
            '/\/(.*\.(css|js))$/' => function ($matches) {
                return $this->getFile(Option::TEMPORARY_ASSET_DIR.$matches[1]);
            },
            '/\/(.*\.(gif|jpg|png|png))$/' => function ($matches){
                return $this->renderImage($matches[1]);
            },
            '/\/(.*)$/' => function ($matches) {
                return $this->getFile(Option::ASSET_DIR.$matches[1]);
            },
            '/(.*)/' => function ($matches) {
                return $this->renderTwig("index");
            }
        ];
    }

    public function getFile($file) {
        if (!file_exists($file))
            return false;
        $mimeType = MimeType::detectByFilename($file);
        Header::add("Content-Type", $mimeType);
        return file_get_contents($file);
    }

    public function renderTwig($file) {
        if (!file_exists(Option::ASSET_DIR.$file.".twig"))
            return false;
        Header::add("Content-Type", "text/html");
        $twig = new Twig();
        return $twig->render($file.".twig");
    }

    private function match($regex) {
        if (!preg_match($regex, $this->query, $matches))
            return false;
        return $matches;
    }

    public function initiateAPI($request) {
        //$_SERVER['REQUEST_URI'] = $request;
        /** Initiate Restler Object */
        //$rest = new Restler(!Option::isDev());
        $rest = new Api();

        /** Configuration */
        $rest->setAPIVersion(1);
        $rest->setBaseUrl(Option::getBaseUrl()."/api");
        //$rest->setSupportedFormats('XmlFormat', 'JsonFormat');

        //$rest->addAPIClass("Resources");
        $rest->addApi('Hos\\Translator');

        /** Insert All PHP Class */
        foreach (Option::get()['api'] as $class)
            $rest->addApi($class);

        /** Start */
        return $rest->handle($request);
    }

    public function renderImage($file) {
        if (!file_exists(Option::ASSET_DIR.$file))
            return false;
        $service = ServerFactory::create([
            "source" => Option::ASSET_DIR,
            "cache" => Option::TEMPORARY_ASSET_DIR,
            "watermarks" => Option::ASSET_DIR
        ]);
        $cachedPath = $service->makeImage($file, $_GET);
        $mimeType = MimeType::detectByFilename($file);
        Header::add("Content-Type", $mimeType);
        return file_get_contents(Option::TEMPORARY_ASSET_DIR.$cachedPath);
    }

    public function dispatch() {
        foreach(self::$DEFAULT_ROUTE as $reg => $function)
            if ($matches = $this->match($reg))
                if ($result = $function($matches))
                    return $result;
        throw new ExceptionExt("Not Found", 404);
    }
}