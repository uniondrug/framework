<?php
/**
 * 框架级通用服务
 * @author wsfuyibing <websearch@163.com>
 * @date 2017-11-16
 */
namespace Pails\Services;

use Pails\Interfaces\RelateFetchInterface;
use Pails\Interfaces\RelateWriteInterface;
use Phalcon\Mvc\Model;
use Phalcon\Exception;

/**
 * 有隶属关系的通用Service
 * @package Pails\Services
 */
abstract class RelateService extends FrameworkService implements RelateFetchInterface, RelateWriteInterface
{
    /**
     * @inheritdoc
     */
    public function fetchAllByColumn($relateName, $relateValue, $columnName, $columnValue, $columns = "*")
    {
        $parameters['columns'] = $columns;
        if (is_array($columnValue)) {
            $parameters['conditions'] = "{$relateName} = '{$relateValue}' AND {$columnName} IN ('".implode("', '", $columnValue)."')";
        } else {
            $parameters['conditions'] = $relateName.' = :'.$relateName.': AND '.$columnName.' = :'.$columnName.':';
            $parameters['bind'] = [
                $relateName => $relateValue,
                $columnName => $columnValue
            ];
        }
        return $this->fetchAll($parameters);
    }

    /**
     * @inheritdoc
     */
    public function fetchOneByColumn($relateName, $relateValue, $columnName, $columnValue)
    {
        $parameters = [];
        $parameters['conditions'] = $relateName.' = :'.$relateName.': AND '.$columnName.' = :'.$columnName.':';
        $parameters['bind'] = [
            $relateName => $relateValue,
            $columnName => $columnValue
        ];
        return $this->fetchOne($parameters);
    }

    /**
     * @inheritdoc
     */
    public function fetchOneById($relateName, $relateValue, $id)
    {
        return $this->fetchOneByColumn($relateName, $relateValue, 'id', (int) $id);
    }

    /**
     * @inheritdoc
     */
    public function delete($relateName, $relateValue, $columnName, $columnValue)
    {
        $count = 0;
        $fetch = $this->fetchAllByColumn($relateName, $relateValue, $columnName, $columnValue);
        foreach ($fetch as $model) {
            $count += $model->delete() ? 1 : true;
        }
        return $count;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($relateName, $relateValue, $id)
    {
        return $this->delete($relateName, $relateValue, 'id', (int) $id);
    }

    /**
     * @inheritdoc
     */
    public function insert($relateName, $relateValue, $columns)
    {
        $columns[$relateName] = $relateValue;
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
    public function update($relateName, $relateValue, $columnName, $columnValue, $columns = [])
    {
        if (count($columns) === 0) {
            $message = "未指定要修改的字段";
            $this->setError($message);
            throw new Exception($message);
        }
        $fetch = $this->fetchOneByColumn($relateName, $relateValue, $columnName, $columnValue);
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
    public function updateById($relateName, $relateValue, $id, $columns = [])
    {
        return $this->update($relateName, $relateValue, 'id', (int) $id, $columns);
    }
}