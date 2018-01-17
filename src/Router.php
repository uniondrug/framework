<?php
/**
 * 改写路由器，注解路由的重新实现。同时适配Fpm模式和Swoole模式，提高性能。
 *
 * @author XueronNi <xueronni@uniondrug.cn>
 * @date   2018-01-17
 */

namespace Pails;

use Phalcon\Annotations\Annotation;
use Phalcon\Mvc\Router as PhalconRouter;
use Phalcon\Mvc\Router\Exception;
use Phalcon\Text;

class Router extends PhalconRouter
{
    /**
     * 是否已经解析过
     *
     * @var bool
     */
    protected $_parsed = false;

    protected $_handlers = [];

    protected $_controllerSuffix = 'Controller';

    protected $_actionSuffix = 'Action';

    protected $_routePrefix;

    /**
     * Adds a resource to the annotations handler
     * A resource is a class that contains routing annotations
     *
     * @param      $handler
     * @param null $prefix
     *
     * @return $this
     */
    public function addResource($handler, $prefix = null)
    {
        $this->_handlers[] = [$prefix, $handler];

        return $this;
    }

    /**
     * Adds a resource to the annotations handler
     * A resource is a class that contains routing annotations
     * The class is located in a module
     *
     * @param      $module
     * @param      $handler
     * @param null $prefix
     *
     * @return $this
     */
    public function addModuleResource($module, $handler, $prefix = null)
    {
        $this->_handlers[] = [$prefix, $handler, $module];

        return $this;
    }

    /**
     * Changes the controller class suffix
     *
     * @param string $controllerSuffix
     */
    public function setControllerSuffix($controllerSuffix)
    {
        $this->_controllerSuffix = $controllerSuffix;
    }

    /**
     * Changes the action method suffix
     *
     * @param string $actionSuffix
     */
    public function setActionSuffix($actionSuffix)
    {
        $this->_actionSuffix = $actionSuffix;
    }

    /**
     * Return the registered resources
     *
     * @return array
     */
    public function getResources()
    {
        return $this->_handlers;
    }

    /**
     * Produce the routing parameters from the rewrite information
     *
     * @param null $uri
     *
     * @throws \Phalcon\Mvc\Router\Exception
     */
    public function handle($uri = null)
    {
        if (!$uri) {
            $realUri = $this->getRewriteUri();
        } else {
            $realUri = $uri;
        }

        if (!$this->_parsed) {
            $dependencyInjector = $this->_dependencyInjector;
            if (!is_object($dependencyInjector)) {
                throw new Exception("A dependency injection container is required to access the 'annotations' service");
            }
            $annotationsService = $dependencyInjector->getShared("annotations");
            $handlers = $this->_handlers;
            $controllerSuffix = $this->_controllerSuffix;
            foreach ($handlers as $scope) {
                if (!is_array($scope)) {
                    continue;
                }
// 这段代码在fpm下能提升性能，在swoole下，会导致无法使用，去掉不影响注解路由的功能
//                $prefix = $scope[0];
//                if (!empty($prefix) && !Text::startsWith($realUri, $prefix)) {
//                    continue;
//                }

                $handler = $scope[1];
                if (($pos = strrpos($handler, '\\')) !== false) {
                    $controllerName = substr($handler, $pos + 1);
                    $namespaceName = substr($handler, 0, $pos);
                } else {
                    $controllerName = $handler;
                    $namespaceName = $this->_defaultNamespace;
                }

                $this->_routePrefix = null;

                if (isset($scope[2])) {
                    $moduleName = $scope[2];
                } else {
                    $moduleName = null;
                }
                $sufixed = $controllerName . $controllerSuffix;
                if ($namespaceName) {
                    $sufixed = $namespaceName . '\\' . $sufixed;
                }

                $handlerAnnotations = $annotationsService->get($sufixed);
                if (!is_object($handlerAnnotations)) {
                    continue;
                }

                $classAnnotations = $handlerAnnotations->getClassAnnotations();
                if (is_object($classAnnotations)) {
                    $annotations = $classAnnotations->getAnnotations();
                    if (is_array($annotations)) {
                        foreach ($annotations as $annotation) {
                            $this->processControllerAnnotation($controllerName, $annotation);
                        }
                    }
                }

                $methodAnnotations = $handlerAnnotations->getMethodsAnnotations();
                if (is_array($methodAnnotations)) {
                    $lowerControllerName = Text::uncamelize($controllerName);

                    foreach ($methodAnnotations as $method => $collection) {
                        if (is_object($collection)) {
                            foreach ($collection->getAnnotations() as $annotation) {
                                $this->processActionAnnotation($moduleName, $namespaceName, $lowerControllerName, $method, $annotation);
                            }
                        }
                    }
                }
            }

            $this->_parsed = true;
        }

        parent::handle($realUri);
    }

