<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-03-23
 */

namespace Uniondrug\Framework\Services;

/**
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
 * @property \Phalcon\Db\AdapterInterface            $dbSlave
 * @property \Uniondrug\Crontab\Crontab              $crontabService
 * @property \Uniondrug\TcpClient\Client             $tcpClient
 * @property \Redis                                  $redis
 * @property \Uniondrug\ServiceSdk\ServiceSdk        $serviceSdk
 */
trait ServiceTrait
{
}
