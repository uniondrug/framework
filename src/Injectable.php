<?php
/**
 * Injectable.php
 *
 */

namespace Uniondrug\Framework;

/**
 * Class Injectable
 *
 * @package Uniondrug\Framework
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
 */
abstract class Injectable extends \Phalcon\Di\Injectable
{

}
