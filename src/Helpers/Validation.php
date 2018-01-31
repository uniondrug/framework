<?php
/**
 * 框架级验证
 * @author wsfuyibing <websearch@163.com>
 * @date 2017-12-26
 */
namespace Pails\Helpers;

use Phalcon\Validation\Message\Group;
use Phalcon\Validation as PhalconValidation;
use Phalcon\Validation\ValidatorInterface;
use Phalcon\ValidationInterface;

/**
 * 参数检查, 运行以下类型
 * <ul>
 * <li>date</li>
 * <li>datetime</li>
 * <li>double</li>
 * <li>email</li>
 * <li>integer</li>
 * <li>mobile</li>
 * <li>string</li>
 * <li>time</li>
 * </ul>
 * @package Pails\Helpers
 */
class Validation extends PhalconValidation
{
    /**
     * 参数(字段)的记数器
     * 1. 指定参数使用了几种规则['validators' => 3]
     * 2. 指定的规则中验证失败的有几个['failures' => 2]
     * 3. 当 validators == failures 时为未能过验证
     * [如下例]
     * 电话号码参数: 可以填写手机号或固定电话, 任意一项通过即正确。
     * @var array
     */
    private $stats = [];
    /**
     * @var array 待合并的数据组
     */
    private $mergeData = [];

    /**
     * 添加验证规则
     *
     * @param mixed|string       $field 字段/参数名称
     * @param ValidatorInterface $validator 验证对象
     *
     * @return ValidationInterface
     */
    public function add($field, ValidatorInterface $validator)
    {
        /**
         * 同步计数器
         */
        if (!isset($this->stats[$field])) {
            $this->stats[$field] = [
                'validators' => 0,
                'failures' => 0
            ];
        }
        $this->stats[$field]['validators'] += 1;
        /**
         * 加入规则
         */
        return parent::add($field, $validator);
    }

    /**
     * 后置验证
     *
     * @param array|object $data 待验证的数据源
     * @param null         $entity unknown
     * @param Group        $messages 消费集合
     */
    public function afterValidation($data, $entity, $messages)
    {
        /**
         * @var PhalconValidation\Message $message
         */
        foreach ($messages as $message) {
            $field = $message->getField();
            if (isset($this->stats[$field])) {
                $this->stats[$field]['failures'] += 1;
            }
        }
    }

    /**
     * 前置验证
     *
     * @param array|object $data 待验证的数据源
     * @param null         $entity unknown
     * @param Group        $messages 消费集合
     *
     * @return bool
     */
    public function beforeValidation($data, $entity, $messages)
    {
        return true;
    }

    /**
     * 读取需合并的默认值
     * @return array
     */
    public function getMergeDefault()
    {
        return $this->mergeData;
    }

    /**
     * 读取错误原因
     * @return string
     */
    public function getFailureMessage()
    {
        $attribute = null;
        $message = null;
        foreach ($this->stats as $key => $stat) {
            if ($stat['validators'] === $stat['failures']) {
                $attribute = $key;
                break;
            }
        }
        foreach ($this->getMessages() as $message) {
            if ($message->getField() === $attribute) {
                $message = $message->getMessage();
                break;
            }
        }
        return $message;
    }

    /**
     * 是否有错误
     * @return bool
     */
    public function hasFailure()
    {
        $has = false;
        foreach ($this->stats as $stat) {
            if ($stat['validators'] === $stat['failures']) {
                $has = true;
                break;
            }
        }
        return $has;
    }

    /**
     * 合并默认数据, 符合如下条件
     * 1. 指定的参数未传递
     * 2. 配置项中已为此字段指定了默认值
     *
     * @param string $key 字段名
     * @param mixed  $value 字段值
     */
    public function mergeDefault($key, $value)
    {
        $this->mergeData[$key] = $value;
    }

    /**
     * 验证过程
     *
     * @param array|object $data 待验证的数据源
     * @param null         $entity unknown
     *
     * @return Group
     */
    public function validate($data = null, $entity = null)
    {
        return parent::validate($data, $entity);
    }
}
