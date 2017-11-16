<?php
/**
 * 框架级写接口
 * @author wsfuyibing <websearch@163.com>
 * @date 2017-11-16
 */
namespace Pails\Interfaces;

/**
 * 有隶属关系的写入
 * @package Pails\Interfaces
 */
interface RelateWriteInterface
{
    /**
     * 删除指定字段为某个值的全部记录
     * <code>
     * $relateName = 'userId';
     * $relateValue = 1;
     * $columnName = 'id';
     * $columnValue = 1;
     * return $this->delete($relateName, $relateValue, $columnName, $columnValue);
     * </code>
     *
     * @param string $relateName 关系字段名称
     * @param int    $relateValue 关系字段值
     * @param string $columnName 字段名称
     * @param string $columnValue 字段值
     *
     * @return int 删除数量
     */
    public function delete($relateName, $relateValue, $columnName, $columnValue);

    /**
     * 按主键ID删除记录
     * <code
     * $relateName = 'userId';
     * $relateValue = 1;
     * $id = 1;
     * return $this->deleteById($relateName, $relateValue, $id);
     * </code>
     *
     * @param string $relateName 关系字段名称
     * @param int    $relateValue 关系字段值
     * @param int    $id 主键ID记录
     *
     * @return int 删除数量
     */
    public function deleteById($relateName, $relateValue, $id);

    /**
     * 添加记录
     * <code>
     * $relateName = 'userId';
     * $relateValue = 1;
     * $columns = ["key" => "value"];
     * return $this->insert($relateName, $relateValue, $columns);
     * </code>
     *
     * @param string $relateName 关系字段名称
     * @param int    $relateValue 关系字段值
     * @param array  $columns 待修改的键值对
     *
     * @return int|true 当指定表有主键时返回主键的流水号返之返回true
     */
    public function insert($relateName, $relateValue, $columns);

    /**
     * 修改记录
     * <code>
     * $relateName = 'userId';
     * $relateValue = 1;
     * $columnName = 'id';
     * $columnValue = 1;
     * $columns = ["key" => "value"];
     * return $this->update($relateName, $relateValue, $columnName, $columnValue, $columns);
     * </code>
     *
     * @param string $relateName 关系字段名称
     * @param int    $relateValue 关系字段值
     * @param string $columnName 字段名称
     * @param string $columnValue 字段值
     * @param array  $columns 待修改的键值对
     *
     * @return bool
     */
    public function update($relateName, $relateValue, $columnName, $columnValue, $columns = []);

    /**
     * 按主键ID修改记录
     * <code>
     * $relateName = 'userId';
     * $relateValue = 1;
     * $id = 1;
     * $columns = ["key" => "value"];
     * return $this->updateById($relateName, $relateValue, $id, $columns);
     * </code>
     *
     * @param string $relateName 关系字段名称
     * @param int    $relateValue 关系字段值
     * @param int    $id
     * @param array  $columns
     *
     * @return bool
     */
    public function updateById($relateName, $relateValue, $id, $columns = []);
}