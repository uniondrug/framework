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
 * 验证字符串
 * <code>
 * $validation = new Validation();                  // 创建Validation实例
 * $attribute = 'field';                            // 参数名称
 * $options = [                                     // 验证选项
 *     'required' => true,                          // 是否必填
 *     'empty' => false,                            // 是否允许为空
 *     'options' => [                               // 限定字符串
 *         'enable',
 *         'disable'
 *     ],
 *     'min' => 10,                                 // 最少10个字符(UTF8以一个中文3个字符)
 *     'minChar' => 3,                              // 最少 3个文字(1个中文、数字、字母都算为1个字)
 *     'max' => 30,                                 // 最多30个字符(UTF8以一个中文3个字符)
 *     'maxChar' => 10                              // 最少10个文字(1个中文、数字、字母都算为1个字)
 * ];
 * $validator = new StringValidator($options);
 * $validation->add($attribute, $validator);
 * $validation->validate();
 * </code>
 * @package Pails\Validators
 */
class StringValidator extends Validator
{
    private static $encodeing = 'UTF-8';

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
        // 4. 限定字符串
        //    如指定参数只接受'enable'、'disabled'之中的一个
        $strings = $this->getOption('options');
        if (is_array($strings) && count($strings)) {
            $inStrings = false;
            foreach ($strings as $str) {
                if ($str === $value) {
                    $inStrings = true;
                    break;
                }
            }
            if ($inStrings) {
                return true;
            }
            $validation->appendMessage(new Message("参数'{$attribute}'的值须为'".implode("'、'", $strings)."'中的一个", $attribute));
            return false;
        }
        // 5. 字符长度
        $len = strlen($value);
        // 5.1 最小字符长度
        $minLen = $this->getOption('min');
        if (is_numeric($minLen) && $minLen > 0 && $len < $minLen) {
            $validation->appendMessage(new Message("参数'{$attribute}'的值的长度不能少于'{$minLen}'个字符", $attribute));
            return false;
        }
        // 5.2 最大字符长度
        $maxLen = $this->getOption('max');
        if (is_numeric($maxLen) && $maxLen > 0 && $len > $maxLen) {
            $validation->appendMessage(new Message("参数'{$attribute}'的值的长度不能多于'{$maxLen}'个字符", $attribute));
            return false;
        }
        // 6. 字长度
        $charLen = mb_strlen($value, static::$encodeing);
        // 6.1 最小字符长度
        $minChar = $this->getOption('minChar');
        if (is_numeric($minChar) && $minChar > 0 && $charLen < $minChar) {
            $validation->appendMessage(new Message("参数'{$attribute}'的值的长度不能少于'{$minChar}'个字", $attribute));
            return false;
        }
        // 6.2 最大字符长度
        $maxChar = $this->getOption('maxChar');
        if (is_numeric($maxChar) && $maxChar > 0 && $charLen > $maxChar) {
            $validation->appendMessage(new Message("参数'{$attribute}'的值的长度不能少于'{$maxChar}'个字", $attribute));
            return false;
        }
        // 7. 字符正确
        return true;
    }
}