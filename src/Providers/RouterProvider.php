<?php
/**
 * RouterProvider.php
 *
 */

namespace Pails\Providers;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Mvc\Router;

class RouterProvider implements ServiceProviderInterface
{
    public function register(\Phalcon\DiInterface $di)
    {
        $di->setShared(
            'router',
            function () {
                $router = new Router();
                $router->removeExtraSlashes(true);
                $router->setDefaultNamespace('App\\Controllers');
                $router->setDefaultController('index');
                $router->setDefaultAction('index');

                /*
                 * 载入自定义路由
                 */
                if ($routes = $this->getConfig()->get('routes')) {
                    $this->getLogger()->info(json_encode($routes));
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
