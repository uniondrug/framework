<?php
/**
 * 框架级Helper
 * @author wsfuyibing <websearch@163.com>
 * @date 2017-12-26
 */
namespace Pails\Helpers;

/**
 * 参数检查, 运行以下类型
 * <ul>
 * <li>date</li>
 * <li>datetime</li>
 * <li>double</li>
 * <li>email</li>
 * <li>integer</li>
 * <li>mobile</li>
 * <li>string</li>
 * <li>time</li>
 * </ul>
 * @package Pails\Helpers
 */
class Param extends \stdClass
{
    const CHARACTER_ENCODING = 'utf-8';
    const REGEXP_DATE = "/^(\d+)[^\d]+(\d+)[^\d]+(\d+)$/";
    const REGEXP_DATETIME = "/^(\d+)[^\d]+(\d+)[^\d]+(\d+)[^\d]+(\d+):(\d+):(\d+)$/";
    const REGEXP_DOUBLE = "/^[\+|\-]?[0-9]+\.?[0-9]*$/";
    const REGEXP_EMAIL = "/^[_a-z0-9][_a-z0-9\.\-]+[_a-z0-9]@[a-z0-9][a-z0-9\-\.]\.[a-z]{2,6}$/i";
    const REGEXP_INTEGER = "/^[\+|\-]?[0-9]+$/";
    const REGEXP_MOBILE = "/^1[3-9][\d]{9}$/";
    const REGEXP_TIME = "/^(\d+):(\d+)[:]?(\d*)$/";
    /**
     * 固定电话的规则
     * 1. 特殊1号: 95588、10000、10086
     * 2. 特殊2号: 4008365365
     * 3. 普通1号(号): 7777777、88888888
     * 4. 普通2号(区-号): 010-88888888、0553-2237136
     * 5. 普通3号(区-号-分): 025-88888888-3721
     * @var array
     */
    private static $telphoneRegexps = [
        "/^([1-9][0-9]{4})$/",
        "/^([48]00[0-9]{7})$/",
        "/^([2-9][0-9]{6,7})$/",
        "/^(0(10|2[0-9]|[3-9][0-9]{2}))[\-]+([2-9][0-9]{6,7})$/",
        "/^(0[0-9]{2,3})[\-]+([2-9][0-9]{6,7})[\-]+([0-9]+)$/"
    ];

    /**
     * 参数检查
     * <code>
     * // 以下示例应用于Controller
     * $json = $this->getJsonRawBody();
     * $rules = [
     *     'id' => [
     *         'type' => 'int',
     *         'required' => true,
     *         'min' => 1,
     *     ],
     *     'status' => [
     *         'type' => 'string',
     *         'required' => true,
     *         'options' => ['success', 'expired']
     *     ]
     * ];
     * Param::check($json, $rules);
     * </code>
     *
     * @param object $json JSON格式的RAW对象
     * @param array  $rules JSON
     *
     * @throws \Exception|ParamException
     */
    public static function check(& $json, & $rules)
    {
        // [1] $rules参数必须为数组格式
        if (!is_array($rules)) {
            throw new \Exception("规则'rules'参数配置必须为数组格式");
        }
        // [2] 遍历$rules规则
        foreach ($rules as $key => $rule) {
            // [2.0] 规则格式化
            //       1. 是否允许为空值
            //       2. 是否必须传递
            $rule['empty'] = isset($rule['empty']) && $rule['empty'] === true;
            $rule['required'] = isset($rule['required']) && $rule['required'] === true;
            // [2.1] 规则定义限数组
            if (!is_array($rule)) {
                throw new \Exception("字段'{$key}'的规则定义必须为数组格式");
            }
            // [2.2] 规则定义参数类型
            if (!isset($rule['type'])) {
                throw new \Exception("字段'{$key}'接受的数据类型未定义");
            }
            // [2.3] 必须字段未传递
            if (isset($json->$key)) {
                if ($json->$key === "") {
                    if ($rule['empty']) {
                        continue;
                    }
                    throw new ParamException("字段'{$key}'的值不能为空");
                }
            } else {
                if ($rule['required']) {
                    // [2.3.1] 必须字段
                    throw new ParamException("字段'{$key}'未传递");
                }
                if (isset($rule['default'])) {
                    // [2.3.2] 从默认值中提取
                    $json->$key = $rule['default'];
                }
                continue;
            }
            // [2.4] 按类型与规则检查
            switch (strtolower($rule['type'])) {
                case 'date' :
                    static::checkDate($json, $rule, $key);
                    break;
                case 'datetime' :
                    static::checkDatetime($json, $rule, $key);
                    break;
                case 'double' :
                case 'float' :
                    static::checkDouble($json, $rule, $key);
                    break;
                case 'email' :
                    static::checkEmail($json, $rule, $key);
                    break;
                case 'int' :
                case 'integer' :
                    static::checkInteger($json, $rule, $key);
                    break;
                case 'mobile' :
                    static::checkMobile($json, $rule, $key);
                    break;
                case 'string' :
                    static::checkString($json, $rule, $key);
                    break;
                case 'telphone' :
                    static::checkTelphone($json, $rule, $key);
                    break;
                case 'time' :
                    static::checkTime($json, $rule, $key);
                    break;
            }
        }
    }

