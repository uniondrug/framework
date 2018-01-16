<?php
/**
 * 框架级Service基类
 * @author wsfuyibing <websearch@163.com>
 * @date 2017-11-15
 */
namespace Pails\Services;

use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Di\Injectable;
use Phalcon\Exception;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\ModelInterface;
use Phalcon\Paginator\Adapter\QueryBuilder;

/**
 * @property Mysql $db
 * @property Mysql $dbSlave
 * @package Pails\Services
 */
abstract class FrameworkService extends Injectable
{
    /**
     * @var Model Service对应的Model
     */
    private $model;
    /**
     * @var array 默认的错误结构
     */
    private $defaultError = [
        "errno" => 0,
        "error" => ""
    ];
    /**
     * @var array 最近的错误结构
     */
    private $lastError = null;

    /**
     * 读取指定模型的递增流水号字段名
     *
     * @param Model $model
     *
     * @return null|string
     */
    public function getAutoIncrementColumn(& $model)
    {
        $primaryKeys = $model->getModelsMetaData()->getPrimaryKeyAttributes($model);
        if (count($primaryKeys) > 0) {
            return (string) $primaryKeys[0];
        }
        return null;
    }

    /**
     * 读取最近的错误
     * @return array
     */
    public function getError()
    {
        if ($this->lastError !== null) {
            return $this->lastError;
        }
        return $this->defaultError;
    }

    /**
     * 读取最近的错误原因
     * @return string
     */
    public function getErrorMessage()
    {
        $lastError = $this->getError();
        return $lastError['error'];
    }

    /**
     * 读取模型对象
     * @return \Phalcon\Mvc\Model
     * @throws Exception
     */
    public function getModel()
    {
        if ($this->model === null) {
            if (0 === preg_match("/([_a-zA-Z0-9]+)Service$/", get_class($this), $m)) {
                throw new Exception("不合法的Service命名");
            }
            try {
                $class = "\\App\\Models\\{$m[1]}";
                $this->model = new $class();
            } catch(\Exception $e) {
                throw new Exception($e->getMessage());
            }
        }
        return $this->model;
    }

    /**
     * 是否有错误
     * @return bool
     */
    public function hasError()
    {
        $lastError = $this->getError();
        return $lastError['errno'] !== 0;
    }

    /**
     * 设置最近错误
     *
     * @param string $error 错误原因
     * @param int    $errno 错误编号
     *
     * @return $this
     */
    public function setError($error, $errno = 1)
    {
        $lastError = $this->defaultError;
        $lastError['errno'] = $errno ? $errno : 1;
        $lastError['error'] = $error;
        $this->lastError = $lastError;
        return $this;
    }

    /**
     * 设置Service模型
     *
     * @param ModelInterface $model
     *
     * @return $this
     * @throws Exception
     */
    public function setModel(& $model)
    {
        if (!($model instanceof Model)) {
            throw new Exception("无效的Phalon模型");
        }
        $this->model = $model;
        return $this;
    }

    /**
     * 读全部
     * <code>
     * return $this->fetchAll(['userId = 1'])
     * </code>
     *
     * @param array $parameters 参数同\Phalcon\Mvc\ModelInterface::find()方法
     *
     * @return Model\ResultsetInterface
     */
    public function fetchAll($parameters = [])
    {
        return call_user_func_array([
            $this->getModel(),
            'find'
        ], [$parameters]);
    }

    /**
     * 查询数量
     * <code>
     * return $this->fetchCount(['id = 1'])
     * </code>
     *
     * @param array $parameters 参数同\Phalcon\Mvc\ModelInterface::findFirst()方法
     *
     * @return int
     */
    public function fetchCount($parameters = [])
    {
        return call_user_func_array([
            $this->getModel(),
            'count'
        ], [$parameters]);
    }

    /**
     * 读1条
     * <code>
     * return $this->fetchOne(['id = 1'])
     * </code>
     *
     * @param array $parameters 参数同\Phalcon\Mvc\ModelInterface::findFirst()方法
     *
     * @return null|ModelInterface
     */
    public function fetchOne($parameters = [])
    {
        return call_user_func_array([
            $this->getModel(),
            'findFirst'
        ], [$parameters]);
    }

    /**
     * 分页读
     * <code>
     * return $this->fetchPaging([], 1, 10)
     * </code>
     *
     * @param array $parameters 参数同\Phalcon\Mvc\ModelInterface::find()方法
     * @param int   $page 页码
     * @param int   $limit 每页数量
     *
     * @return array $result 结果集
     *               array body 记录数数
     *               int total 总数
     *               int page 页码
     *               int limit 每页数量
     */
    public function fetchPaging($parameters = [], $page = 1, $limit = 10)
    {
        $builder = $this->modelsManager->createBuilder($parameters)->from(get_class($this->getModel()));
        $query = new QueryBuilder([
            "builder" => $builder,
            "limit" => $limit,
            "page" => $page
        ]);
        $paging = $query->getPaginate();
        return [
            "body" => $paging->items->toArray(),
            "total" => $paging->total_items,
            "page" => $paging->current,
            "limit" => $paging->limit
        ];
    }
}
