<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2019-05-23
 */
namespace Uniondrug\Framework\Commands\Abstracts;

use Uniondrug\Console\Command;

/**
 * 操作ConsulKV
 * @package Uniondrug\Framework\Commands\Abstracts
 */
abstract class KvCommand extends Command
{
    /**
     * 可执行权限
     */
    protected function accessAble()
    {
        if (defined("PHAR_WORKING_FILE")) {
            throw new \Exception("不支持PHAR部署项目");
        }
    }

    /**
     * @param string $host
     * @param string $key
     * @return string
     */
    protected function getConsulApi(string $host, string $key)
    {
        return sprintf("http://%s/v1/kv/%s", $host, $key);
    }

    /**
     * 读取顶级Key
     * @param string $key
     * @return string
     */
    protected function getConsulAppKey(string $key)
    {
        return "apps/{$key}/config";
    }

    /**
     * 服务地址
     * @return string
     */
    protected function getConsulHost()
    {
        $host = (string) $this->input->getOption('consul');
        $host === '' && $host = '127.0.0.1:8500';
        return $host;
    }

    /**
     * 项目名称
     * @return string
     */
    protected function getKeyName()
    {
        $name = (string) $this->input->getOption('name');
        $name === '' && $name = (string) $this->config->path('app.appName');
        if ($name === '') {
            throw new \RuntimeException("用于`KEY`的应用名称未找到");
        }
        return $name;
    }

