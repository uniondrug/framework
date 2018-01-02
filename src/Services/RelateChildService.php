<?php
/**
 * 框架级通用服务
 * @author wsfuyibing <websearch@163.com>
 * @date 2017-11-16
 */
namespace Pails\Services;

use Pails\Interfaces\RelateChildInterface;

/**
 * 有隶属关系和上下级的通用Service
 * @package Pails\Services
 */
abstract class RelateChildService extends RelateService implements RelateChildInterface
{
    /**
     * @inheritdoc
     */
    public function fetchChild($relateName, $relateValue, $parentName, $parentValue)
    {
        return $this->fetchOneByColumn($relateName, $relateValue, $parentName, $parentValue);
    }

    /**
     * @inheritdoc
     */
    public function fetchChildren($relateName, $relateValue, $parentName, $parentValue, $columns = "*")
    {
        return $this->fetchAllByColumn($relateName, $relateValue, $parentName, $parentValue, $columns);
    }

    /**
     * @inheritdoc
     */
    public function fetchTree($relateName, $relateValue, $parentName, $parentValue, $primaryColumn = 'id', $columns = "*")
    {
        $tree = [];
        $children = $this->fetchChildren($relateName, $relateValue, $parentName, $parentValue, $columns);
        foreach ($children as $child) {
            $temp = $child->toArray();
            $temp['children'] = $this->fetchTree($relateName, $relateValue, $parentName, $temp[$primaryColumn], $primaryColumn, $columns);
            $tree[] = $temp;
        }
        unset($children);
        return $tree;
    }

    /**
     * @inheritdoc
     */
    public function hasChild($relateName, $relateValue, $parentName, $parentValue)
    {
        $parameters = [];
        if (is_array($parentValue)) {
            $parameters['conditions'] = "{$relateName} = '{$relateValue}' AND {$parentName} IN ('".implode("', '", $parentValue)."')";
        } else {
            $parameters['conditions'] = $relateName.' = :'.$relateName.': AND '.$parentName.' = :'.$parentName.':';
            $parameters['bind'] = [
                $relateName => $relateValue,
                $parentName => $parentValue
            ];
        }
        return 0 < (int) $this->fetchCount($parameters);
    }
}