<?php
/**
 * 微服务
 * @author wsfuyibing <websearch@163.com>
 * @date 2017-11-03
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
 */
abstract class ServiceServerController extends Controller
{
    /**
     * @var object
     */
    private $serviceJsonRawBody;

    /**
     * @return \stdClass
     * @throws \Exception
     */
    public function getJsonRawBody()
    {
        if ($this->serviceJsonRawBody === null) {
            try {
                $this->serviceJsonRawBody = $this->request->getJsonRawBody();
                if ($this->serviceJsonRawBody === null) {
                    throw new \Exception("无法解析JSON格式的RawBody参数");
                }
            } catch(\Exception $e) {
                throw new \Exception($e->getMessage());
            }
        }
        return $this->serviceJsonRawBody;
    }
}