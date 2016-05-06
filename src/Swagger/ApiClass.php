<?php
/**
 * Created by PhpStorm.
 * User: mallanic
 * Date: 19/04/16
 * Time: 13:28
 */

namespace Hos\Swagger;


use Hos\ExceptionExt;

class ApiClass extends \Zend_Reflection_Class
{
    CONST API_PARAM = "/^(?<url>\/[a-zA-Z0-9\/{}-]*)(?:\s(?<param>{(?:(?!}).)+}))?/";

    private $methods = [];
    private $apiDoc;

    function __construct($namespace)
    {
        parent::__construct($namespace);
        /** @var \Zend_Reflection_Method $method */
        foreach (parent::getMethods() as $method) {
            $apiMethod = new ApiMethod(parent::getName(), $method->getName());
            if ($apiMethod->isRegistrable()) {
                $url = $apiMethod->getApiUrl();
                if (!isset($this->methods[$url]))
                    $this->methods[$url] = [];
                $this->methods[$url][] = $apiMethod;
            }
        }
    }

    private function getDoc() {
        if (!$this->apiDoc
            && strlen($this->getDocComment()) > 0
            && $tag = $this->getDocblock()->getTag('api'))
            preg_match(self::API_PARAM, $tag->getDescription(), $this->apiDoc);
        return $this->apiDoc;
    }

    function getApiUrl() {
        $doc = $this->getDoc();
        if ($doc && isset($doc['url']))
            return $doc['url'] == '/' ? "" : $doc['url'];
        return "/".$this->getApiName();
    }

    function getApiName() {
        return strtolower($this->getShortName());
    }

    function getApiMethods() {
        return $this->methods;
    }

    function getApiMethodFromRoute($routeRequest) {
        foreach($this->getApiMethods() as $route) {
            /** @var ApiMethod $method */
            foreach ($route as $method) {
                if ($method->routeIs($routeRequest))
                    return $method;
            }

        }
        throw new ExceptionExt('api.no_api_found');
    }

    function getApiMethod($name) {
        foreach($this->methods as $route)
            /** @var ApiMethod $method */
            foreach ($route as $method)
                if ($method->getName() == $name)
                    return $method;
        throw new ExceptionExt('api.no_method');
    }

    function getDescription() {
        if (strlen($this->getDocComment()) > 0 && $description = $this->getDocblock()->getShortDescription())
            return $description;
        return "";
    }

    function generateAuth() {
        return [
            "type" => $this->getMethod('getType')->invoke(null),
            "description" => $this->getDescription(),
            "name" => $this->getApiName(),
            "in" => "header",
        ];
    }
}