    /**
     * 日期格式检查
     * <code>
     * $rule = [
     *     'type' => 'date',        // 限日期格式
     *     'required' => true,      // 是否为必须
     *     'min' => '2010-01-01',   // 最小日期
     *     'max' => '2099-12-31'    // 最大日期
     * ]
     * </code>
     *
     * @param object $json 表单数据
     * @param array  $rule 验证规则
     * @param string $key 字段名
     *
     * @throws ParamException
     */
    public static function checkDate(& $json, & $rule, $key)
    {
        // 1 类型检查
        $value = null;
        $parse = [];
        if ('string' === strtolower(gettype($json->$key))) {
            $parse = static::parseDate($json->$key);
            if (count($parse)) {
                $value = $json->$key;
            }
        }
        if ($value === null) {
            throw new ParamException("参数'{$key}'的日期值无效");
        }
        // 2. 最小时间
        if (isset($rule['min'])) {
            $parseMin = static::parseDate($rule['min']);
            if (count($parseMin) && $parseMin['number'] > $parse['number']) {
                throw new ParamException("参数'{$key}'的日期值不能早于'{$rule['min']}'");
            }
        }
        // 3. 最大时间
        if (isset($rule['max'])) {
            $parseMax = static::parseDate($rule['max']);
            if (count($parseMax) && $parseMax['number'] < $parse['number']) {
                throw new ParamException("参数'{$key}'的日期值不能迟于'{$rule['max']}'");
            }
        }
        // 4. 重置/修正参数结果
        $json->$key = $parse['response'];
    }

    /**
     * 时间格式检查
     * <code>
     * $rule = [
     *     'type' => 'datetime',            // 限完整时间格式
     *     'required' => true,              // 是否为必须
     *     'min' => '2010-01-01 00:00:00',  // 最小时间
     *     'max' => '2099-12-31 23:00:00'   // 最大时间
     * ]
     * </code>
     *
     * @param object $json 表单数据
     * @param array  $rule 验证规则
     * @param string $key 字段名
     *
     * @throws ParamException
     */
    public static function checkDatetime(& $json, & $rule, $key)
    {
        // 1 类型检查
        $value = null;
        $parse = [];
        if ('string' === strtolower(gettype($json->$key))) {
            $parse = static::parseDatetime($json->$key);
            if (count($parse)) {
                $value = $json->$key;
            }
        }
        if ($value === null) {
            throw new ParamException("参数'{$key}'的完整时间值无效");
        }
        // 2. 最小时间
        if (isset($rule['min'])) {
            $parseMin = static::parseDatetime($rule['min']);
            if (count($parseMin) && $parseMin['number'] > $parse['number']) {
                throw new ParamException("参数'{$key}'的完整时间值不能早于'{$rule['min']}'");
            }
        }
        // 3. 最大时间
        if (isset($rule['max'])) {
            $parseMax = static::parseDatetime($rule['max']);
            if (count($parseMax) && $parseMax['number'] < $parse['number']) {
                throw new ParamException("参数'{$key}'的完整时间值不能迟于'{$rule['max']}'");
            }
        }
        // 4. 重置/修正参数结果
        $json->$key = $parse['response'];
    }

