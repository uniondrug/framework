<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date 2018-03-21
 */
namespace Uniondrug\Framework\Logics;

use Uniondrug\Structs\StructInterface;

/**
 * @package Uniondrug\Framework\Logics
 */
interface LogicInterface
{
    /**
     * @param array|null|object $payload 入参
     *
     * @return array|StructInterface
     */
    public function run($payload);
}
