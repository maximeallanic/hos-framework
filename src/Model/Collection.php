<?php

namespace Hos\Model;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;
use Hos\ExceptionExt;
use Hos\Option;

/**
 * Created by PhpStorm.
 * User: mallanic
 * Date: 10/04/16
 * Time: 17:35
 */
class Collection
{
    CONST DATABASE_TYPE = [
        "mysql" => 'Cake\Database\Driver\Mysql',
        "pdo" => 'Cake\Database\Driver\PDODriverTrait',
        "pgsql" => 'Cake\Database\Driver\Postgres',
        "sqlite" => 'Cake\Database\Driver\Sqlite',
        "sql" => 'Cake\Database\Driver\Sqlserver'
    ];
    /**
     * @var \ReflectionClass
     */
    private $model = null;
    private $class = null;
    public $collections = null;
    static $connection = null;

    public function __construct()
    {

        $this->class = new \Zend_Reflection_Class(get_class($this));
        if (!$model = $this->class->getDocblock()->getTag('model'))
            throw new ExceptionExt("collection.require_model_doc");
        $this->model = new \Zend_Reflection_Class($model->getDescription());

        if (!self::$connection) {
            $params = Option::get()['database'];
            self::$connection = ConnectionManager::config('default', [
                'className' => 'Cake\Database\Connection',
                'driver' => self::DATABASE_TYPE[$params['type']],
                'persistent' => false,
                'host' => $params['host'],
                'username' => $params['user'],
                'password' => $params['password'],
                'database' => $params['db'],
                'encoding' => 'utf8',
                'timezone' => 'UTC',
                'cacheMetadata' => true,
            ]);
        }
        $this->collections = TableRegistry::get($this->model->getShortName());
    }

    private function dataToModel($data) {
        $model = $this->model->newInstance();
        $model->from($data);
        return $model;
    }

    private function datasToModel($datas) {
        foreach ($datas as &$data)
            $data = $this->dataToModel($data);
        return $datas;
    }


    public function findAll($orderBy = [], $filterBy = [], $limit = -1, $startAt = 0) {
        $query = $this->collections->find();
        if ($limit >= 0)
            $query->limit($limit);
        if (count($orderBy) > 0)
            $query->order($orderBy);
        if (count($filterBy) > 0)
            $query->offset($startAt);
        return $this->datasToModel($query->getIterator());
    }

    public function find() {

    }
}