    /**
     * 浮点型验证
     * <code>
     * $rule = [
     *     'type' => 'double',      // 限浮点型
     *     'required' => true,      // 是否为必须
     *     'min' => 0.01,           // 最小值
     *     'max' => 99.99           // 最大值
     * ]
     * </code>
     *
     * @param object $json 表单数据
     * @param array  $rule 验证规则
     * @param string $key 字段名
     *
     * @throws ParamException
     */
    public static function checkDouble(& $json, & $rule, $key)
    {
        // 1. 类型检查
        $type = strtolower(gettype($json->$key));
        $types = [
            'integer',
            'float',
            'double',
            'string'
        ];
        $value = null;
        if (in_array($type, $types)) {
            $temp = (string) $json->$key;
            if (preg_match(static::REGEXP_DOUBLE, $temp)) {
                $value = (double) $temp;
            }
        }
        if ($value === null) {
            throw new ParamException("参数'{$key}'的浮点型值无效");
        }
        // 2. 最小值
        if (isset($rule['min']) && $value < $rule['min']) {
            throw new ParamException("参数'{$key}'的浮点型值不能小于'{$rule['min']}'");
        }
        // 3. 最大值
        if (isset($rule['max']) && $value > $rule['max']) {
            throw new ParamException("参数'{$key}'的浮点型值不能大于'{$rule['max']}'");
        }
        // 4. 重置/修正参数结果
        $json->$key = $value;
    }

    /**
     * 邮箱格式验证
     * <code>
     * $rule = [
     *     'type' => 'email',       // 限邮箱格式
     *     'required' => true,      // 是否为必须
     * ]
     * </code>
     *
     * @param object $json 表单数据
     * @param array  $rule 验证规则
     * @param string $key 字段名
     *
     * @throws ParamException
     */
    public static function checkEmail(& $json, & $rule, $key)
    {
        // 1 类型检查
        $value = null;
        if ('string' === strtolower(gettype($json->$key))) {
            $value = preg_replace("/[^\d]+/", "", $json->$key);
            if (preg_match(static::REGEXP_EMAIL, $value) === 0) {
                $value = null;
            }
        }
        if ($value === null) {
            throw new ParamException("参数'{$key}'的邮箱地址无效");
        }
        // 2. 重置/修正参数结果
        $json->$key = strtolower($value);
    }

    /**
     * 整型验证
     * <code>
     * $rule = [
     *     'type' => 'int',         // 限整型
     *     'required' => true,      // 是否为必须
     *     'min' => 1,              // 最小值
     *     'max' => 100             // 最大值
     * ]
     * </code>
     *
     * @param object $json JSON格式的RAW对象
     * @param array  $rule 验证规则
     * @param string $key 字段名
     *
     * @throws ParamException
     */
    public static function checkInteger(& $json, & $rule, $key)
    {
        // 1. 类型检查
        $type = strtolower(gettype($json->$key));
        $types = [
            'integer',
            'string'
        ];
        $value = null;
        if (in_array($type, $types)) {
            $temp = (string) $json->$key;
            if (preg_match(static::REGEXP_INTEGER, $temp)) {
                $value = (int) $temp;
            }
        }
        if ($value === null) {
            throw new ParamException("参数'{$key}'的整型值无效");
        }
        // 2. 最小值
        if (isset($rule['min']) && $value < $rule['min']) {
            throw new ParamException("参数'{$key}'的整型值不能小于'{$rule['min']}'");
        }
        // 3. 最大值
        if (isset($rule['max']) && $value > $rule['max']) {
            throw new ParamException("参数'{$key}'的整型值不能大于'{$rule['max']}'");
        }
        // 4. 重置/修正参数结果
        $json->$key = $value;
    }

