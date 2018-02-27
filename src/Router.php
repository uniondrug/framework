<?php
/**
 * 改写路由器，注解路由的重新实现。同时适配Fpm模式和Swoole模式，提高性能。
 *
 * @author XueronNi <xueronni@uniondrug.cn>
 * @date   2018-01-17
 */

namespace Uniondrug\Framework;

use Phalcon\Mvc\Router\Annotations;

class Router extends Annotations
{
    /**
     * 是否已经解析过
     *
     * @var bool
     */
    protected $_parsed = false;

    /**
     * 重置Parsed属性
     * @param bool $parsed
     */
    public function setParsed($parsed = false)
    {
        $this->_parsed = $parsed;
    }

    /**
     * 彻底禁止prefix功能
     *
     * @inheritdoc
     */
    public function addResource($handler, $prefix = null)
    {
        $this->_handlers[] = [null, $handler];

        return $this;
    }

    /**
     * 彻底禁止prefix功能
     *
     * @inheritdoc
     */
    public function addModuleResource($module, $handler, $prefix = null)
    {
        $this->_handlers[] = [null, $handler, $module];

        return $this;
    }

    /**
     * 增加一个注解解析状态。如果已经解析，则不用再次解析了。直接调用祖父类的处理方法。
     *
     * @inheritdoc
     */
    public function handle($uri = null)
    {
        if (!$this->_parsed) {
            $this->_parsed = true;
            parent::handle($uri);
        } else {
            \Phalcon\Mvc\Router::handle($uri);
        }
    }
}
