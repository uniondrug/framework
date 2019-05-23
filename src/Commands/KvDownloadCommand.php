<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2019-05-23
 */
namespace Uniondrug\Framework\Commands;

/**
 * 下载KV
 * 从Consul服务器下载KV配置, 并合到本地的配置文件中
 * @package Uniondrug\Framework\Commands
 */
class KvDownloadCommand extends Abstracts\KvCommand
{
    /**
     * @var string
     */
    protected $signature = 'kv:download
        {--name= : Key名称, 默为本地配置文件中的`app.appName`参数值}
        {--consul=127.0.0.1:8500 : Consul服务器地址}';
    /**
     * @var string
     */
    protected $description = '从`Consul`服务器的`KV`中下载配置参数并合到`tmp/config.php`文件中';

    /**
     * @return mixed
     */
    public function handle()
    {
        $this->info("[INFO] 从{Consul}的{KV}中下载{config}参数.");
        try {
            // 1. access
            $this->accessAble();
            // 2. local scanner
            $scan = $this->scanner();
            // 3. remote
            // 3.1. key name
            $key = $this->getConsulAppKey($this->getKeyName());
            $this->info("[INFO] 名称为{{$key}}的KEY.");
            // 3.2. host & key
            $host = $this->getConsulHost();
            $json = $this->readKeyString($host, $key);
            if ($json === false) {
                throw new \RuntimeException("从{Consul}服务的`KV`未读取到有效的顶级配置");
            }
            // 3.4. json validator
            $data = json_decode($json, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException("{Consul}服务`KV`的顶级参数不是有效的{JSON}数据");
            }
            // 3.5. nest data
            $this->nestKeyData($data, $host);
            // 3.6. response
            $this->info("[INFO] 合并到{{$this->di->environment()}}环境");
            // 3.6. merge
            $this->mergeToConfig($scan, $data);
            $this->info("[INFO] 下载KV完成");
        } catch(\Throwable $e) {
            $this->error("[ERROR] {$e->getMessage()}");
        }
    }
}
