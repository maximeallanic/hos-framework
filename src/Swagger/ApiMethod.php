<?php
/**
 * Created by PhpStorm.
 * User: mallanic
 * Date: 19/04/16
 * Time: 13:40
 */

namespace Hos\Swagger;


use Hos\ExceptionExt;

class ApiMethod extends \Zend_Reflection_Method
{
    CONST API_PARAM = "/^(?<url>\/[a-zA-Z0-9\/{}-]*)(?:\s(?<param>{(?:(?!}).)+}))?/";
    CONST PROHIBITED_METHOD = [
        '__construct',
        '__destruct'
    ];
    private $parameters = [];
    private $apiDoc;

    function __construct($class, $name)
    {
        parent::__construct($class, $name);
        /** @var \Zend_Reflection_Parameter $parameter */
        foreach ($this->getParameters() as $parameter) {
            $this->parameters[$parameter->getName()] = new ApiParameter($parameter);
        }
    }

    public function getApiName() {
        return strtolower($this->getShortName());
    }

    private function getDoc($method = null) {
        if (!$method)
            $method = $this;

        if (!$this->apiDoc
            && strlen($method->getDocComment()) > 0
            && $tag = $method->getDocblock()->getTag('api')) {
            if (preg_match(self::API_PARAM, $tag->getDescription(), $this->apiDoc))
                if (isset($this->apiDoc['param']))
                    $this->apiDoc['param'] = json_decode($this->apiDoc['param'], true);
        }
        else if ($method->getDeclaringClass()->getParentClass()
            && $method->getDeclaringClass()->getParentClass()->hasMethod($this->getName()))
            $this->getDoc($this->getDeclaringClass()->getParentClass()->getMethod($this->getName()));

        return $this->apiDoc;
    }

    private function getParam($name) {
        $doc = $this->getDoc();
        if ($doc && isset($doc['param']))
            return isset($doc['param'][$name]) ? $doc['param'][$name] : false;
        return false;
    }

    public function getApiUrl() {
        $doc = $this->getDoc();
        if ($doc && isset($doc['url']))
            return $doc['url'] == '/' ? "" : $doc['url'];
        return "/".$this->getApiName();
    }

    public function getApiParameters() {
        return $this->parameters;
    }

    public function getApiParameter($name) {
        if (!isset($this->parameters[$name]))
            throw new ExceptionExt("api.no_parameter");
        return $this->parameters[$name];
    }

    private function getDescription() {
        if (strlen($this->getDocComment()) > 0 && $description = $this->getDocblock()->getShortDescription())
            return $description;
        return "";
    }

    public function getMethod() {
        $doc = $this->getDoc();
        return isset($doc['param']) && isset($doc['param']['method']) ? $doc['param']['method'] : "GET";
    }

    public function isRegistrable() {
        if (in_array($this->getShortName(), self::PROHIBITED_METHOD)
            || !$this->isPublic()
            || $this->isStatic())
            return false;
        if (strlen($this->getDocComment()) > 0
            && $this->getDocblock()->hasTag('noApi'))
            return false;
        return true;
    }

    public function routeIs($route) {
        $replaced = preg_replace("/{([a-zA-Z0-9]+)}/", "(?<$1>[a-zA-Z0-9]+)", $this->getApiUrl());
        $replaced = str_replace('/', "\/", $replaced);
        if (strlen($replaced) <= 0)
            $replaced = '\/';
        if (preg_match("/^$replaced(\.[a-zA-Z]+)?$/", "/$route", $paths)) {
            if (($this->getParam('method') ?: 'GET') == Request::getRequestType()) {
                Request::setPaths($paths);
                return true;
            }
        }
        return false;
    }

    public function generateDoc() {
        $out = [
            "method" => $this->getMethod(),
            "nickname" => $this->getShortName(),
            "authorizations" => [],
            "responseMessages" => [],
            "type" => "void",
            "security" => "user",
            // "responseClass" => $method->getReturnType(),
            "summary" => $this->getDescription(),
            "notes" => $this->getDescription()
        ];
        /** @var ApiParameter $parameter */
        foreach ($this->parameters as $parameter) {
            $out['parameters'][] = $parameter->generateDoc();
        }

        return $out;
    }
}