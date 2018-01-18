<?php
/**
 * 验证一个表达式是否是一个规范的Crontab格式。
 *
 * 需要composer require dragonmantank/cron-expression
 *
 * @author XueronNi <xueronni@uniondrug.cn>
 * @date   2018-01-01
 */

namespace Pails\Validators;

use Cron\CronExpression;
use Phalcon\Validation;
use Phalcon\Validation\Validator;

class CrontabValidator extends Validator
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
        if (!CronExpression::isValidExpression($value)) {
            $label = $this->prepareLabel($validation, $field);
            $message = $this->prepareMessage($validation, $field, 'Crontab');
            $code = $this->prepareCode($field);
            $replace = [':field' => $label];
            $validation->appendMessage(
                new Validation\Message(strtr($message, $replace), $field, 'Crontab', $code)
            );

            return false;
        }

        return true;
    }
}
