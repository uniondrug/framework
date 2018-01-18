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
 * 验证整型值
 * <code>
 * $validation = new Validation();                  // 创建Validation实例
 * $attribute = 'field';                            // 参数名称
 * $options = [                                     // 验证选项
 *     'required' => true,                          // 是否必填
 *     'empty' => false,                            // 是否允许为空
 *     'min' => -180,                               // 最小值
 *     'max' => 180                                 // 最大值
 * ];
 * $validator = new IntegerValidator($options);
 * $validation->add($attribute, $validator);
 * $validation->validate();
 * </code>
 * @package Pails\Validators
 */
class IntegerValidator extends Validator
{
    private static $regexp = "/^[\+|\-]*[0-9]$/";

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
        $value = (string) $validation->getValue($attribute);
        // 3. 允许为空(当禁止为空时已由validateEmpty()过滤)
        if ($value === '') {
            return true;
        }
        // 4. 格式检查
        if (preg_match(self::$regexp, $value) == 0) {
            $validation->appendMessage(new Message("参数'{$attribute}'的值不是有效的数值", $attribute));
            return false;
        }
        // 5. 最小值
        $value = (int) $value;
        $minValue = $this->getOption('min');
        if (is_numeric($minValue) && $value < (int) $minValue){
            $validation->appendMessage(new Message("参数'{$attribute}'的值不能小于'{$minValue}'", $attribute));
            return false;
        }
        // 6. 最大值
        $maxValue = $this->getOption('max');
        if (is_numeric($maxValue) && $value > (int) $maxValue){
            $validation->appendMessage(new Message("参数'{$attribute}'的值不能大于'{$maxValue}'", $attribute));
            return false;
        }
        // 7. 格式正确
        return true;
    }
}