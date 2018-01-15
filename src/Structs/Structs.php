<?php
/**
 * 数据结构体
 * 用于对Model的补充
 * @author wsfuyibing <websearch@163.com>
 * @date 2018-01-15
 */
namespace Pails\Structs;

use Phalcon\Exception;

/**
 * 结构体基类
 * @package Pails\Structs
 */
abstract class Structs extends \stdClass
{
    private $_properties = [];

    /**
     * 预设属性值
     * <code>
     * $result = new ExampleStruct([
     *     'id' => 0, 'key' => 'value'
     * ]);
     * echo $result->id;  // 返回: 0
     * echo $result->key; // 返回: value
     * </code>
     *
     * @param array $options 默认项
     */
    public function __construct($defaults = [])
    {
        foreach ($defaults as $name => $value) {
            $this->__set($name, $value);
        }
    }

    /**
     * 通过Magic读取属性值
     *
     * @param string $name 属性名称
     *
     * @return mixed
     * @throws Exception
     * @example $obj->id;
     */
    final public function __get($name)
    {
        if (isset($this->_properties[$name])) {
            return $this->_properties[$name];
        }
        throw new Exception("属性'{$name}'未定义");
    }

    /**
     * 设置属性值
     *
     * @param string $name 属性名
     * @param mixed  $value 属性值
     *
     * @example $obj->id = 1;
     */
    final public function __set($name, $value)
    {
        $this->_properties[$name] = $value;
    }

    /**
     * 重置结果集
     */
    public function reset()
    {
        $this->_properties = [];
    }

    /**
     * 转为数组
     * @return array
     * @example $data = $obj->toArray()
     */
    public function toArray()
    {
        $tmp = $this->_properties;
        foreach ($tmp as & $value){
            if ($value instanceof Structs) {
                $value = $value->toArray();
            }
        }
        return $tmp;
    }

    /**
     * 转为JSON字符串
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->toArray(), true);
    }
}