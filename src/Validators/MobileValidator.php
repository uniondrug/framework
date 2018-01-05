<?php
/**
 * 框架级Validator
 * @author wsfuyibing <websearch@163.com>
 * @date 2018-01-05
 */
namespace Pails\Validators;

use Phalcon\Validation\Message;
use Phalcon\Validation\Validator;

/**
 * 验证手机号
 * @package Pails\Validators
 */
class MobileValidator extends Validator
{
    private static $regexp = "/^1[3-9][0-9]{9}$/";

    /**
     * 执行验证
     *
     * @param \Phalcon\Validation $validation Validation对象
     * @param string              $attribute 待验证的字段/参数名
     *
     * @return bool
     */
    public function validate(\Phalcon\Validation $validation, $attribute)
    {
        $value = $validation->getValue($attribute);
        if (preg_match(self::$regexp, $value) > 0) {
            return true;
        }
        $validation->appendMessage(new Message(
            "参数'{$attribute}'不是有效的手机号",
            $attribute
        ));
        return false;
    }
}