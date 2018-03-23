<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-03-23
 */
namespace Uniondrug\Framework\Controllers;

use Phalcon\Mvc\Controller as PhalconController;
use Uniondrug\Framework\Services\ServiceTrait;

/**
 * @package Uniondrug\Framework\Controllers
 */
abstract class Controller extends PhalconController
{
    use ServiceTrait;
}
