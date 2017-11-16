<?php
/**
 * 行记录读取接口
 * 1. 一条
 * 2. 全部
 * @author wsfuyibing <websearch@163.com>
 * @date 2017-11-15
 */
namespace Pails\Interfaces\Singles;

use Phalcon\Mvc\Model\ResultsetInterface;
use Phalcon\Mvc\ModelInterface;
use Phalcon\Exception;

/**
 * 无关系(或上、下级)型的行读取接口
 * 如: 平台注册(运营平台、开放平台、保司平台等)
 * @package Pails\Interfaces\Singles
 */
interface FetchInterface
{
    /**
     * 不限条件取全部
     * 1. Restful以GET方式提交
     * 2. SQL 对应于 SELECT * FROM table
     * <code>
     * $id = 1;
     * $model = $obj->fetchAll($id);
     * </code>
     * @return ResultsetInterface
     * @throws Exception
     */
    public function fetchAll();

    /**
     * 按指定ID读取一条
     * 1. Restful以GET方式提交
     * 2. SQL 对应于 SELECT * FROM table WHERE id = $id
     * <code>
     * $id = 1;
     * $model = $obj->fetchOne($id);
     * </code>
     *
     * @param int $id 记录主键ID号
     *
     * @return null|ModelInterface
     * @throws Exception
     */
    public function fetchOne($id);
}