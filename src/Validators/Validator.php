<?php
/**
 * 框架级Validator
 * @author wsfuyibing <websearch@163.com>
 * @date 2018-01-05
 */
namespace Pails\Validators;

use Pails\Helpers\Validation;
use Phalcon\Filter;
use Phalcon\Validation\Message;
use Phalcon\Validation\Validator as PhalconValidator;

/**
 * Validator基类
 * @package Pails\Validators
 */
abstract class Validator extends PhalconValidator
{
    /**
     * @var Filter
     */
    protected $filter;

    public function __construct($options = [])
    {
        parent::__construct($options);
        $this->filter = new Filter();
    }

    /**
     * 空值字段检查
     *
     * @param Validation $validation Validation对象
     * @param string     $attribute 待验证的字段/参数名
     *
     * @return bool
     */
    protected function validateEmpty($validation, $attribute)
    {
        // 1. 已是非空
        if ($validation->getValue($attribute) !== '') {
            return true;
        }
        // 2. 允放为空
        if ($this->getOption('empty') === true) {
            return true;
        }
        // 3.
        $validation->appendMessage(new Message("参数'{$attribute}'的值不能为空", $attribute));
        return false;
    }

    /**
     * 必须字段验证
     *
     * @param Validation $validation Validation对象
     * @param string     $attribute 待验证的字段/参数名
     *
     * @return bool
     */
    protected function validateRequired($validation, $attribute)
    {
        // 1. 字段已传递
        if ($validation->getValue($attribute) !== null) {
            return true;
        }
        // 2. 限制必须传递时

        var_dump('required: ', $this->getOption('required'));

        if ($this->getOption('required') === true) {
            echo __METHOD__."->Required\r\n";
            $validation->appendMessage(new Message("参数'{$attribute}'未传递", $attribute));
            return false;
        }
        // 3. 设置默认值
        $defaultValue = $this->getOption('default');
        if ($defaultValue !== null) {
            $validation->mergeDefault($attribute, $defaultValue);
        }
        return true;
    }



}