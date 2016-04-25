<?php
/**
 * Created by PhpStorm.
 * User: mallanic
 * Date: 19/04/16
 * Time: 13:19
 */

namespace Hos\Swagger;


use Hos\Api;

class Schema
{

    CONST AUTH_TYPE = [
        'basic'
    ];

    private $OUT = [
        "apiVersion" => "1",
        "swaggerVersion" => "2.0",
        "basePath" => "http://localhost/api/",
        "authorizations" => [],
        "produces" => [
            "application/json"
        ],
        "consumes" => [
            "application/json"
        ],
        "apis" => [],
        "security" => [],
        "tags" => []
    ];

    private $apis = [];
    private $security = [];

    /** @param ApiClass|String $api */
    function addApi($api) {
        if (gettype($api) == 'string')
            $api = new ApiClass($api);
        if ($api->hasMethod('getType')) {
            $type = $api->getMethod('getType')->invoke(null);
            if (in_array($type, self::AUTH_TYPE))
                $this->security[$type] = $api;
        }
        $this->apis[$api->getApiName()] = $api;
    }

    public function setApiVersion($version) {
        $this->OUT['apiVersion'] = $version;
    }

    public function setBaseUrl($baseUrl) {
        $this->OUT['basePath'] = $baseUrl;
    }

    public function setSwaggerVersion($swaggerVersion) {
        $this->OUT['swaggerVersion'] = $swaggerVersion;
    }

    public function setProduces($produces) {
        $this->OUT['produces'] = $produces;
    }

    public function setConsumes($produces) {
        $this->OUT['produces'] = $produces;
    }

    /**
     * @param $className
     * @return ApiClass
     */
    public function getApiClass($className) {
        return $this->apis[$className];
    }

    /**
     * @param string $format
     * @param null $className
     * @param null $methodName
     * @return array
     */
    public function generate($format = 'json', $className = null, $methodName = null) {
        $out = array_merge($this->OUT);
        if ($className && !$methodName) {
            /** @var ApiClass $class */
            $class = $this->getApiClass($className);
            $out['tags'][] = [
                'name' => $class->getApiName(),
                'description' => $class->getDescription()
            ];
            foreach ($class->getApiMethods() as $route) {
                /** @var ApiMethod $method */
                foreach ($route as $method) {
                    $operations = $method->generateDoc();
                    $operations['tags'] = [$class->getApiName()];
                    $out['apis'][] = [
                        'path' => '/' . $class->getApiName() . $method->getApiUrl() . ".$format",
                        'operations' => [$operations]
                    ];
                }
            }
        }
        else {
            /** @var ApiClass $api */
            foreach ($this->apis as $api) {
                $out['apis'][] = [
                    'path' => '/resources/'.$api->getApiName().".$format",
                    'description' => $api->getDescription()
                ];
            }
        }
        /** @var ApiClass $security */
        foreach ($this->security as $security)
            $out['security'][$security->getApiName()] = $security->generateAuth();
        return $out;
    }
}