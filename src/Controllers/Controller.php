<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-03-23
 */
namespace Uniondrug\Framework\Controllers;

use Phalcon\Mvc\Controller as PhalconController;
use Uniondrug\Framework\Request;
use Uniondrug\Framework\Services\ServiceTrait;

/**
 * @property Request $request
 * @package Uniondrug\Framework\Controllers
 */
abstract class Controller extends PhalconController
{
    use ServiceTrait;
}
