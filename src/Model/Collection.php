<?php

namespace Hos\Model;
/**
 * Created by PhpStorm.
 * User: mallanic
 * Date: 10/04/16
 * Time: 17:35
 */
class Collection
{
    /**
     * @var MySQL
     */
    protected $database = null;

    /**
     * @var \ReflectionClass
     */
    protected $reflectionEntity = null;

    /**
     * @var string
     */
    protected $tableName = null;


    function __construct() {
        $this->database = Instance::getDatabaseInstance();
        $reflection = new \ReflectionClass(get_class($this));
        $doc = Instance::getDocInstance()->getDocHeader($reflection);
        if (!isset($doc['entity']))
            throw new Error("no_entity_select");
        $this->reflectionEntity = new \ReflectionClass($doc['entity'][0]);
        $this->tableName = $this->reflectionEntity->getShortName();
        if (!$this->database->hasTable($this->tableName))
            $this->createTable($this->reflectionEntity->getName(),
                $this->tableName);
    }

    protected function createTable($entityName, $tableName) {
        $doc = Instance::getDocInstance();
        /*
        $entries = $doc->getEntriesFromEntity($entityName);
        $properties = [];
        foreach ($entries as $name=>$entry) {
            $properties[$name] = array(
                'sql' => $this->database->getMySQLTypeFromPHP($entry['var'])
            );
        }
        $this->database->createTable($tableName, $properties);*/
    }

    protected function getAll() {
        $entities = $this->database->select($this->tableName)
            ->getAll();
        return $this->arraySQLToEntity($entities);
    }

    protected function sqlToEntity($result) {
        return $this->reflectionEntity->newInstance($result);
    }

    protected function arraySQLToEntity($results) {
        $entities = [];
        foreach ($results as $result)
            $entities[] = $this->sqlToEntity($result);
        return $entities;
    }

    protected function create() {
        $keys = array_keys(get_object_vars($this->reflectionEntity->getName()));
        $values = func_get_args()[0];
        $properties = array_combine($keys, $values);
        $entity = $this->reflectionEntity->newInstance();
        foreach ($properties as $key=>$value) {
            $function = "set".ucfirst($key);
            $entity->$function($value);
        }
        if (method_exists($entity, "onCreate"))
            $entity->onCreate();
        $properties = $entity->toSQL();
        $this->database->insert($this->tableName, $properties)
            ->execute();
    }

    protected function update() {
        $keys = [];
        foreach ($this->reflectionEntity->getProperties() as $property)
            $keys[] = $property->getName();
        $values = func_get_args()[0];
        $properties = array_combine($keys, $values);
        $entity = $this->findOneById($properties['id']);
        foreach ($properties as $key=>$value) {
            $function = "set".ucfirst($key);
            $entity->$function($value);
        }
        if (method_exists($entity, "onUpdate"))
            $entity->onUpdate();
        $properties = $entity->toSQL();
        $this->database->update($this->tableName, $properties)
            ->execute();

    }

    protected function delete($id) {
        $entity = $this->findOneById($id);
        if (method_exists($entity, "onDelete"))
            $entity->onDelete();
        return $this->database->delete($this->tableName)
            ->where("`id` = $id")
            ->execute();
    }

    protected function find($search) {
        $columns = $this->database->getColumns($this->tableName);
        array_walk($columns, function (&$column, $key, $search) {
            $column = "`$column` LIKE '%$search%'";
        }, $search);
        $columns = implode(' OR ', $columns);
        $results = $this->database->select($this->tableName)
            ->where("$columns")
            ->getAll();
        if (!$results)
            throw new Error("not_found");
        return $this->arraySQLToEntity($results);
    }

    protected function findOne($search) {
        $columns = $this->database->getColumns($this->tableName);
        array_walk($columns, function (&$column, $key, $search) {
            $column = "`$column` LIKE '%$search%'";
        }, $search);
        $columns = implode(' OR ', $columns);
        $result = $this->database->select($this->tableName)
            ->where("$columns")
            ->getOne();
        if (!$result)
            throw new Error("not_found");
        return $this->sqlToEntity($result);
    }

    protected function findOneById($id) {
        $result = $this->database->select($this->tableName)
            ->where("`id` = '$id'")
            ->getOne();
        if (!$result)
            throw new Error("not_found", array('id' => $id));
        return $this->sqlToEntity($result);
    }
}