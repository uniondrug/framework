<?php
/**
 * 框架级写接口
 * @author wsfuyibing <websearch@163.com>
 * @date 2017-11-16
 */
namespace Pails\Interfaces;

/**
 * 无隶属关系的写入
 * @package Pails\Interfaces
 */
interface SingleWriteInterface
{
    /**
     * 删除指定字段为某个值的全部记录
     * <code>
     * $columnName = 'id';
     * $columnValue = 1;
     * return $this->delete($columnName, $columnValue);
     * </code>
     *
     * @param string $columnName 字段名称
     * @param string $columnValue 字段值
     *
     * @return int 删除数量
     */
    public function delete($columnName, $columnValue);

    /**
     * 按主键ID删除记录
     * <code
     * $id = 1;
     * return $this->deleteById($id);
     * </code>
     *
     * @param int $id 主键ID记录
     *
     * @return int 删除数量
     */
    public function deleteById($id);

    /**
     * 添加记录
     * <code>
     * $columns = ["key" => "value"];
     * return $this->insert($columns);
     * </code>
     *
     * @param array $columns 待修改的键值对
     *
     * @return int|true 当指定表有主键时返回主键的流水号返之返回true
     */
    public function insert($columns);

    /**
     * 修改记录
     * <code>
     * $columnName = 'id';
     * $columnValue = 1;
     * $columns = ["key" => "value"];
     * return $this->update($columnName, $columnValue, $columns);
     * </code>
     *
     * @param string $columnName 字段名称
     * @param string $columnValue 字段值
     * @param array  $columns 待修改的键值对
     *
     * @return bool
     */
    public function update($columnName, $columnValue, $columns = []);

    /**
     * 按主键ID修改记录
     * <code>
     * $id = 1;
     * $columns = ["key" => "value"];
     * return $this->updateById($id, $columns);
     * </code>
     *
     * @param int   $id
     * @param array $columns
     *
     * @return bool
     */
    public function updateById($id, $columns = []);
}