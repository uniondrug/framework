<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2019-10-28
 */
namespace Uniondrug\Framework\Providers;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;
use Uniondrug\Framework\Trace;

/**
 * Trace请求链
 * @package Uniondrug\Framework\Providers
 */
class TraceProvider implements ServiceProviderInterface
{
    /**
     * @param DiInterface $di
     */
    public function register(DiInterface $di)
    {
        $di->setShared("trace", function(){
            return new Trace();
        });
    }
}
