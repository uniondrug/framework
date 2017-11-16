<?php
/**
 * 框架级读接口
 * @author wsfuyibing <websearch@163.com>
 * @date 2017-11-16
 */
namespace Pails\Interfaces;

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset;

/**
 * 有隶属关系的读取
 * @package Pails\Interfaces
 */
interface RelateFetchInterface
{
    /**
     * 按字段名称与值读取列表
     * <code>
     * $relateName = 'userId';
     * $relateValue = 1;
     * $columnName = 'userId';
     * $columnValue = 1;
     * return $this->fetchAllByColumn($relateName, $relateValue, $columnName, $columnValue);
     * </code>
     *
     * @param string $relateName 关系字段名称
     * @param int    $relateValue 关系字段值
     * @param string $columnName 字段名称
     * @param mixed  $columnValue 字段值
     *
     * @return Resultset
     */
    public function fetchAllByColumn($relateName, $relateValue, $columnName, $columnValue);

    /**
     * 按字段名称与值读取一条记录
     * <code>
     * $relateName = 'userId';
     * $relateValue = 1;
     * $columnName = 'id';
     * $columnValue = 1;
     * return $this->fetchOneByColumn($relateName, $relateValue, $columnName, $columnValue);
     * </code>
     *
     * @param string $relateName 关系字段名称
     * @param int    $relateValue 关系字段值
     * @param string $columnName 字段名称
     * @param mixed  $columnValue 字段值
     *
     * @return Model
     */
    public function fetchOneByColumn($relateName, $relateValue, $columnName, $columnValue);

    /**
     * 按记录ID读取1条数据
     * <code>
     * $relateName = 'userId';
     * $relateValue = 1;
     * $id = 1;
     * return $this->fetchOneById($relateName, $relateValue, $id);
     * </code>
     *
     * @param string $relateName 关系字段名称
     * @param int    $relateValue 关系字段值
     * @param int    $id 主键id的值
     *
     * @return Model
     */
    public function fetchOneById($relateName, $relateValue, $id);
}