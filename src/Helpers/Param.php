<?php
/**
 * 框架级Helper
 * @author wsfuyibing <websearch@163.com>
 * @date 2017-12-26
 */
namespace Pails\Helpers;

use Pails\Validators\DatetimeValidator;
use Pails\Validators\DateValidator;
use Pails\Validators\DoubleValidator;
use Pails\Validators\EmailValidator;
use Pails\Validators\IntegerValidator;
use Pails\Validators\MobileValidator;
use Pails\Validators\StringValidator;
use Pails\Validators\TelphoneValidator;
use Pails\Validators\TimeValidator;
use Phalcon\Exception;

/**
 * 参数检查, 运行以下类型
 * @package Pails\Helpers
 */
class Param extends \stdClass
{
    /**
     * @var array 验证类型与类关系
     */
    static $validatorConfig = [
        'datetime' => DatetimeValidator::class,
        'date' => DateValidator::class,
        'float' => DoubleValidator::class,
        'double' => DoubleValidator::class,
        'email' => EmailValidator::class,
        'int' => IntegerValidator::class,
        'integer' => IntegerValidator::class,
        'mobile' => MobileValidator::class,
        'string' => StringValidator::class,
        'telphone' => TelphoneValidator::class,
        'time' => TimeValidator::class
    ];

    /**
     * 参数检查
     * <code>
     * // 以下示例应用于Controller
     * $json = $this->getJsonRawBody();
     * $rules = [
     *     'id' => [
     *         'type' => 'int',
     *         'required' => true,
     *         'min' => 1,
     *     ],
     *     'status' => [
     *         'type' => 'string',
     *         'required' => true,
     *         'options' => ['success', 'expired']
     *     ]
     * ];
     * Param::check($json, $rules);
     * </code>
     *
     * @param object $json JSON格式的RAW对象
     * @param array  $rules JSON
     *
     * @throws \Exception|ParamException
     */
    public static function check(& $json, & $rules)
    {
        $validation = new Validation();
        // 1. 遍历规则
        foreach ($rules as $key => $rule) {
            // 1.1 不限制
            if ($rule === null) {
                continue;
            }
            // 1.2 规则定义
            if (!is_array($rule) || !isset($rule['type'])) {
                throw new Exception("字段'{$key}'的规则定义不合法");
            }
            // 1.3 加入验证
            $type = strtolower($rule['type']);
            if (isset(static::$validatorConfig[$type])) {
                $validation->add($key, new static::$validatorConfig[$type]($rule));
            }
        }
        // 2. 批量验证
        $validation->validate($json);
        // 3. 验证过程有错误
        if ($validation->hasFailure()) {
            throw new ParamException($validation->getFailureMessage());
        }
        // 4. 数据合并
        $merge = $validation->getMergeDefault();
        foreach ($merge as $key => $value) {
            $json->$key = $value;
        }
    }
}
