<?php
/**
 * cn.uniondrug
 * @author wsfuyibing <websearch@163.com>
 * @date 2018-01-16
 */
namespace Pails\Structs;

/**
 * 时间验证结构体
 * @property int  $hour 时值(0-23)
 * @property int  $minute 分值(0-59)
 * @property int  $second 秒值(0-59)
 * @property int  $number 和值(0-86399)
 * @property string $response 标准格式输出
 * @property bool $parsed 是否Parse成功
 * @package Pails\Structs
 */
class TimeParseStruct extends Structs
{
    const REGEXP = "/^(\d+):(\d+)[:]?(\d*)$/";

    /**
     * 实例化时间结构体
     *
     * @param null $value
     */
    public function __construct($value = null)
    {
        $defaults = $this->parseTimeValue($value);
        parent::__construct($defaults);
    }

    /**
     * 解析时间
     *
     * @param string $value 时间字符串
     *
     * @return array
     * @example $this->parseTimeValue('19:00')
     * @example $this->parseTimeValue('08:09:10')
     */
    private function parseTimeValue($value = null)
    {
        $result = [
            'hour' => 0,
            'minute' => 0,
            'second' => 0,
            'number' => 0,
            'response' => '',
            'parsed' => false
        ];
        if (is_string($value) && preg_match(self::REGEXP, $value, $m) > 0) {
            $result['hour'] = min((int) $m[1], 23);
            $result['minute'] = min((int) $m[2], 59);
            $result['second'] = min((int) $m[3], 59);
            $result['parsed'] = true;
            $result['number'] = $result['hour'] * 3600 + $result['minute'] * 60 + $result['second'];
            $result['response'] = sprintf("%d:%d:%d", $result['hour'], $result['minute'], $result['second']);
        }
        return $result;
    }
}