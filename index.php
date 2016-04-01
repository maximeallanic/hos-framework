<?php
/**
 * Created by PhpStorm.
 * User: mallanic
 * Date: 31/03/16
 * Time: 18:05
 */

use Hos\Option;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "../autoload.php";

echo Option::get();
/**

define('DEV', true);
define('QUERY', strtok($_SERVER['REQUEST_URI'], '?'));
define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);
if (DEV) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}




function requestFile() {
    if (!preg_match("/^(.*)\.((?:(?!\.).)*)$/", QUERY, $matches))
        return false;
    return $matches;
}

function generateTwig($file) {
    $am = new AssetManager();
    $am->set('jquery', new FileAsset('public/javascripts'));
    $am->set('base_css', new GlobAsset('public/compass'));

    $fm = new FilterManager();
    $fm->set('compass', new CompassFilter('/path/to/parser/sass'));
    $fm->set('yui_css', new CssCompressorFilter('/path/to/yuicompressor.jar'));

    $factory = new AssetFactory('/path/to/asset/directory/');
    $factory->setAssetManager($am);
    $factory->setFilterManager($fm);
    $factory->setDebug(DEV);

    $loader = new Twig_Loader_Filesystem(DOCUMENT_ROOT.'/public/templates/');
    $twig = new Twig_Environment($loader, array());
    $twig->addExtension(new AsseticExtension($factory));
    return $twig->render($file.'.twig', array(
        'name'> 'Fabien'
    ));
}

try {
    if (!($file = requestFile()))
        echo generateTwig('index');
    if ($file[2] == 'html') {
        echo generateTwig($file[1]);
    }
} catch (Exception $e) {
    echo $e->getMessage();
}**/