    /**
     * 手机号格式验证
     * <code>
     * $rule = [
     *     'type' => 'mobile',      // 限手机号
     *     'required' => true,      // 是否为必须
     * ]
     * </code>
     *
     * @param object $json 表单数据
     * @param array  $rule 验证规则
     * @param string $key 字段名
     *
     * @throws ParamException
     */
    public static function checkMobile(& $json, & $rule, $key)
    {
        // 1 类型检查
        $value = null;
        if ('string' === strtolower(gettype($json->$key))) {
            $value = preg_replace("/[^\d]+/", "", $json->$key);
            if (preg_match(static::REGEXP_MOBILE, $value) === 0) {
                $value = null;
            }
        }
        if ($value === null) {
            throw new ParamException("参数'{$key}'的手机号无效");
        }
        // 2. 重置/修正参数结果
        $json->$key = $value;
    }

    /**
     * 字符串验证
     * <code>
     * $rule = [
     *     'type' => 'string',      // 限字符串
     *     'required' => true,      // 是否为必须
     *     'options' => ['expired', 'success'],     // 限定字符串必须此中之一
     *     'min' => 6,              // 最小字符数
     *     'minChar' => 6,          // 最小字数
     *     'max' => 30,             // 最大字符数
     *     'maxChar' => 30,         // 最小字数
     * ]
     * </code>
     *
     * @param object $json JSON格式的RAW对象
     * @param array  $rule 验证规则
     * @param string $key 字段名
     *
     * @throws ParamException
     */
    public static function checkString(& $json, & $rule, $key)
    {
        // 1. 类型检查
        $type = strtolower(gettype($json->$key));
        $types = [
            'integer',
            'float',
            'double',
            'string'
        ];
        $value = null;
        if (in_array($type, $types)) {
            $value = (string) $json->$key;
        }
        if ($value === null) {
            throw new ParamException("参数'{$key}'的字符串值无效");
        }
        // 2. 选项型参数
        if (isset($rule['options']) && is_array($rule['options'])) {
            // 2.1.1 按选项定义
            if (in_array($value, $rule['options'])) {
                return;
            }
            // 2.1.2 不接受的字符串
            throw new ParamException("参数'{$key}'的字符串值必须为'".implode(', ', $rule['options'])."'中的一个");
        } else {
            // 2.2.1 字符长度范围
            $length = strlen($value);
            if (isset($rule['min']) && $length < $rule['min']) {
                throw new ParamException("参数'{$key}'的字符串值最少要求'{$rule['min']}'个字符");
            }
            if (isset($rule['max']) && $length > $rule['max']) {
                throw new ParamException("参数'{$key}'的字符串值最多要求'{$rule['max']}'个字符");
            }
            // 2.2.2 字长度范围
            $charLength = mb_strlen($value, static::CHARACTER_ENCODING);
            if (isset($rule['minChar']) && $charLength < $rule['minChar']) {
                throw new ParamException("参数'{$key}'的字符串值最少要求'{$rule['minChar']}'个字");
            }
            if (isset($rule['maxChar']) && $charLength > $rule['maxChar']) {
                throw new ParamException("参数'{$key}'的字符串值最多要求'{$rule['maxChar']}'个字");
            }
        }
    }

    /**
     * 验证电话号码
     * <code>
     * $rule = [
     *     'type' => 'telphone',
     *     'required' => true,
     * ]
     * </code>
     *
     * @param object $json 表单数据
     * @param array  $rule 验证规则
     * @param string $key 字段名
     *
     * @throws ParamException
     */
    public static function checkTelphone(& $json, & $rule, $key)
    {
        $telphone = preg_replace("/[^\d]+/", '-', $json->$key);
        $parse = self::parseTelphone($telphone);
        if (count($parse) === 0) {
            throw new ParamException("字段'{$key}'的值不是有效的电话号码");
        }
        $json->$key = $parse['response'];
    }

