<?php
/**
 * 框架级通用服务
 * @author wsfuyibing <websearch@163.com>
 * @date 2017-11-16
 */
namespace Pails\Services;

use Pails\Interfaces\SingleFetchInterface;
use Pails\Interfaces\SingleWriteInterface;
use Phalcon\Exception;
use Phalcon\Mvc\Model;

/**
 * 无隶属关系的通用Service
 * @package Pails\Services
 */
abstract class SingleService extends FrameworkService implements SingleFetchInterface, SingleWriteInterface
{
    /**
     * @inheritdoc
     */
    public function fetchAllByColumn($columnName, $columnValue, $columns = "*")
    {
        $parameters['columns'] = $columns;
        if (is_array($columnValue)) {
            $parameters['conditions'] = "{$columnName} IN ('".implode("', '", $columnValue)."')";
        } else {
            $parameters['conditions'] = "{$columnName} = :{$columnName}:";
            $parameters['bind'] = [$columnName => $columnValue];
        }
        return $this->fetchAll($parameters);
    }

    /**
     * @inheritdoc
     */
    public function fetchOneByColumn($columnName, $columnValue)
    {
        $parameters = [];
        $parameters['conditions'] = "{$columnName} = :{$columnName}:";
        $parameters['bind'] = [$columnName => $columnValue];
        return $this->fetchOne($parameters);
    }

    /**
     * @inheritdoc
     */
    public function fetchOneById($id)
    {
        return $this->fetchOneByColumn('id', (int) $id);
    }

    /**
     * @inheritdoc
     */
    public function delete($columnName, $columnValue)
    {
        $count = 0;
        $fetch = $this->fetchAllByColumn($columnName, $columnValue);
        foreach ($fetch as $model) {
            $count += $model->delete() ? 1 : true;
        }
        return $count;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($id)
    {
        return $this->delete('id', (int) $id);
    }

    /**
     * @inheritdoc
     */
    public function insert($columns = [])
    {
        $model = $this->getModel();
        $done = $model->create($columns);
        if ($done) {
            $key = $this->getAutoIncrementColumn($model);
            if ($key) {
                return $this->$key;
            }
        }
        return $done;
    }

    /**
     * @inheritdoc
     */
    public function update($columnName, $columnValue, $columns = [])
    {
        if (count($columns) === 0) {
            $message = "未指定要修改的字段";
            $this->setError($message);
            throw new Exception($message);
        }
        $fetch = $this->fetchOneByColumn($columnName, $columnValue);
        if (!($fetch instanceof Model)) {
            $message = "待修改的记录不存在或已被删除";
            $this->setError($message);
            throw new Exception($message);
        }
        return $fetch->update($columns);
    }

    /**
     * @inheritdoc
     */
    public function updateById($id, $columns = [])
    {
        return $this->update('id', (int) $id, $columns);
    }
}