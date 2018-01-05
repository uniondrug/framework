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
 * 验证电话号码
 * @package Pails\Validators
 */
class TelphoneValidator extends Validator
{

    /**
     * 固定电话的规则
     * 1. 特殊1号: 95588、10000、10086
     * 2. 特殊2号: 4008365365
     * 3. 普通1号(号): 7777777、88888888
     * 4. 普通2号(区-号): 010-88888888、0553-2237136
     * 5. 普通3号(区-号-分): 025-88888888-3721
     * @var array
     */
    private static $regexps = [
        "/^([1-9][0-9]{4})$/",
        "/^([48]00[0-9]{7})$/",
        "/^([2-9][0-9]{6,7})$/",
        "/^(010|02[0-9]|0[3-9][0-9]{2})[^\d]*([2-9][0-9]{6,7})$/",
        "/^(010|02[0-9]|0[3-9][0-9]{2})[^\d]*([2-9][0-9]{6,7})[^\d]*([0-9]+)$/"
    ];

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
        foreach (self::$regexps as $regexp){
            if (preg_match($regexp, $validation->getValue($attribute)) > 0){
                return true;
            }
        }
        $validation->appendMessage(new Message(
            "参数'{$attribute}'不是有效的电话号码",
            $attribute
        ));
        return false;
    }
}