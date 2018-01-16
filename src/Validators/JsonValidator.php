<?php
/**
 * 验证一个字符串是否是合法的JSON格式。通过json_decode一下之后，检测错误信息实现。
 *
 * @author XueronNi <xueronni@uniondrug.cn>
 * @date   2018-01-01
 */

namespace Pails\Validators;

use Phalcon\Validation;
use Phalcon\Validation\Validator;

class JsonValidator extends Validator
{
    /**
     * @param \Phalcon\Validation $validation
     * @param                     $field
     *
     * @return bool
     */
    public function validate(Validation $validation, $field)
    {
        $value = $validation->getValue($field);

        $data = \json_decode($value);
        if (JSON_ERROR_NONE !== json_last_error()) {
            //json_last_error_msg()
            $label = $this->prepareLabel($validation, $field);
            $message = $this->prepareMessage($validation, $field, 'Json');
            $code = $this->prepareCode($field);
            $replace = [':field' => $label, ':err' => json_last_error_msg()];
            $validation->appendMessage(
                new Validation\Message(strtr($message, $replace), $field, 'Json', $code)
            );

            return false;
        }

        return true;
    }
}
