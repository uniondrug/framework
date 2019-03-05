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
         * 1. Consul模式
         * @var Container $di
         */
        if ($this->registerWithTmp($di)) {
            return;
        }
        // 3. Scan模式
        $this->registerWithScan($di);
    }

    /**
     * TMP模式
     * 在执行composer update时, 从ConsulKV中拉取
     * 配置参数, 并写入tmp/config.php中
     * @param Container $di
     * @return bool
     */
    private function registerWithTmp($di)
    {
        $file = $di->tmpPath().'/config.php';
        if (file_exists($file)) {
            $data = include($file);
            if (is_array($data)) {
                $di->setShared('config', new Config($this->parseKvConfigurations($data)));
                return true;
            }
        }
        return false;
    }

    /**
     * Scan模式
     * 扫描项目所在的config目录, 从该目录下的php文件中
     * 按环境变量参数读取配置
     * @param Container $di
     * @return bool
     */
    private function registerWithScan($di)
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
        return true;
    }

    /**
     * @param $data
     * @return array
     */
    private function parseKvConfigurations(& $data)
    {
        $resp = [];
        foreach ($data as $key => $value) {
            if (is_array($value) && isset($value['key'], $value['value'])) {
                $resp[$key] = $value['value'];
            } else {
                $resp[$key] = $value;
            }
        }
        return $resp;
    }
}
