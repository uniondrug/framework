<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date 2018-03-21
 */
namespace Uniondrug\Framework\Logics;

use Uniondrug\Framework\Injectable;
use Uniondrug\Structs\StructInterface;

/**
 * 业务逻辑抽像
 * @package Uniondrug\Framework\Logics
 */
abstract class Logic extends Injectable implements LogicInterface
{
    /**
     * 逻辑工厂
     *
     * @param array|null|object $payload 入参
     *
     * @return array|StructInterface 逻辑执行结果
     */
    public static function factory($payload = null)
    {
        $logic = new static();
        return $logic->run($payload);
    }
}