    /**
     * 时间格式检查
     * <code>
     * $rule = [
     *     'type' => 'time',        // 限时间格式
     *     'required' => true,      // 是否为必须
     *     'min' => '08:00:00',     // 最小日期
     *     'max' => '21:30:00'      // 最大日期
     * ]
     * </code>
     *
     * @param object $json 表单数据
     * @param array  $rule 验证规则
     * @param string $key 字段名
     *
     * @throws ParamException
     */
    public static function checkTime(& $json, & $rule, $key)
    {
        // 1 类型检查
        $value = null;
        $parse = [];
        if ('string' === strtolower(gettype($json->$key))) {
            $parse = static::parseTime($json->$key);
            if (count($parse)) {
                $value = $json->$key;
            }
        }
        if ($value === null) {
            throw new ParamException("参数'{$key}'的时间值无效");
        }
        // 2. 最小时间
        if (isset($rule['min'])) {
            $parseMin = static::parseTime($rule['min']);
            if (count($parseMin) && $parseMin['number'] > $parse['number']) {
                throw new ParamException("参数'{$key}'的时间值不能早于'{$rule['min']}'");
            }
        }
        // 3. 最大时间
        if (isset($rule['max'])) {
            $parseMax = static::parseTime($rule['max']);
            if (count($parseMax) && $parseMax['number'] < $parse['number']) {
                throw new ParamException("参数'{$key}'的时间值不能迟于'{$rule['max']}'");
            }
        }
        // 4. 重置/修正参数结果
        $json->$key = $parse['response'];
    }

    /**
     * 从日期字符串中截取各元素值
     * <code>
     * return [
     *     'year' => 2017,              // 年份
     *     'month' => 12,               // 月份
     *     'day' => 1,                  // 日期
     *     'number' => 20171201,        // 用于范围比较的整型
     *     'response' => '2017-12-01',  // 格式化输出
     * ]
     * </code>
     *
     * @param string $value 日期字符串
     *
     * @return array 当格式错误时返回空数组
     * @example Param::parseDate('2017-12-01')
     */
    private static function parseDate($value)
    {
        $result = [];
        if (preg_match(static::REGEXP_DATE, $value, $m) > 0) {
            // 1. 整型
            $result['year'] = (int) $m[1];
            $result['month'] = (int) $m[2];
            $result['month'] < 1 && $result['month'] = 1;
            $result['month'] > 12 && $result['month'] = 12;
            $result['day'] = (int) $m[3];
            $result['day'] < 1 && $result['day'] = 0;
            // 2. 最大天修正
            $maxDay = 31;
            $maxDayLowers = [
                4,
                6,
                9,
                11
            ];
            if (in_array($result['month'], $maxDayLowers) && $result['day'] > 30) {
                $result['day'] = 30;
            } else if ($result['month'] == 2) {
                $maxDay = 28;
                if ($result['year'] % 4 === 0 && ($result['year'] % 100 > 0 || $result['year'] % 400 == 0)) {
                    $maxDay = 29;
                }
            }
            if ($result['day'] > $maxDay) {
                $result['day'] = $maxDay;
            }
            // 用于时间比较
            $result['number'] = (int) ($result['year'] * 10000 + $result['month'] * 100 + $result['day']);
            $result['response'] = (string) sprintf("%04d-%02d-%02d", $result['year'], $result['month'], $result['day']);
        }
        return $result;
    }

