<?php
/**
 * Created by PhpStorm.
 * User: mallanic
 * Date: 01/04/16
 * Time: 18:48
 */

namespace Hos;


class Api
{

    CONST PATH = "/resources/%s.%s";
    CONST API_PATH_RESOURCES = "resources";
    CONST API_PATH_CLASS = "class";
    CONST API_PATH_FUNCTION = "function";
    CONST API_PATH_FORMAT = "format";
    CONST API_PATH = "/^(?<".self::API_PATH_RESOURCES.">\/?resources)?(?:\/?(?<".self::API_PATH_CLASS.">[a-z]+)(?:\/(?<".self::API_PATH_FUNCTION.">[a-z]+))?)?(?:\.(?<".self::API_PATH_FORMAT.">[a-z]+))?/";

    private $OUT = [
        "apiVersion" => "1",
        "swaggerVersion" => "1.1",
        "basePath" => "http://localhost/api/",
        "produces" => [
            "application/json"
        ],
        "consumes" => [
            "application/json"
        ],
        "apis" => [

        ]
    ];

    private $apis = [];

    public function setApiVersion($version) {
        $this->OUT['apiVersion'] = $version;
    }

    public function setBaseUrl($baseUrl) {
        $this->OUT['basePath'] = $baseUrl;
    }

    public function addAPIClass($namespace) {
        $class = new \Zend_Reflection_Class($namespace);
        if (!$class)
            throw new ExceptionExt("api.no_class");
        $className = strtolower($class->getShortName());
        $this->apis[$className] = [];
        $this->apis[$className][0] = $class;
        /** @var \Zend_Reflection_Method $method */
        foreach ($class->getMethods() as $method) {
            if ($method->isPublic()) {
                $methodName = strtolower($method->getName());
                $this->apis[$className][$methodName] = $method;
            }
        }
    }

    public function getApi($name) {
        return $this->OUT;
    }

    /**
     * @param $method \Zend_Reflection_Method
     * @return array
     */
    public function getResourcesParameter($method) {
        $parameters = [];
        /** @var \Zend_Reflection_Parameter $parameter */
        foreach ($method->getParameters() as $parameter) {
            $parameters[] = [
                "name" => $parameter->getName(),
                "description" => "",
                "paramType" => $method->getDocComment() ? $parameter->getType() : "int",
                "required" => !$parameter->isOptional(),
                "defaultValue" => $parameter->isOptional() ? $parameter->getDefaultValue() : null,
                "dataType" => $method->getDocComment() ? $parameter->getType() : "int"
            ];
        }
        return $parameters;
    }

    public function getResourcesClass($className, $format) {
        /** @var \Zend_Reflection_Class $class */
        $class = $this->apis[$className][0];
        $functions = [];
        /** @var \Zend_Reflection_Method $method */
        foreach ($this->apis[$className] as $key => $method) {
            if (!is_numeric($key) && $key[0] != '_') {
                $functionName = $key;
                $tags = $this->getTagToArray($method);
                $functions[] = [
                    "path" => sprintf(self::PATH, "$className/$functionName", $format),
                    "description" => $this->getDescription($class),
                    "operations" => [
                        "httpMethod" => isset($tags['url']) ? $tags['url'][0] : "GET",
                        "nickname" => $method->getShortName(),
                        "responseClass" => $method->getReturnType(),
                        "parameters" => $this->getResourcesParameter($method),
                        "summary" => $this->getDescription($class),
                        "notes" => $this->getDescription($method),
                        "errorResponses" => []
                    ]
                ];
            }
        }
        return $functions;
    }

    public function getResourcesMethod($className, $methodName, $format) {

    }

    public function getResources($className = null, $functionName = null, $format = "json") {
        if ($className && isset($this->apis[$className])) {
            if ($functionName && isset($this->apis[$functionName]))
                $this->OUT['apis'] = $this->getResourcesMethod($className, $functionName, $format);
            else
                $this->OUT['apis'] = $this->getResourcesClass($className, $format);
        }
        else {
            $classes = [];
            foreach ($this->apis as $name=>$class) {
                /** @var \Zend_Reflection_Class $class */
                $class = $class[0];
                $classes[] = [
                    "path" => sprintf(self::PATH, $name, $format),
                    "description" => $this->getDescription($class)
                ];
            }
            $this->OUT['apis'] = $classes;
        }
        return $this->OUT;
    }

    /**
     * @param \Zend_Reflection_Method $method
     * @return array
     */
    private function getParsingArgument($method) {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $data = $_GET;
                break;
            case 'POST':
                $data = json_decode(file_get_contents('php://input'), true);
                break;
            case 'PUT':
                $data = json_decode(file_get_contents('php://input'), true);
                break;
            case 'DELETE':
                $data = json_decode(file_get_contents('php://input'), true);
                break;
            default:
                $data = $_GET;
                break;
        }
        $paramsSorted = [];
        $params = $method->getParameters();
        /** @var \Zend_Reflection_Parameter $param */
        foreach ($params as $param)
            $paramsSorted[] = $data[$param->getName()];
        return $paramsSorted;
    }

    public function handle($request) {
        if (!preg_match(self::API_PATH, $request, $api))
            throw new ExceptionExt("api.request_parsing");

        $format = isset($api[self::API_PATH_FORMAT]) && $api[self::API_PATH_FORMAT] ? $api[self::API_PATH_FORMAT] : 'json';
        if ($api[self::API_PATH_RESOURCES])
            $out = $this->getResources($api[self::API_PATH_CLASS], $api[self::API_PATH_FUNCTION], $format);
        else {
            /** @var \Zend_Reflection_Class $class */
            $class = $this->apis[$api[self::API_PATH_CLASS]][0];
            $classObject = $class->newInstance();
            /** @var \Zend_Reflection_Method $methodObject */
            $methodObject = $this->apis[$api[self::API_PATH_CLASS]][$api[self::API_PATH_FUNCTION]];
            $out = $methodObject->invokeArgs($classObject, $this->getParsingArgument($methodObject));
        }

        switch ($format) {
            case "json":
                $out = json_encode($out, Option::isDev() ? JSON_PRETTY_PRINT : 0);
                break;
            default:
                $out = json_encode($out, JSON_PRETTY_PRINT);
        }
        return $out;
    }

    /**
     * @param $class \Zend_Reflection_Class | \Zend_Reflection_Method
     * @return string
     */
    private function getDescription($class) {
        if (strlen($class->getDocComment()) > 0 && $description = $class->getDocblock()->getShortDescription())
            return $description;
        return "";
    }

    /**
     * @param $reflection \Zend_Reflection_Class | \Zend_Reflection_Method
     * @return string
     */
    private function getTagToArray($reflection) {
        if (strlen($reflection->getDocComment()) <= 0
            || !($doc = $reflection->getDocblock()))
            return [];
        $tags = [];
        /** @var \Zend_Reflection_Docblock_Tag $content */
        foreach ($doc->getTags() as $content) {
            $tags[$content->getName()] = explode(' ', $content->getDescription());
        }
        return $tags;
    }
}