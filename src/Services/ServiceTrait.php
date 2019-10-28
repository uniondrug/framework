<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-03-23
 */

namespace Uniondrug\Framework\Services;

/**
 * @property \GuzzleHttp\Client                      $httpClient
 * @property \Phalcon\Cache\BackendInterface         $cache
 * @property \Phalcon\Config                         $config
 * @property \Phalcon\Db\AdapterInterface            $dbSlave
 * @property \Phalcon\Logger\AdapterInterface        $logger
 * @property \Redis                                  $redis
 * @property \Uniondrug\Redis\RedisLock              $redisLock
 * @property \Uniondrug\Framework\Container          $di
 * @property \Uniondrug\Crontab\Crontab              $crontabService
 * @property \Uniondrug\Middleware\MiddlewareManager $middlewareManager
 * @property \Uniondrug\Register\RegisterClient      $registerClient
 * @property \Uniondrug\Service\Server               $serviceServer
 * @property \Uniondrug\Service\Client               $serviceClient
 * @property \Uniondrug\TcpClient\Client             $tcpClient
 * @property \Uniondrug\Trace\TraceClient            $traceClient
 * @property \Uniondrug\Server\Task\Dispatcher       $taskDispatcher
 * @property \Uniondrug\ServiceSdk\ServiceSdk        $serviceSdk
 * @property \Uniondrug\Validation\Param             $validationService
 */
trait ServiceTrait
{
}
