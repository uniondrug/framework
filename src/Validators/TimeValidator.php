<?php
/**
 * 框架级Validator
 * @author wsfuyibing <websearch@163.com>
 * @date 2018-01-05
 */
namespace Pails\Validators;

use Pails\Helpers\Validation;
use Phalcon\Validation\Message;

/**
 * 验证时间格式
 * <code>
 * new TimeValidator([
 *     'min' => '08:00',
 *     'max' => '21:30'
 * ])
 * </code>
 * @package Pails\Validators
 */
class TimeValidator extends Validator
{
    private static $regexp = "/^(\d+):(\d+)[:]?(\d*)$/";

    /**
     * 执行验证
     *
     * @param Validation $validation Validation对象
     * @param string     $attribute 待验证的字段/参数名
     *
     * @return bool
     */
    public function validate(\Phalcon\Validation $validation, $attribute)
    {
        // 1. 必须和非空验证
        if (!$this->validateRequired($validation, $attribute) || !$this->validateEmpty($validation, $attribute)) {
            return false;
        }
        // 2. 格式检查
        $value = $validation->getValue($attribute);
        $parsed = $this->parseTime($value);
        if ($parsed === null){
            $validation->appendMessage(new Message("参数'{$attribute}'的值不是有效的时间", $attribute));
            return false;
        }
        // 3. 时间范围


        return true;
    }

    /**
     * 解析时间值
     *
     * @param string $value 时间值
     *
     * @return object|null
     */
    private function parseTime($value)
    {
        // 1. 非时间格式
        if (preg_match(self::$regexp, $value, $m) === 0) {
            return null;
        }
        // 2. 时间格式正确
        $obj = new \stdClass();
        $obj->hour = min((int) $m[1], 23);
        $obj->minute = min((int) $m[2], 59);
        $obj->second = min((int) $m[3], 59);
        $obj->number = $obj->hour * 3600 + $obj->minute * 60 + $obj->second;
        return $obj;
    }
}