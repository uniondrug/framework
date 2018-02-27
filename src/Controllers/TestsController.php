<?php
/**
 * 单元测试
 * @author wsfuyibing <websearch@163.com>
 * @date 2017-11-03
 */

namespace Pails\Controllers;

use Pails\Container;
use Phalcon\Mvc\Controller;

/**
 * 如何创建单元测试
 * <code>
 * // 在项目下创建
 * // app/Controllers/TestsController.php
 * // http://app.uniondrug.cn/tests/route/action
 * class TestsController extends \Pails\Controllers\TestsController {
 *     // 无需编写任何的代码
 * }
 * </code>
 * @property Container $di
 * @package Pails
 */
abstract class TestsController extends Controller
{

    private $testsController = null;
    private $testsAction = null;

    /**
     * 构造(重设action)
     */
    final public function onConstruct()
    {
        $this->dispatcher->setActionName("index");
        if ($this->di->isProduction()) {
            throw new \Exception("单元测试不对生产环境开放");
        }
    }

    /**
     * 转向单元测试
     * @throws \Exception
     */
    final public function indexAction()
    {
        $this->parseTestsRequest();
        $dispatch = clone ($this->dispatcher);
        $dispatch->setControllerName($this->testsController);
        $dispatch->setActionName($this->testsAction);
        $dispatch->dispatch();
        return $dispatch->getReturnedValue();
    }

    /**
     * 解析单元测试请求
     */
    private function parseTestsRequest()
    {
        // 1. 默认测试单元
        $action = 'index';
        $controller = 'index';

        // 2. 解析真实测试单元
        $url = preg_replace("/^\/tests\/*/i", "", $this->request->getURI());
        $len = preg_match_all("/([_a-zA-Z0-9]+)/", $url, $match);
        if ($len > 0) {
            $controller = $match[1][0];
            if ($len > 1) {
                $action = $match[1][1];
            }
        }

        // 4. 设置测试单元
        $this->testsAction = &$action;
        $this->testsController = "Tests\\".ucfirst($controller);

        // 5. 导入测试单元
        //    与autoload冲突, 使用include()方法替代
        $controllerClass = "\\App\\Controllers\\Tests\\".ucfirst($controller)."Controller";
        if (!class_exists($controllerClass, false)) {
            $controllerFile = $this->di->appPath()."/Controllers/Tests/".ucfirst($controller)."Controller.php";
            if (file_exists($controllerFile)) {
                include($controllerFile);
            }
        }
    }
}