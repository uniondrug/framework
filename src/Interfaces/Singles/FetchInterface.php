<?php
/**
 * 行记录管理接口
 * 1. 添加
 * 2. 修改
 * 3. 删除
 * @author wsfuyibing <websearch@163.com>
 * @date 2017-11-15
 */
namespace Pails\Interfaces\Singles;

use Phalcon\Exception;

/**
 * 无关系(或上、下级)型的行管理接口
 * 如: 平台注册(运营平台、开放平台、保司平台等)
 * @package Pails\Interfaces\Singles
 */
interface RowInterface
{
    /**
     * 删除记录
     * 1. Restful以DELETE方式提交
     * 2. SQL 对应于 DELETE FROM table WHERE id = $id
     *
     * @param int $id 记录主键ID号
     *
     * @return bool
     * @throws Exception
     */
    public function deleteRow($id);

    /**
     * 修改记录
     * 1. Restful以POST方式提交
     * 2. SQL 对应于 UPDATE table SET `column` = :column WHERE id = $id
     * <code>
     * $id = 1;
     * $columns = [
     *     "key" => "value"
     * ];
     * $obj->postRow($id, $columns);
     * </code>
     *
     * @param int   $id 记录主键ID号
     * @param array $columns 列结构
     *
     * @return bool
     * @throws Exception
     */
    public function postRow($id, $columns);

    /**
     * 添加记录
     * 1. Restful以PUT方式提交
     * 2. SQL 对应于 INSERT INTO table (`column`) VALUES (:column)
     * <code>
     * $columns = [
     *     "key" => "value"
     * ];
     * $obj->putRow($columns);
     * </code>
     *
     * @param array $columns 列结构
     *
     * @return bool
     * @throws Exception
     */
    public function putRow($columns);
}