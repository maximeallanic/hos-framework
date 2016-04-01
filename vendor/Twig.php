<?php
/**
 * Created by PhpStorm.
 * User: mallanic
 * Date: 01/04/16
 * Time: 16:48
 */

namespace Hos;


use Assetic\Asset\FileAsset;
use Assetic\Asset\GlobAsset;
use Assetic\AssetManager;
use Assetic\AssetWriter;
use Assetic\Extension\Twig\AsseticExtension;
use Assetic\Extension\Twig\TwigFormulaLoader;
use Assetic\Extension\Twig\TwigResource;
use Assetic\Factory\AssetFactory;
use Assetic\Factory\LazyAssetManager;
use Assetic\Factory\Worker\CacheBustingWorker;
use Assetic\Filter\CompassFilter;
use Assetic\Filter\Yui\CssCompressorFilter;
use Assetic\Filter\Yui\JsCompressorFilter;
use Assetic\FilterManager;
use Twig_Environment;
use Twig_Loader_Filesystem;

class Twig
{
    private $twig;
    private $factory;
    private $twigLoader;

    function __construct()
    {
        $am = new AssetManager();
        $am->set('js', new FileAsset(Option::ASSET_DIR));
        $am->set('css', new GlobAsset(Option::ASSET_DIR));

        $fm = new FilterManager();
        $fm->set('compass', new CompassFilter(Option::get()['bin']['compass']));
        $fm->set('yui_css', new CssCompressorFilter(Option::get()['bin']['yuicompressor']));
        $fm->set('yui_js', new JSCompressorFilter(Option::get()['bin']['yuicompressor']));

        $this->factory = new AssetFactory(Option::ASSET_DIR);
        $this->factory->setAssetManager($am);
        $this->factory->setFilterManager($fm);
        $this->factory->setDebug(Option::isDev());

        $this->twigLoader = new Twig_Loader_Filesystem(Option::ASSET_DIR);
        $argument = array(
            'debug' => Option::isDev()
        );
        if (!Option::isDev()) {
            $argument['cache'] = Option::TEMPORARY_DIR;
            $this->factory->addWorker(new CacheBustingWorker());
        }
        $this->twig = new Twig_Environment($this->twigLoader, $argument);
        $this->twig->addGlobal('api', new Api());
        $this->twig->addExtension(new AsseticExtension($this->factory));
    }

    function render($file, $array = []) {
        $cache = Option::TEMPORARY_ASSET_DIR.md5($file);
        $render = $this->twig->render($file, $array);
        if (Option::isDev() || !file_exists($cache)) {
            $am = new LazyAssetManager($this->factory);
            $am->setLoader('twig', new TwigFormulaLoader($this->twig));
            $resource = new TwigResource($this->twigLoader, $file);
            $am->addResource($resource, 'twig');
            $writer = new AssetWriter(Option::TEMPORARY_ASSET_DIR);
            $writer->writeManagerAssets($am);
            file_put_contents($cache, '');
        }
        return $render;
    }

    function renderAssets($file, $type) {
        $file = Option::TEMPORARY_ASSET_DIR.$file;
        if (!file_exists($file))
            throw new ExceptionExt("No File", 404);
        $mimeType = [
            'css' => 'text/css',
            'js' => 'application/javascript'
        ];
        Header::set('Content-Type', $mimeType[$type]);
        return file_get_contents($file);
    }
}