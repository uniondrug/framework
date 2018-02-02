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
     * 从$data数据源中提取数据, 并转为结构体默认选项
     * <code>
     * $defaults = [
     *     'drugId' => [
     *         'type' => 'int',
     *         'field' => 'id'
     *     ]
     * ];
     * </code>
     *
     * @param array     $defaults 默认项
     * @param \stdClass $data 数据源
     *
     * @return array
     */
    public static function initOptions(& $defaults, & $data)
    {
        $options = [];
        foreach ($defaults as $key => $conf) {
            // 1. 数组级配置
            $conf = is_array($conf) ? $conf : [];
            $type = isset($conf['type']) ? strtolower($conf['type']) : null;
            $field = isset($conf['field']) ? $conf['field'] : $key;
            // 2. 已传参
            if (isset($data->$field)) {
                // 3. 类型与值
                $value = (string) $data->$field;
                // 4. 类型转换
                if ($type !== null) {
                    switch ($type) {
                        // int
                        case 'int' :
                        case 'integer' :
                            $value = preg_match("/^[\-]?[0-9]+$/", $value) > 0 ? (int) $value : 0;
                            break;
                        // double
                        case 'float' :
                        case 'double' :
                            $value = preg_match("/^[\-]?[0-9]*[\.]?[0-9]+$/", $value) > 0 ? (double) $value : 0.0;
                            break;
                        // string
                        case 'string' :
                            break;
                        // null
                        default :
                            $value = null;
                            break;
                    }
                }
                $options[$field] = $value;
                continue;
            }
            // 2. 未传参
            $value = isset($conf['default']) ? $conf['default'] : null;
            if ($type !== null) {
                switch ($type) {
                    // int
                    case 'int' :
                    case 'integer' :
                        $value = 0;
                        break;
                    // double
                    case 'float' :
                    case 'double' :
                        $value = 0.0;
                        break;
                    // string
                    case 'string' :
                        $value = '';
                        break;
                }
            }
            $options[$field] = $value;
        }
        return $options;
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
        return $this->toArrayParser($this->_properties);
    }

    /**
     * 转为JSON字符串
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->toArray(), true);
    }

    /**
     * 解析std/structs为数组
     * @param array $data
     *
     * @return array
     */
    private function toArrayParser($data)
    {
        foreach ($data as $key => & $value) {
            if (is_array($value)) {
                $value = $this->toArrayParser($value);
            } else if ($value instanceof Structs) {
                $value = $value->toArray();
            }
        }
        return $data;
    }
}
