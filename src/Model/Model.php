<?php

namespace Hos\Model;
use Cake\ORM\TableRegistry;
use Hos\ExceptionExt;

/**
 * Created by PhpStorm.
 * User: mallanic
 * Date: 10/04/16
 * Time: 17:36
 */
class Model
{
    CONST SET_REGEX = "/^(?:set|add)(?<name>[A-Za-z])+/";
    CONST GET_REGEX = "/^get(?<name>[A-Za-z])+/";
    CONST PROPERTY_REGEX = "/(?<type>[A-Za-z]+)(?:(?:\s+default=(?<default>(?:(?!\s).)+)))?/";

    /** @var null|\Zend_Reflection_Class */
    private $class = null;

    private $disableDatabase = false;
    private $properties = [];
    private $isCreated = false;

    /**
     * @var int
     */
    public $id;

    function __construct() {
        $this->class = new \Zend_Reflection_Class(get_class($this));
        /** @var \Zend_Reflection_Property $property */
        foreach ($this->class->getProperties() as $property) {
            if (!$property->isPrivate()
                && $doc = $property->getDocComment())
                $this->properties[$property->getName()] = $this->parseProperty($doc);
            $property->setAccessible(false);
        }
    }

    /**
     * @param \Zend_Reflection_Docblock $doc
     * @return array
     */
    private function parseProperty($doc) {
        $var = $doc->getTag('var');
        if (!$var)
            return false;
        preg_match(self::PROPERTY_REGEX, $var->getDescription(), $matches);
        return array_filter($matches, function ($key) {
            return !is_numeric($key);
        }, ARRAY_FILTER_USE_KEY);
    }

    public function disableDatabase() {
        $this->disableDatabase = true;
    }

    private function update() {
        $this->newEvent("preCreate");
        $collections = TableRegistry::get($this->class->getShortName());
        $model = $collections->get($this->getPrimaryKey());
        $this->newEvent("postCreate");
        if ($collections->save($model)) {
            $this->isCreated = true;
            return true;
        }
        return false;
    }

    private function create() {
        if (!$this->disableDatabase)
            return false;
        $this->newEvent("preCreate");
        $collections = TableRegistry::get($this->class->getName());
        $model = $collections->newEntity($this->toArray());
        $this->newEvent("postCreate");
        if ($collections->save($model)) {
            $this->isCreated = true;
            return true;
        }
        return false;
    }

    public function delete() {
        if (!$this->disableDatabase)
            return false;
        $this->newEvent("preDelete");
        $collections = TableRegistry::get($this->class->getName());
        $model = $collections->get($this->getPrimaryKey());
        $this->newEvent("postDelete");
        if ($collections->delete($model)) {
            $this->isCreated = true;
            return true;
        }
        return false;
    }

    public function save()
    {
        if (!$this->disableDatabase)
            return false;
        return $this->isCreated ? $this->update() : $this->create();
    }

    public function __call($name, $arguments) {
        if (!$method = $this->class->getMethod($name))
            return false;
        if (preg_match(self::SET_REGEX, $name, $matches) ||
            preg_match(self::GET_REGEX, $name, $matches)) {
            $return = $method->invoke($this, $arguments);
            return $return;
        }
        return $method->invoke($this, $arguments);
    }

    public function jsonSerialize() {
        return $this->toArray();
    }

    function toArray() {
        $properties = [];
        /** @var \Zend_Reflection_Method $method */
        foreach ($this->properties as $name => $property) {
            $methodName = sprintf("get%s", ucfirst($name));
            if (!$this->class->hasMethod($methodName))
                $properties[$name] = $this->class->getProperty($name)->getValue($this);
            else
                $properties[$name] = $this->class->getMethod($methodName)->invoke($this);
        }
        return $properties;
    }

    public function fromArray($properties) {
        foreach ($properties as $name => $value) {
            $methodName = sprintf("set%s", ucfirst($name));
            if (!$this->class->hasMethod($methodName))
                $this->class->getProperty($name)->setValue($this, $value);
            else
                $this->class->getMethod($methodName)->invokeArgs($this, [$value]);
        }
    }


    public function from($properties) {
        foreach ($properties as $name => $value)
            $this->class->getProperty($name)->setValue($this, $value);
        $this->isCreated = true;
    }

    public function getPrimaryKey() {
        return $this->id;
    }

    /** Event */
    private function newEvent($name) {

    }
}