    /**
     * 从完整时间字符串中截取各元素值
     * <code>
     * return [
     *     'year' => 2017,                          // 年份
     *     'month' => 12,                           // 月份
     *     'day' => 1,                              // 日期
     *     'hour' => 10,                            // 时间
     *     'minute' => 0,                           // 分钟
     *     'second' => 0,                           // 秒数
     *     'number' => 2017120136000,               // 用于范围比较的整型
     *     'response' => '2017-12-01 10:00:00',     // 格式化输出
     * ]
     * </code>
     *
     * @param string $value 含日期的时间字符串
     *
     * @return array 当格式错误时返回空数组
     * @example Param::parseDatetime('2017-12-01 10:00:00')
     */
    private static function parseDatetime($value)
    {
        $result = [];
        if (preg_match(static::REGEXP_DATETIME, $value, $m) > 0) {
            // 1. 整型
            $result['year'] = (int) $m[1];
            $result['month'] = (int) $m[2];
            $result['month'] < 1 && $result['month'] = 1;
            $result['month'] > 12 && $result['month'] = 12;
            $result['day'] = (int) $m[3];
            $result['day'] < 1 && $result['day'] = 0;
            $result['hour'] = (int) $m[4];
            $result['hour'] < 1 && $result['hour'] = 1;
            $result['hour'] > 23 && $result['hour'] = 23;
            $result['minute'] = (int) $m[5];
            $result['minute'] > 59 && $result['minute'] = 59;
            $result['second'] = (int) $m[6];
            $result['second'] > 59 && $result['second'] = 59;
            // 2. 最大天修正
            $maxDay = 31;
            $maxDayLowers = [
                4,
                6,
                9,
                11
            ];
            if (in_array($result['month'], $maxDayLowers) && $result['day'] > 30) {
                $result['day'] = 30;
            } else if ($result['month'] == 2) {
                $maxDay = 28;
                if ($result['year'] % 4 === 0 && ($result['year'] % 100 > 0 || $result['year'] % 400 == 0)) {
                    $maxDay = 29;
                }
            }
            if ($result['day'] > $maxDay) {
                $result['day'] = $maxDay;
            }
            // 用于时间比较
            $result['number'] = (int) (($result['year'] * 10000 + $result['month'] * 100 + $result['day']) * 100000 + ($result['hour'] * 3600 + $result['minute'] * 60 + $result['second']));
            $result['response'] = (string) sprintf("%04d-%02d-%02d %02d:%02d:%02d", $result['year'], $result['month'], $result['day'], $result['hour'], $result['minute'], $result['second']);
        }
        return $result;
    }

    /**
     * 从时间字符串中截取各元素值
     * <code>
     * return [
     *     'area' => '025',                     // 区号
     *     'body' => '88888888',                // 主号码
     *     'child' => '8888',                   // 分机号
     *     'response' => '025-88888888-8888'    // 格式化输出
     * ];
     * </code>
     *
     * @param string $value 时间字符串
     *
     * @return array 当格式错误时返回空数组
     * @example Param::parseTelphone('95588')
     * @example Param::parseTelphone('4008365365')
     * @example Param::parseTelphone('010-88888888')
     * @example Param::parseTelphone('010-88888888-888')
     */
    private static function parseTelphone($value)
    {
        $result = [];
        foreach (self::$telphoneRegexps as $regexp) {
            $length = preg_match($regexp, $value, $m);
            if ($length > 0) {
                switch (count($m)) {
                    case 2 :
                        $result['body'] = $m[1];
                        break;
                    case 3 :
                        $result['area'] = $m[1];
                        $result['body'] = $m[2];
                        break;
                    case 4 :
                        $result['area'] = $m[1];
                        $result['body'] = $m[2];
                        $result['child'] = $m[3];
                        break;
                }
                array_shift($m);
                $result['response'] = implode('-', $m);
                break;
            }
        }
        return $result;
    }

    /**
     * 从时间字符串中截取各元素值
     * <code>
     * return [
     *     'hour' => 10,                // 时间值
     *     'minute' => 0,               // 分钟值
     *     'second' => 0,               // 秒数值
     *     'number' => 36000,           // 当天中的秒数(0-86399)/用于范围比较的整型
     *     'response' => '10:00:00'     // 格式化输出
     * ];
     * </code>
     *
     * @param string $value 时间字符串
     *
     * @return array 当格式错误时返回空数组
     * @example Param::parseTime('10:00')
     * @example Param::parseTime('10:00:00')
     */
    private static function parseTime($value)
    {
        $result = [];
        if (preg_match(static::REGEXP_TIME, $value, $m) > 0) {
            // 1. 整型
            $result['hour'] = (int) $m[1];
            $result['hour'] < 1 && $result['hour'] = 1;
            $result['hour'] > 23 && $result['hour'] = 23;
            $result['minute'] = (int) $m[2];
            $result['minute'] > 59 && $result['minute'] = 59;
            $result['second'] = (int) $m[3];
            $result['second'] > 59 && $result['second'] = 59;
            // 用于时间比较
            $result['number'] = (int) ($result['hour'] * 3600 + $result['minute'] * 60 + $result['second']);
            $result['response'] = (string) sprintf("%02d:%02d:%02d", $result['hour'], $result['minute'], $result['second']);
        }
        return $result;
    }
}
