<?php
/**
 * 框架级Child接口
 * @author wsfuyibing <websearch@163.com>
 * @date 2017-11-16
 */
namespace Pails\Interfaces;

use Phalcon\Mvc\ModelInterface;
use Phalcon\Mvc\Model\ResultsetInterface;

/**
 * 无隶属关系的上下级读取
 * @package Pails\Interfaces
 */
interface SingleChildInterface
{
    /**
     * 读取子列表中的第1条
     * <code>
     * $parentName = 'parentId';
     * $parentValue = 0;
     * $children = $this->fetchChild($parentName, $parentValue);
     * </code>
     *
     * @param string $parentName 上级字段名称
     * @param int    $parentValue 上级字段值
     *
     * @return ModelInterface
     */
    public function fetchChild($parentName, $parentValue);

    /**
     * 读取子列表
     * <code>
     * $parentName = 'parentId';
     * $parentValue = 0;
     * $children = $this->fetchChildren($parentName, $parentValue);
     * </code>
     *
     * @param string $parentName 上级字段名称
     * @param int    $parentValue 上级字段值
     * @param string $columns 获取需要的字段 默认为*
     *
     * @return ResultsetInterface
     */
    public function fetchChildren($parentName, $parentValue, $columns = "*");

    /**
     * 读取结构树
     * <code>
     * $parentName = 'parentId';
     * $parentValue = 0;
     * $children = $this->fetchTree($parentName, $parentValue);
     * </code>
     *
     * @param string $parentName 上级字段名称
     * @param int    $parentValue 上级字段值
     * @param string $primaryColumn 读取树结构时依赖的主键键名
     *
     * @return array
     */
    public function fetchTree($parentName, $parentValue, $primaryColumn = 'id');

    /**
     * 是否有子记录
     * <code>
     * $parentName = 'parentId';
     * $parentValue = 0;
     * $has = $this->hasChild($parentName, $parentValue);
     * </code>
     *
     * @param string $parentName 上级字段名称
     * @param int    $parentValue 上级字段值
     *
     * @return bool
     */
    public function hasChild($parentName, $parentValue);
}