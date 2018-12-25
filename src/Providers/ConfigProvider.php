<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-25
 */
namespace Uniondrug\Framework\Providers;

use Phalcon\Config;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Text;
use Uniondrug\Framework\Container;

/**
 * 初始化系统配置
 * @package Uniondrug\Framework\Providers
 */
class ConfigProvider implements ServiceProviderInterface
{
    /**
     * @param \Phalcon\DiInterface $di
     */
    public function register(\Phalcon\DiInterface $di)
    {
        /**
         * @var Container $di
         */
        $file = $di->tmpPath().'/config.php';
        if (file_exists($file)) {
            $this->registerFromFilepath($di, $file);
        } else {
            $this->registerFromDirectory($di);
        }
    }

    /**
     * 从文件中加载配置
     * 优选使用Composer创建的配置文件, 本文件已从Consul中的参数
     * 提取关键字段, 并替换
     * @param Container $di
     * @param string    $file
     */
    private function registerFromFilepath($di, $file)
    {
        $data = include($file);
        $di->setShared('config', new Config($data));
    }

    /**
     * 从目录中遍历文件
     * 遍历指定目录下的全部文件, 将配置项合并加入配置清单
     * @param Container $di
     */
    private function registerFromDirectory($di)
    {
        $di->setShared('config', function(){
            $env = \app()->environment();
            $config = new Config([]);
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(\app()->configPath()), \RecursiveIteratorIterator::SELF_FIRST);
            foreach ($iterator as $item) {
                if (Text::endsWith($item, '.php', false)) {
                    $name = str_replace([
                        $this->configPath().DIRECTORY_SEPARATOR,
                        '.php'
                    ], '', $item);
                    $data = include $item;
                    // 默认配置组
                    if (is_array($data) && isset($data['default']) && is_array($data['default'])) {
                        $config[$name] = $data['default'];
                    }
                    // 非空初始化
                    if (!isset($config[$name])) {
                        $config[$name] = [];
                    }
                    // 指定环境的配置组，覆盖默认配置
                    if (is_array($data) && isset($data[$env]) && is_array($data[$env])) {
                        $config->merge(new Config([$name => $data[$env]]));
                    }
                }
            }
            return $config;
        });
    }
}
