<?php
/**
 * cn.uniondrug
 * @author wsfuyibing <websearch@163.com>
 * @date 2018-01-17
 */
namespace Pails\Structs;

/**
 * 含日期的时间验证结构体
 * @property int  $year 年份(YYYY)
 * @property int  $month 月份(1-12)
 * @property int  $day 日期(1-31)
 * @property int  $hour 时值(0-23)
 * @property int  $minute 分值(0-59)
 * @property int  $second 秒值(0-59)
 * @property int  $number 和值(0-86399)
 * @property string $response 标准输出格式
 * @property bool $parsed 是否Parse成功
 * @package Pails\Structs
 */
class DatetimeParseStruct extends Structs
{
    const REGEXP = "/^(\d+)[^\d]+(\d+)[^\d]+(\d+)[^\d]+(\d+):(\d+)[:]*(\d*)$/";

    /**
     * 实例化含日期的时间结构体
     *
     * @param null $value
     */
    public function __construct($value = null)
    {
        $defaults = $this->parseDatetimeValue($value);
        parent::__construct($defaults);
    }

    /**
     * 解析含日期的时间
     *
     * @param string $value 含日期的时间字符串
     *
     * @return array
     * @example $this->parseDatetimeValue('2018-01-01 08:09')
     * @example $this->parseDatetimeValue('2018-01-01 08:09:10')
     */
    private function parseDatetimeValue($value = null)
    {
        $result = [
            'year' => 0,
            'month' => 0,
            'day' => 0,
            'hour' => 0,
            'minute' => 0,
            'second' => 0,
            'number' => 0,
            'parsed' => false
        ];
        if (is_string($value) && preg_match(self::REGEXP, $value, $m) > 0) {
            // 1. unix timestamp switch
            $timeStamp = gmmktime($m[4], $m[5], (int) $m[6], $m[2], $m[3], $m[1]);
            $timeDatas = explode('-', gmdate('Y-n-j-H-i-s', $timeStamp));
            // 2. convert timestamp to date
            $result['year'] = (int) $timeDatas[0];
            $result['month'] = (int) $timeDatas[1];
            $result['day'] = (int) $timeDatas[2];
            $result['hour'] = (int) $timeDatas[3];
            $result['minute'] = (int) $timeDatas[4];
            $result['second'] = (int) $timeDatas[5];
            // 3. state
            $result['parsed'] = true;
            $result['number'] = ($result['year'] * 10000 + $result['month'] * 100 + $result['day']) * 100000 + ($result['hour'] * 3600 + $result['minute'] * 60 + $result['second']);
            $result['response'] = sprintf("%d-%d-%d %d:%d:%d", $result['year'], $result['month'], $result['day'], $result['hour'], $result['minute'], $result['second']);

        }
        return $result;
    }
}