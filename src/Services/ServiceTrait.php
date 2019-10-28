<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-03-23
 */
namespace Uniondrug\Framework\Services;

/**
 * @property \Uniondrug\HttpClient\Client            $httpClient
 * @property \Phalcon\Cache\BackendInterface         $cache
 * @property \Phalcon\Config                         $config
 * @property \Phalcon\Db\AdapterInterface            $dbSlave
 * @property \Phalcon\Logger\AdapterInterface        $logger
 * @property \Redis                                  $redis
 * @property \Uniondrug\Redis\RedisLock              $redisLock
 * @property \Uniondrug\Framework\Container          $di
 * @property \Uniondrug\Framework\Trace              $trace
 * @property \Uniondrug\Middleware\MiddlewareManager $middlewareManager
 * @property \Uniondrug\Service\Server               $serviceServer
 * @property \Uniondrug\Service\Client               $serviceClient
 * @property \Uniondrug\ServiceSdk\ServiceSdk        $serviceSdk
 * @property \Uniondrug\Validation\Param             $validationService
 */
trait ServiceTrait
{
}
