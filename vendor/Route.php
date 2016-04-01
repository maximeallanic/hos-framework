<?php
/**
 * Created by PhpStorm.
 * User: mallanic
 * Date: 01/04/16
 * Time: 15:04
 */

namespace Hos;

use Assetic\Asset\FileAsset;
use Assetic\Asset\GlobAsset;
use Assetic\AssetManager;
use Assetic\Extension\Twig\AsseticExtension;
use Assetic\Factory\AssetFactory;
use Assetic\Filter\CompassFilter;
use Assetic\Filter\Yui\CssCompressorFilter;
use Assetic\FilterManager;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Glide\Responses\PsrResponseFactory;
use League\Glide\ServerFactory;
use Luracast\Restler\Restler;
use Twig_Environment;
use Twig_Loader_Filesystem;

class Route
{
    private $query;

    public function __construct()
    {
        $this->query = strtok($_SERVER['REQUEST_URI'], '?');
    }

    private function match($regex) {
        if (!preg_match($regex, $this->query, $matches))
            return false;
        return $matches;
    }

    public function initiateAPI($request) {
        $_SERVER['REQUEST_URI'] = $request;
        /** Initiate Restler Object */
        $rest = new Restler(!Option::isDev());

        /** Configuration */
        $rest->setAPIVersion(1);
        $rest->setBaseUrls(Option::getBaseUrl()."/api/");
        $rest->setSupportedFormats('XmlFormat', 'JsonFormat');

        /** Insert API Doc */
        $rest->addAPIClass('Luracast\\Restler\\Resources');

        /** Insert All PHP Class */
        foreach (Option::get()['api']['classes'] as $class)
            $rest->addAPIClass($class);

        /** Start */
        $rest->handle();
    }

    public function initiateImage($file) {
        $service = ServerFactory::create([
            "source" => Option::ASSET_DIR,
            "cache" => Option::TEMPORARY_ASSET_DIR,
            "watermarks" => Option::ASSET_DIR
        ]);
        $service->outputImage($file, $_GET);
    }

    public function dispatch() {
        /** API Doc */
        if ($matches = $this->match('/^\/api\/doc\/(.*)/'))
            return file_get_contents(__DIR__."/../doc/".($matches[1] ? $matches[1] : "index.html"));
        /** Api */
        else if ($matches = $this->match('/^\/api\/(.*)/'))
            $this->initiateAPI($matches[1]);

            /** Twig Element */
        else if ($matches = $this->match('/\/(.*)\.html$/'))
            return (new Twig())->render($matches[1].".twig");

            /** CSS and JS Element */
        else if ($matches = $this->match('/\/(.*\.(css|js))$/'))
            return (new Twig())->renderAssets($matches[1], $matches[2]);

            /** Picture Element */
        else if ($matches = $this->match('/\/(.*\.(gif|jpg|png|png))$/'))
            $this->initiateImage($matches[1]);

            /** Default Element */
        else
            return (new Twig())->render("index.twig");
    }
}