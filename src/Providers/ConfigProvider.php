<?php
/**
 * ConfigProvider.php
 *
 */

namespace Uniondrug\Framework\Providers;

use Phalcon\Config;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Text;

class ConfigProvider implements ServiceProviderInterface
{
    public function register(\Phalcon\DiInterface $di)
    {
        $di->setShared(
            'config',
            function () {
                $env = $this->environment();
                $config = new Config([]);
                $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->configPath()), \RecursiveIteratorIterator::SELF_FIRST);
                foreach ($iterator as $item) {
                    if (Text::endsWith($item, '.php', false)) {
                        $name = str_replace([$this->configPath() . DIRECTORY_SEPARATOR, '.php'], '', $item);
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
            }
        );
    }
}
