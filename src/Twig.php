<?php
/**
 * Created by PhpStorm.
 * User: mallanic
 * Date: 01/04/16
 * Time: 16:48
 */

namespace Hos;


use Assetic\Asset\AssetCollection;
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
use Assetic\Filter\UglifyJs2Filter;
use Assetic\Filter\Yui\CssCompressorFilter;
use Assetic\Filter\Yui\JsCompressorFilter;
use Assetic\FilterManager;
use Hos\Stats\Stats;
use Hos\Twig\Extensions;
use Twig_Environment;
use Twig_Extension_Optimizer;
use Twig_Extension_Sandbox;
use Twig_Lexer;
use Twig_Loader_Filesystem;

class Twig
{
    private $twig;
    private $factory;
    private $twigLoader;

    function __construct()
    {
        $am = new AssetManager();
        $am->set('vendor', new AssetCollection(array(
            new FileAsset(Option::VENDOR_JAVASCRIPT_DIR."main.js"),
            new GlobAsset(Option::VENDOR_JAVASCRIPT_DIR."**/*.js")
        )));

        $fm = new FilterManager();
        $compassFilter = new CompassFilter(Option::get()['bin']['compass']);
        $compassFilter->addLoadPath(Option::VENDOR_COMPASS_DIR);
        $compassFilter->setCacheLocation(Option::TEMPORARY_DIR);

        if (!Option::isDev()) {
            $compassFilter->setNoLineComments(true);
            $compassFilter->setDebugInfo(false);
            $compassFilter->setStyle("compressed");
        }
        //$compassFilter->setNoCache(true);
        $compassFilter->setTimeout(300000);
        $fm->set('compass', $compassFilter);
        $fm->set('yui_css', new CssCompressorFilter(Option::get()['bin']['yuicompressor']));
        $uglify = new UglifyJs2Filter(Option::get()['bin']['uglify']);
        //$fm->set('uglify', $uglify);

        $this->factory = new AssetFactory(Option::ASSET_DIR);
        $this->factory->setAssetManager($am);
        $this->factory->setFilterManager($fm);
        $this->factory->setDebug(Option::isDev());

        if (!file_exists(Option::TEMPORARY_ASSET_DIR))
            mkdir(Option::TEMPORARY_ASSET_DIR, Option::WRITE_MODE, true);

        $this->twigLoader = new Twig_Loader_Filesystem(Option::ASSET_DIR);

        /** Set For Environment */
        $argument = array(
            'debug' => Option::isDev(),
            'optimizations' => -1
        );
        if (!Option::isDev()) {
            $argument['cache'] = Option::TEMPORARY_DIR;
            $this->factory->addWorker(new CacheBustingWorker());
        }

        $this->twig = new Twig_Environment($this->twigLoader, $argument);

        /** Customize Twig */


        $this->twig->addGlobal('api', new Api());
        $this->twig->addGlobal('app', new Option());
        $this->twig->addGlobal('stat', new Stats());
        //$this->twig->addExtension(new WhitespaceCollapser(['twig', 'html', 'svg', 'xml']));
        $this->twig->addExtension(new AsseticExtension($this->factory));
        $this->twig->addExtension(new Twig_Extension_Optimizer());
        $this->twig->addExtension(new Extensions());

        $optionsLexer = Option::get()['twig']['lexer'];
        $lexer = new Twig_Lexer($this->twig, array(
            'tag_comment'   => $optionsLexer['tagcomment'],
            'tag_block'     => $optionsLexer['tagblock'],
            'tag_variable'  => $optionsLexer['tagvariable'],
            'interpolation' => $optionsLexer['interpolation'],
        ));
        $this->twig->setLexer($lexer);
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
}
