<?php
/**
 * Created by PhpStorm.
 * User: mallanic
 * Date: 01/04/16
 * Time: 15:04
 */

namespace Hos;


use Klein\Klein;
use Luracast\Restler\Restler;

class Route
{
    private $route;
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

    public function route() {
        if ($matches = $this->match('/^\/api\/doc\/(.*)/'))
            echo file_get_contents(__DIR__."/../doc/".($matches[1] ? $matches[1] : "index.html"));
        else if ($matches = $this->match('/^\/api\//')) {
            /** Initiate Restler Object */
            $rest = new Restler(!Option::isDev());

            /** Configuration */
            $rest->setAPIVersion(1);
            $rest->setSupportedFormats('XmlFormat', 'JsonFormat');

            /** Insert API Doc */
            $rest->addAPIClass('Luracast\\Restler\\Resources');

            /** Insert All PHP Class */
            foreach (Option::get()->api->classes as $class) {
                $rest->addAPIClass($class);
            }

            /** Start */
            $rest->handle();
        }
    }
}