    /**
     * @param array $scan 本地扫描到的结构
     * @param array $data ConsulKV存储的结构
     */
    protected function mergeToConfig($scan, $data)
    {
        $data = array_replace_recursive($scan, $data);
        $this->sortKeys($data);
        $text = "<?php\n// merged: ".date('r')."\nreturn unserialize('".serialize($data)."');\n";
        $this->output->writeln("       写入{".$this->di->tmpPath().'/config.json'."}文件");
        file_put_contents($this->di->tmpPath().'/config.json', json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        $this->output->writeln("       写入{".$this->di->tmpPath().'/config.php'."}文件");
        file_put_contents($this->di->tmpPath().'/config.php', $text);
    }

    /**
     * @param array  $data
     * @param string $host
     */
    protected function nestKeyData(& $data, string $host)
    {
        // 1. not array
        if (!is_array($data)) {
            return;
        }
        // 2. array looper
        foreach ($data as & $value) {
            // 3. nest depth
            if (is_array($value)) {
                $this->nestKeyData($value, $host);
                continue;
            }
            // 4. not string
            if (!is_string($value)) {
                continue;
            }
            // 5. string checker
            if (preg_match("/^kv:\/\/(\S+)$/i", $value, $m) > 0) {
                // 5.1 nest key
                $nest = $this->readKeyString($host, $m[1]);
                // 5.2 error nested
                if ($nest === false) {
                    $value = "";
                    continue;
                }
                // 5.3 array nested
                $nestData = json_decode($nest, true);
                if (is_array($nestData)) {
                    $this->nestKeyData($nestData, $host);
                    $value = $nestData;
                    continue;
                }
                // 5.4
                $value = $this->nestKeyString($this, $host, $nest);
                continue;
            }
            // 6. boolean
            $lower = strtolower($value);
            if ($lower === "true") {
                $value = true;
            } else if ($lower === "false") {
                $value = false;
            } else {
                $value = $this->nestKeyString($this, $host, $value);
            }
        }
    }

    /**
     * @param KvCommand $cmd
     * @param string    $host
     * @param string    $text
     * @return string
     */
    protected function nestKeyString($cmd, $host, $text)
    {
        return preg_replace_callback("/kv:\/\/([_a-z0-9\-\/]+)/i", function($a) use ($cmd, $host){
            $nest = $cmd->readKeyString($host, $a[1]);
            if ($nest === false) {
                return "";
            }
            return $nest;
        }, $text);
    }

    /**
     * @param string $host
     * @param string $key
     * @return bool|string
     */
    protected function readKeyString(string $host, string $key)
    {
        $this->output->writeln("       读取{{$key}}配置");
        try {
            $url = $this->getConsulApi($host, $key);
            $text = $this->httpClient->get($url)->getBody()->getContents();
            $data = json_decode($text, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($data) && count($data) > 0) {
                throw new \RuntimeException("解析{JSON}发生{".json_last_error_msg()."}错误");
            }
            return trim(base64_decode($data[0]['Value']));
        } catch(\Throwable $e) {
            $this->error("[ERROR] 请求{Consul}返回{{$e->getCode()}}错误 - {$e->getMessage()}");
        }
        return false;
    }

    /**
     * 扫描本地配置
     * @return array
     * @throws \RuntimeException
     */
    protected function scanner()
    {
        $path = $this->di->configPath();
        if (!is_dir($path)) {
            throw new \RuntimeException("路径{{$path}}不是有效的配置文件存放目录");
        }
        $this->info("[INFO] 扫描本地{config}目录下的{php}文件");
        $env = $this->di->environment();
        $data = [];
        $d = dir($path);
        while (false !== ($e = $d->read())) {
            if (preg_match("/^([a-z][_a-z0-9]*)\.php$/", $e, $m) > 0) {
                $this->output->writeln("       发现{{$e}}文件");
                $temp = include($path.'/'.$e);
                if (is_array($temp)) {
                    $def = isset($temp['default']) && is_array($temp['default']) ? $temp['default'] : [];
                    $conf = isset($temp[$env]) && is_array($temp[$env]) ? $temp[$env] : [];
                    $conf = array_replace_recursive($def, $conf);
                    $data[$m[1]] = [
                        'key' => $env,
                        'value' => $conf
                    ];
                }
            }
        }
        $d->close();
        return $data;
    }

    /**
     * @param array $scan
     */
    protected function scannerDefault(& $scan)
    {
        // 1, remove env key
        foreach ($scan as & $data) {
            if (!is_array($data)) {
                continue;
            }
            if (isset($data['key'])) {
                unset($data['key']);
            }
        }
        // 2, append server
        if (isset($scan['server'], $scan['server']['value'])) {
            // 2.1, server logger
            if (!isset($scan['server']['value']['logger'])) {
                $scan['server']['value']['logger'] = 'kv://globals/log/default';
            }
            // 2.2, server settings
            if (!isset($scan['server']['value']['settings'])) {
                $scan['server']['value']['settings'] = 'kv://globals/swoole/worker';
            }
        }
        // 3, append sdk
        if (!isset($scan['sdk'])) {
            $scan['sdk'] = [
                'value' => 'kv://globals/sdk/v2'
            ];
        }
        // 4, unset keys
        foreach ([
            'app',
            'logger',
            'middleware',
            'routes',
            'trace'
        ] as $name) {
            if (isset($scan[$name])) {
                unset($scan[$name]);
            }
        }
        // 5, sort able
        $this->sortKeys($scan);
    }

    /**
     * 键名排序
     * @param array $data
     */
    protected function sortKeys(& $data)
    {
        ksort($data);
        reset($data);
        foreach ($data as & $temp) {
            if (is_array($temp)) {
                $this->sortKeys($temp);
            }
        }
    }

    /**
     * @param string $host
     * @param string $key
     * @return bool|string
     */
    protected function writeKeyString(string $host, string $key, array $data)
    {
        $this->info("[INFO] 上传到{{$key}}配置");
        $override = strtolower((string) $this->input->getOption('override')) === 'yes';
        try {
            // 1. generate URL
            $url = $this->getConsulApi($host, $key).($override ? '' : '?cas=0');
            $this->httpClient->put($url, [
                'body' => json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            ])->getBody()->getContents();
            return true;
        } catch(\Throwable $e) {
            $this->error("[ERROR] 请求{Consul}返回{{$e->getCode()}}错误 - {$e->getMessage()}");
        }
        return false;
    }
}
