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
 * 有隶属关系的上下级读取
 * 1. 后台菜单管理
 * 2. 组织架构
 * @package Pails\Interfaces
 */
interface RelateChildInterface
{
    /**
     * 读取子列表中的第1条
     * <code>
     * $relateName = 'userId';
     * $relateValue = 1;
     * $parentName = 'parentId';
     * $parentValue = 0;
     * $children = $this->fetchChild($relateName, $relateValue, $parentName, $parentValue);
     * </code>
     *
     * @param string $relateName 关系字段名称
     * @param int    $relateValue 关系字段值
     * @param string $parentName 上级字段名称
     * @param int    $parentValue 上级字段值
     *
     * @return ModelInterface
     */
    public function fetchChild($relateName, $relateValue, $parentName, $parentValue);

    /**
     * 读取子列表
     * <code>
     * $relateName = 'userId';
     * $relateValue = 1;
     * $parentName = 'parentId';
     * $parentValue = 0;
     * $children = $this->fetchChildren($relateName, $relateValue, $parentName, $parentValue);
     * </code>
     *
     * @param string $relateName 关系字段名称
     * @param int    $relateValue 关系字段值
     * @param string $parentName 上级字段名称
     * @param int    $parentValue 上级字段值
     * @param string $columns 获取需要的字段 默认为*
     *
     * @return ResultsetInterface
     */
    public function fetchChildren($relateName, $relateValue, $parentName, $parentValue, $columns = "*");

    /**
     * 读取结构树
     * <code>
     * $relateName = 'userId';
     * $relateValue = 1;
     * $parentName = 'parentId';
     * $parentValue = 0;
     * $children = $this->fetchTree($relateName, $relateValue, $parentName, $parentValue);
     * </code>
     *
     * @param string $relateName 关系字段名称
     * @param int    $relateValue 关系字段值
     * @param string $parentName 上级字段名称
     * @param int    $parentValue 上级字段值
     * @param string $primaryColumn 读取树结构时依赖的主键键名
     *
     * @return array
     */
    public function fetchTree($relateName, $relateValue, $parentName, $parentValue, $primaryColumn = 'id');

    /**
     * 是否有子记录
     * <code>
     * $relateName = 'userId';
     * $relateValue = 1;
     * $parentName = 'parentId';
     * $parentValue = 0;
     * $has = $this->hasChild($relateName, $relateValue, $parentName, $parentValue);
     * </code>
     *
     * @param string $relateName 关系字段名称
     * @param int    $relateValue 关系字段值
     * @param string $parentName 上级字段名称
     * @param int    $parentValue 上级字段值
     *
     * @return bool
     */
    public function hasChild($relateName, $relateValue, $parentName, $parentValue);
}