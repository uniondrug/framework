<?php
/**
 * 框架级Validator
 * @author wsfuyibing <websearch@163.com>
 * @date 2018-01-05
 */
namespace Pails\Validators;

use Pails\Helpers\Validation;
use Pails\Structs\DateParseStruct;
use Phalcon\Validation\Message;

/**
 * 验证日期格式
 * <code>
 * $validation = new Validation();                  // 创建Validation实例
 * $attribute = 'field';                            // 参数名称
 * $options = [                                     // 验证选项
 *     'required' => 'true',                        // 是否必须
 *     'empty' => 'false',                          // 是否允许为空
 *     'default' => '2018-01-15',                   // 当不传字段时赋默认值
 *     'min' => '2018-01-01',                       // 最小日期
 *     'max' => '2018-12-31'                        // 最大日期
 * ];
 * $validator = new DateValidator($options);
 * $validation->add($attribute, $validator);
 * $validation->validate();
 * </code>
 * @package Pails\Validators
 */
class DateValidator extends Validator
{
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
        // 4. 日期格式检查
        $parsed = new DateParseStruct($value);
        if (!$parsed->parsed) {
            $validation->appendMessage(new Message("参数'{$attribute}'的值不是有效的日期", $attribute));
            return false;
        }
        // 5. 最小值
        $minValue = $this->getOption('min');
        if ($minValue != null) {
            $minParsed = new DateParseStruct($minValue);
            if ($minParsed->parsed && $parsed->number < $minParsed->number) {
                $validation->appendMessage(new Message("参数'{$attribute}'的值不能小于'{$minValue}'", $attribute));
                return false;
            }
        }
        // 6. 最大日期
        $maxValue = $this->getOption('max');
        if ($maxValue != null) {
            $maxParsed = new DateParseStruct($maxValue);
            if ($maxParsed->parsed && $parsed->number > $maxParsed->number) {
                $validation->appendMessage(new Message("参数'{$attribute}'的值不能大于'{$maxValue}'", $attribute));
                return false;
            }
        }
        // 7. 日期正确
        return true;
    }
}
