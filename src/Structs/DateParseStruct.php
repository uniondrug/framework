<?php
/**
 * cn.uniondrug
 * @author wsfuyibing <websearch@163.com>
 * @date 2018-01-17
 */
namespace Pails\Structs;

/**
 * 日期验证结构体
 * @property int  $year 年份(YYYY)
 * @property int  $month 月份(1-12)
 * @property int  $day 日期(1-31)
 * @property int  $number 和值(0-86399)
 * @property string $response 标准输出格式
 * @property bool $parsed 是否Parse成功
 * @package Pails\Structs
 */
class DateParseStruct extends Structs
{
    const REGEXP = "/^(\d+)[^\d]+(\d+)[^\d]+(\d+)$/";

    /**
     * 实例化日期结构体
     *
     * @param null $value
     */
    public function __construct($value = null)
    {
        $defaults = $this->parseDateValue($value);
        parent::__construct($defaults);
    }

    /**
     * 解析日期
     *
     * @param string $value 日期字符串
     *
     * @return array
     * @example $this->parseDateValue('2018-01-01')
     */
    private function parseDateValue($value = null)
    {
        $result = [
            'year' => 0,
            'month' => 0,
            'day' => 0,
            'number' => 0,
            'response' => '',
            'parsed' => false
        ];
        if (is_string($value) && preg_match(self::REGEXP, $value, $m) > 0) {
            // 1. unix timestamp switch
            $timeStamp = gmmktime(0, 0, 0, $m[2], $m[3], $m[1]);
            $timeDatas = explode('-', gmdate('Y-n-j', $timeStamp));
            // 2. convert timestamp to date
            $result['year'] = (int) $timeDatas[0];
            $result['month'] = (int) $timeDatas[1];
            $result['day'] = (int) $timeDatas[2];
            // 3. state
            $result['parsed'] = true;
            $result['number'] = $result['year'] * 10000 + $result['month'] * 100 + $result['day'];
            $result['response'] = sprintf("%d-%d-%d", $result['year'], $result['month'], $result['day']);
        }
        return $result;
    }
}