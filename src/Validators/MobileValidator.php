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
 * 验证手机号
 * <code>
 * $validation = new Validation();                  // 创建Validation实例
 * $attribute = 'field';                            // 参数名称
 * $options = [];                                   // 验证选项
 * $validator = new MobileValidator($options);
 * $validation->add($attribute, $validator);
 * $validation->validate();
 * </code>
 * @package Pails\Validators
 */
class MobileValidator extends Validator
{
    private static $regexp = "/^1[3-9][0-9]{9}$/";

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
        // 3. 允许为空(当禁止为空时已由validateEmpty()过滤)
        if ($value === '') {
            return true;
        }
        // 4. 格式检查
        if (preg_match(self::$regexp, $value) > 0) {
            return true;
        }
        // 3. 格式有错误
        $validation->appendMessage(new Message("参数'{$attribute}'的值不是有效的手机号", $attribute));
        return false;
    }
}