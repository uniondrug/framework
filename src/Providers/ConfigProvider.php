<?php
/**
 * ConfigProvider.php
 *
 */

namespace Pails\Providers;

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
                $config = [];
                $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->configPath()), \RecursiveIteratorIterator::SELF_FIRST);
                foreach ($iterator as $item) {
                    if (Text::endsWith($item, '.php', false)) {
                        $name = str_replace([$this->configPath() . DIRECTORY_SEPARATOR, '.php'], '', $item);
                        $data = include $item;
                        if (is_array($data) && isset($data[$env])) {
                            $config[$name] = $data[$env];
                        }
                    }
                }
                return new Config($config);
            }
        );
    }
}
