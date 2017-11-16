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
abstract class ServiceServerController extends Controller
{

    /**
     * @var \UniondrugServiceServer\Response
     */
    public $serviceServer;

    /**
     * 构造
     * 1. 微服务的服务端对象
     */
    public function onConstruct()
    {
        $this->serviceServer = new \UniondrugServiceServer\Response();
    }

}