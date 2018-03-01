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
 * @property \GuzzleHttp\ClientInterface             $httpClient
 * @property \Phalcon\Cache\BackendInterface         $cache
 * @property \Phalcon\Config                         $config
 * @property \Phalcon\Logger\AdapterInterface        $logger
 * @property \Uniondrug\Service\Server               $serviceServer
 * @property \Uniondrug\Service\Client               $serviceClient
 * @property \Uniondrug\Register\RegisterClient      $registerClient
 * @property \Uniondrug\Middleware\MiddlewareManager $middlewareManager
 * @property \Uniondrug\Validation\Param             $validationService
 */
abstract class Injectable extends \Phalcon\Di\Injectable
{

}
