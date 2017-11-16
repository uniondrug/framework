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
 * 无隶属关系的读取
 * @package Pails\Interfaces
 */
interface SingleFetchInterface
{
    /**
     * 按字段名称与值读取列表
     * <code>
     * $columnName = 'userId';
     * $columnValue = 1;
     * return $this->fetchAllByColumn($columnName, $columnValue);
     * </code>
     *
     * @param string $columnName 字段名称
     * @param mixed  $columnValue 字段值
     *
     * @return Resultset
     */
    public function fetchAllByColumn($columnName, $columnValue);

    /**
     * 按字段名称与值读取一条记录
     * <code>
     * $columnName = 'id';
     * $columnValue = 1;
     * return $this->fetchOneByColumn($columnName, $columnValue);
     * </code>
     *
     * @param string $columnName 字段名称
     * @param mixed  $columnValue 字段值
     *
     * @return Model
     */
    public function fetchOneByColumn($columnName, $columnValue);

    /**
     * 按记录ID读取1条数据
     * <code>
     * $id = 1;
     * return $this->fetchOneById($id);
     * </code>
     *
     * @param int $id 主键id的值
     *
     * @return Model
     */
    public function fetchOneById($id);
}