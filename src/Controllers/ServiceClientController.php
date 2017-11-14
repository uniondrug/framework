<?php
/**
 * 微服务
 * @author wsfuyibing <websearch@163.com>
 * @date 2017-11-03
 */

namespace Pails\Controllers;

use \Phalcon\Mvc\Controller;

/**
 * 微服务服务端基类控制器
 * @package Pails
 */
abstract class ServiceClientController extends Controller
{

    /**
     * @var \UniondrugServiceClient\Request
     */
    protected $serviceClient;

    /**
     * 构造
     * 1. 微服务客户端对象
     */
    public function onConstruct()
    {
        $this->serviceClient = new \UniondrugServiceClient\Request();
    }
}