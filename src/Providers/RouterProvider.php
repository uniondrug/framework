<?php
/**
 * RouterProvider.php
 *
 */

namespace Pails\Providers;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Mvc\Router;
use Phalcon\Text;

class RouterProvider implements ServiceProviderInterface
{
    public function register(\Phalcon\DiInterface $di)
    {
        $di->setShared(
            'router',
            function () {
                if ($this->getConfig()->path('app.userAnnotationRouter', false)) {
                    // 启用注解路由，此时默认路由关闭
                    $router = new \Pails\Router(false);
                    $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->appPath() . '/Controllers'), \RecursiveIteratorIterator::SELF_FIRST);
                    foreach ($iterator as $item) {
                        if (Text::endsWith($item, 'Controller.php', false)) {
                            $name = str_replace([$this->appPath() . '/Controllers' . DIRECTORY_SEPARATOR, 'Controller.php'], '', $item);
                            $name = str_replace(DIRECTORY_SEPARATOR, '\\', $name);
                            $router->addResource('App\\Controllers\\' . $name);
                        }
                    }
                } else {
                    // 使用默认路由
                    $router = new Router();
                }
                $router->removeExtraSlashes(true);
                $router->setDefaultNamespace('App\\Controllers');
                $router->setDefaultController('index');
                $router->setDefaultAction('index');

                /**
                 * 载入自定义路由
                 */
                if ($routes = $this->getConfig()->get('routes')) {
                    foreach ($routes as $pattern => $route) {
                        if (is_string($route)) {
                            $router->add($pattern, $route);
                        } else {
                            $router->add($pattern, $route['path'], $route['methods']->toArray() ?: null);
                        }
                    }
                }

                return $router;
            }
        );
    }
}
