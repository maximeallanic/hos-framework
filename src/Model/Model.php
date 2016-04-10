<?php

namespace Hos\Model;
/**
 * Created by PhpStorm.
 * User: mallanic
 * Date: 10/04/16
 * Time: 17:36
 */
class Model
{
    CONST SET_REGEX = "/^(?:set|add)([A-Za-z])+/";
    CONST GET_REGEX = "/^get([A-Za-z])+/";
    private $toModify = [];
    private $isNew = true;
    private $tableName = null;
    private $translator = null;
    private $doc = null;

    /**
     * @var \ReflectionClass
     */
    private $reflection = null;
    private $disableDatabase = false;

    function __construct($databaseInquire = null) {
        $this->doc = Instance::getDocInstance();
        $this->translator = Instance::getTranslatorInstance();
        $this->reflection = new \ReflectionClass(get_class($this));
        foreach ($this->reflection->getMethods() as $method)
            if (preg_match(self::GET_REGEX, $method->getName()) ||
                preg_match(self::SET_REGEX, $method->getName()))
                $method->setAccessible(false);
        $properties = $this->reflection->getProperties();
        foreach ($properties as $property) {
            $key = $property->getName();
            $this->$key = $this->initializeValue($key,
                $databaseInquire == null ? null : $databaseInquire[$key]);
        }
        if ($databaseInquire)
            $this->isNew = false;
        $this->toModify = [];
    }

    private function disableDatabase() {
        $this->disableDatabase = true;
    }

    private function convertVariable($value, $type) {

        if (strpos($type, '[]')) {
            if (!$value)
                return array();
            if (gettype($value) != "array")
                $value = unserialize($value);
            $type = str_replace('[]', '', $type);
            for ($i = 0; $i < count($value); $i++)
                $value[$i] = $this->convertVariable($value[$i], $type);
            return $value;
        }
        if (!$value)
            return null;
        switch ($type) {
            case "string":
                return strval($value);
            case "integer":
                return intval($value);
        }
        if ($d = $this->doc->getDatabaseFromEntity($type)) {
            $object = $d->newInstance();
            return $d->getMethod("findOneById")->invokeArgs($object, array($value));
        }
        return $value;
    }



    public function __call($name, $arguments) {
        if (!$method = $this->reflection->getMethod($name))
            throw new Error('function_not_exist');
        if (preg_match(self::SET_REGEX, $name) ||
            preg_match(self::GET_REGEX, $name, $function)) {
            /**$doc = $this->doc->getDocHeader($method);
            $arguments = array_combine($arguments, $method->getParameters());
            array_walk($arguments, function ($value, $key, $doc) {
            foreach ($doc['param'] as $param)
            if ($param[0] == $key->getName())
            return $this->convertVariable($key, $param[1][0]);
            }, $doc);*/
            $return = $method->invoke($this, $arguments);
            return $return;//$this->convertVariable($return, $doc['return'][0]);
        }
    }

    function initializeValue($key, $value) {
        $property = $this->reflection->getProperty($key);
        $doc = Instance::getDocInstance()->getDocHeader($property);
        $value = $this->convertVariable($value, $doc['protected'][0]);
        return $value;
    }

    function toSQL() {
        $properties = [];
        $rProperties = $this->reflection->getProperties();
        foreach ($rProperties as $rProperty) {
            $key = $rProperty->getName();
            $doc = Instance::getDocInstance()->getDocHeader($rProperty);
            $type = $this->getVarType($doc);
            if ($type['isArray'])
                $properties[$key] = $this->arrayToSQL($type, $this->$key);
            else
                $properties[$key] = $this->$key;
        }
        return $properties;
    }

    function arrayToSQL($type, $value) {
        if (!$value)
            return array();
        $array = unserialize($value);
        if (!class_exists($type['type'], true))
            return $array;
        for ($i = 0; $i < count($array); $i++)
            $array[$i] = $array[$i]->getId();
        return $array;
    }

    private function getVarType($doc) {
        /*if (isset($doc['protected']))
            $doc['protected'] = str_replace('\\', '\\\\', $doc['protected']);*/
        return array(
            'isArray' => isset($doc['protected'][0]) && strpos($doc['protected'][0], '[]') ? true : false,
            'type' => isset($doc['protected'][0]) ? str_replace('[]', '', $doc['protected'][0]) : null
        );
    }

    function hasAcces($key) {
        if (!$this->reflection->hasProperty($key))
            return false;
        $reflection = $this->reflection->getProperty($key);
        return Instance::getAuthInstance()->hasAccessAttribute($reflection);
    }

    public function jsonSerialize() {
        return $this->toArray();
    }


    function toArray() {
        $properties = [];
        foreach ($this->reflection->getMethods() as $method) {
            $methodName = $method->getName();
            if ($method->getDeclaringClass()->getName() == $this->reflection->getName()
                && preg_match("/^get([A-Za-z]+)/", $methodName, $matches))
                $properties[lcfirst($matches[1])] =
                    $this->__call($methodName, array());
        }
        return $properties;
    }
}