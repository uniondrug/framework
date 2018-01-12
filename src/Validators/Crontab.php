<?php
/**
 * Crontab.php
 *
 */

namespace Pails\Validators;

use Cron\CronExpression;
use Phalcon\Validation;
use Phalcon\Validation\Validator;

class Crontab extends Validator
{
    public function validate(Validation $validation, $field)
    {
        $value = $validation->getValue($field);
        if (!CronExpression::isValidExpression($value)) {
            $label = $this->prepareLabel($validation, $field);
            $message = $this->prepareMessage($validation, $field, 'Crontab');
            $code = $this->prepareCode($field);
            $replace = [':field', $label];
            $validation->appendMessage(
                new Validation\Message(strtr($message, $replace), $field, 'Crontab', $code)
            );

            return false;
        }

        return true;
    }
}
