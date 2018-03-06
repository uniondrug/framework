<?php
/**
 * 微服务
 *
 * @author wsfuyibing <websearch@163.com>
 * @date   2017-11-03
 */

namespace Uniondrug\Framework\Controllers;

use Phalcon\Mvc\Controller;

/**
 * 微服务服务端基类控制器
 *
 * @property \Uniondrug\Service\Server               $serviceServer
 * @property \Uniondrug\Service\Client               $serviceClient
 * @property \GuzzleHttp\ClientInterface             $httpClient
 * @property \Uniondrug\Register\RegisterClient      $registerClient
 * @property \Uniondrug\Middleware\MiddlewareManager $middlewareManager
 * @property \Phalcon\Cache\BackendInterface         $cache
 * @property \Phalcon\Logger\AdapterInterface        $logger
 * @property \Uniondrug\Validation\Param             $validationService
 * @property \Phalcon\Config                         $config
 * @property \Uniondrug\Server\Task\Dispatcher       $taskDispatcher
 */
abstract class ServiceServerController extends Controller
{
}