<?php
/**
 * Json.php
 *
 */

namespace Pails\Validators;

use Phalcon\Validation;
use Phalcon\Validation\Validator;

class Json extends Validator
{
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
