<?php
/**
 * 微服务
 * @author wsfuyibing <websearch@163.com>
 * @date 2017-11-03
 */
namespace Pails\Controllers;

use Pails\Helpers\SessionClient;
use Phalcon\Mvc\Controller;
use UniondrugServiceClient\Client;

/**
 * 微服务服务端基类控制器
 * @package Pails
 */
abstract class ServiceClientController extends Controller
{
    /**
     * @var Client
     */
    public $serviceClient;
    /**
     * @var SessionClient
     */
    public $sessionClient;

    /**
     * 构造
     * 1. 微服务客户端对象
     * 2. 初始化Session客户端服务
     */
    public function onConstruct()
    {
        $this->serviceClient = new Client();
        $this->sessionClient = new SessionClient();
    }
}