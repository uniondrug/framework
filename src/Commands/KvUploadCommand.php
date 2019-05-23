<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2019-05-23
 */
namespace Uniondrug\Framework\Commands;

/**
 * 上传KV
 * 扫描本地config目录下的文件以初始化成JSON字符串, 并上传
 * 到Consul服务器
 * @package Uniondrug\Framework\Commands
 */
class KvUploadCommand extends Abstracts\KvCommand
{
    /**
     * @var string
     */
    protected $signature = 'kv:upload
        {--name= : Key名称, 默为本地配置文件中的`app.appName`参数值}
        {--consul=127.0.0.1:8500 : Consul服务器地址}
        {--override=NO : 上否覆盖已有的KV配置}';
    /**
     * @var string
     */
    protected $description = '扫描`config`目录下的文件并组织成`JSON`字符串, 上传到`Consul`服务器的`KV`中';

    /**
     * @return mixed
     */
    public function handle()
    {
        $this->info("[INFO] 上传{config}参数到{Consul}的{KV}中");
        try {
            // 1. access
            $this->accessAble();
            // 2. local scanner
            $scan = $this->scanner();
            $this->scannerDefault($scan);
            // 3. remote
            // 3.1. host & key
            $host = $this->getConsulHost();
            $key = $this->getConsulAppKey($this->getKeyName());
            // 3.3, 写入数据
            $this->writeKeyString($host, $key, $scan);
            $this->info("[INFO] 上传KV完成");
        } catch(\Throwable $e) {
            $this->error("[ERROR] {$e->getMessage()}");
        }
    }
}
