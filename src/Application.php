<?php
/**
 * 应用入口
 */
namespace Pails;

abstract class Application extends \Phalcon\Mvc\Application
{
    public function boot()
    {
        // 注入应用级的服务
        $this->di->registerServices($this->di->getConfig()->path('app.providers', []));

        // 禁用视图
        $this->useImplicitView(false);

        return $this;
    }
}
