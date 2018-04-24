<?php
/**
 * ServiceProvider.php
 *
 * 继承实现Phalcon的Provider，自动注册APP的Services目录下的服务。
 */

namespace Uniondrug\Framework\Services;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Text;
use Uniondrug\Framework\Container;

abstract class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @param \Phalcon\DiInterface $di
     */
    public function register(\Phalcon\DiInterface $di)
    {
        $this->registerService($di);

        $this->bootstrap($di);
    }

    /**
     * @param \Phalcon\DiInterface|Container $di
     *
     * @return mixed
     */
    abstract function bootstrap(\Phalcon\DiInterface $di);

    /**
     * 自动注册Services目录下的服务
     *
     * @param \Phalcon\DiInterface|Container $di
     */
    public function registerService(\Phalcon\DiInterface $di)
    {
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($di->appPath() . DIRECTORY_SEPARATOR . 'Services'),
            \RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $item) {
            if (Text::endsWith($item, 'Service.php', false)) {
                $name = str_replace([$di->appPath() . DIRECTORY_SEPARATOR . 'Services' . DIRECTORY_SEPARATOR, '.php'], '', $item);
                if ($name) {
                    $name = str_replace(DIRECTORY_SEPARATOR, '\\', $name);
                    $serviceBaseName = basename($item, '.php');
                    $serviceClassName = 'App\\Services\\' . $name;
                    $di->setShared(lcfirst($serviceBaseName), $serviceClassName);
                }
            }
        }
    }
}
