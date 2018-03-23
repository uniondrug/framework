<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date 2018-03-23
 */
namespace Uniondrug\Framework;

use Phalcon\Di\Injectable as PhalconInjectable;
use Uniondrug\Framework\Services\ServiceTrait;

/**
 * @package Uniondrug\Framework
 */
abstract class Injectable extends PhalconInjectable
{
    use ServiceTrait;
}