    /**
     * Checks for annotations in the controller docblock
     *
     * @param                                 $handler
     * @param \Phalcon\Annotations\Annotation $annotation
     */
    public function processControllerAnnotation($handler, Annotation $annotation)
    {
        if ($annotation->getName() == 'RoutePrefix') {
            $this->_routePrefix = $annotation->getArgument(0);
        }
    }

    /**
     * Checks for annotations in the public methods of the controller
     *
     * @param                                 $module
     * @param                                 $namespaceName
     * @param                                 $controller
     * @param                                 $action
     * @param \Phalcon\Annotations\Annotation $annotation
     *
     * @return bool
     */
    public function processActionAnnotation($module, $namespaceName, $controller, $action, Annotation $annotation)
    {
        $isRoute = false;
        $methods = null;
        $name = $annotation->getName();

        switch ($name) {
            case 'Route':
                $isRoute = true;
                break;
            case 'Get':
                $isRoute = true;
                $methods = 'GET';
                break;
            case 'Post':
                $isRoute = true;
                $methods = 'POST';
                break;
            case 'Put':
                $isRoute = true;
                $methods = 'PUT';
                break;
            case 'Patch':
                $isRoute = true;
                $methods = 'PATCH';
                break;
            case 'Delete':
                $isRoute = true;
                $methods = 'DELETE';
                break;
            case 'Options':
                $isRoute = true;
                $methods = 'OPTIONS';
                break;
        }

        if ($isRoute === true) {
            $actionName = strtolower(str_replace($this->_actionSuffix, '', $action));
            $routePrefix = $this->_routePrefix;

            $paths = $annotation->getNamedArgument('paths');
            if (!is_array($paths)) {
                $paths = [];
            }

            if (!empty($module)) {
                $paths['module'] = $module;
            }

            if (!empty($namespaceName)) {
                $paths['namespace'] = $namespaceName;
            }

            $paths['controller'] = $controller;
            $paths['action'] = $actionName;

            $value = $annotation->getArgument(0);
            if (!empty($value)) {
                if ($value != '/') {
                    $uri = $routePrefix . $value;
                } else {
                    if (!empty($routePrefix)) {
                        $uri = $routePrefix;
                    } else {
                        $uri = $value;
                    }
                }
            } else {
                $uri = $routePrefix . $actionName;
            }

            $route = $this->add($uri, $paths);

            /**
             * add Methods
             */
            if ($methods !== null) {
                $route->via($methods);
            } else {
                $methods = $annotation->getNamedArgument('methods');
                if (is_array($methods) || is_string($methods)) {
                    $route->via($methods);
                }
            }

            /**
             * Add the conversors
             */
            $converts = $annotation->getNamedArgument("converts");
            if (is_array($converts)) {
                foreach ($converts as $param => $convert) {
                    $route->convert($param, $convert);
                }
            }

            $converts = $annotation->getNamedArgument('conversors');
            if (is_array($converts)) {
                foreach ($converts as $conversorParam => $convert) {
                    $route->convert($conversorParam, $convert);
                }
            }

            /**
             * Add the beforeMatch
             */
            $beforeMatch = $annotation->getNamedArgument("beforeMatch");
            if (is_array($beforeMatch) || is_string($beforeMatch)) {
                $route->beforeMatch($beforeMatch);
            }

            $routeName = $annotation->getNamedArgument("name");
            if (is_string($routeName)) {
                $route->setName($routeName);
            }

            return true;
        }
    }
}
