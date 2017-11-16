<?php
/**
 * Uniondrug Api Framework
 * 容器
 * @author Unindrug
 */

namespace Pails;

use Pails\Providers\ConfigProvider;
use Pails\Providers\DatabaseProvider;
use Pails\Providers\LoggerProvider;
use Pails\Providers\RouterProvider;
use Phalcon\Di;
use Phalcon\Di\Service;
use Phalcon\Http\Response;
use Phalcon\Text;

/**
 * Class Container
 * @package Pails
 */
final class Container extends Di
{

    /**
     * 版本号
     */
    const VERSION = '1.0.0';

    /**
     * 当前环境
     * @var null
     */
    private $env = null;

    /**
     * 应用路径
     * @var
     */
    protected $baseDir;

    /**
     * 系统服务
     * @var array
     */
    protected $_providers = [
        RouterProvider::class,
        ConfigProvider::class,
        DatabaseProvider::class,
        LoggerProvider::class,
    ];

    /**
     * Container constructor.
     *
     * @param null $baseDir
     */
    public function __construct($baseDir = null)
    {
        // 初始化调试器
        $debug = new \Phalcon\Debug();
        $debug->listen(true, true);

        // 初始化容器
        parent::__construct();

        // 设置主目录
        $this->setBaseDir($baseDir);

        // 设置默认的服务
        $this->_services = [
            "dispatcher" => new Service("dispatcher", "Phalcon\\Mvc\\Dispatcher", true),
            "url" => new Service("url", "Phalcon\\Mvc\\Url", true),
            "modelsManager" => new Service("modelsManager", "Phalcon\\Mvc\\Model\\Manager", true),
            "modelsMetadata" => new Service("modelsMetadata", "Phalcon\\Mvc\\Model\\MetaData\\Memory", true),
            "response" => new Service("response", "Phalcon\\Http\\Response", true),
            "request" => new Service("request", "Phalcon\\Http\\Request", true),
            "filter" => new Service("filter", "Phalcon\\Filter", true),
            "escaper" => new Service("escaper", "Phalcon\\Escaper", true),
            "security" => new Service("security", "Phalcon\\Security", true),
            "crypt" => new Service("crypt", "Phalcon\\Crypt", true),
            "eventsManager" => new Service("eventsManager", "Phalcon\\Events\\Manager", true),
            "transactionManager" => new Service("transactionManager", "Phalcon\\Mvc\\Model\\Transaction\\Manager", true),
        ];

        // 设置容器的事件管理器为全局管理器。
        if (!$this->getInternalEventsManager() && ($eventsManager = $this->getEventsManager())) {
            $this->setInternalEventsManager($eventsManager);
        }

        // 注入Pails定义的服务
        $this->registerServices($this->_providers);
    }

    /**
     * @return string
     */
    public function environment()
    {
        // 1. 优先用属性配置
        if ($this->env) {
            return $this->env;
        }

        // 2. 读环境变量
        $default = 'development';
        $value = getenv('APP_ENV');
        if ($value && Text::startsWith($value, '"') && Text::endsWith($value, '"')) {
            $value = substr($value, 1, -1);
        }

        // 3. 使用默认
        $value || $value = $default;

        // 4. 同步属性并返回
        $this->env = strtolower($value);
        return $this->env;
    }

    /**
     * 是否为开发环境
     * @return bool
     */
    public function isDevelopment()
    {
        return $this->env === "development";
    }

    /**
     * 是否为生产环境
     * @return bool
     */
    public function isProduction()
    {
        return $this->env === "production";
    }

    /**
     * 是否为测试环境
     * @return bool
     */
    public function isTesting()
    {
        return $this->env === "testing";
    }

    /**
     * 注册服务列表
     *
     * @param array $serviceProviders Name of service providers which implements ServiceProviderInterface
     */
    public function registerServices($serviceProviders = [])
    {
        foreach ($serviceProviders as $serviceProviderClass) {
            $this->register(new $serviceProviderClass());
        }
    }

    /**
     * @param $applicationClassName
     *
     * @throws \Exception
     */
    public function run($applicationClassName)
    {
        try {
            $application = $this->getShared($applicationClassName);
            $return = $application->boot()->handle();
            if ($return instanceof Response) {
                $return->send();
            } else {
                $this->getShared('response')->setJsonContent([])->send();
            }
        } catch(\Exception $e) {
            $this->getLogger('error')->error("System Error: ".$e->getMessage().". \nTrace: \n".$e->getTraceAsString());

            if ($this->getConfig()->path('app.debug', false)) {
                /**
                 * 开启Debug模式，交给PhalconDebugger捕获
                 */
                throw $e;
            } else {
                /**
                 * 非Debug模式，直接输出一个Json
                 */
                $this->getShared('response')->setJsonContent([
                    'errno' => "-1",
                    'error' => "System Error: ".$e->getMessage(),
                ])->send();
            }
        }
    }

    /**
     * @param $baseDir
     *
     * @return $this
     */
    public function setBaseDir($baseDir)
    {
        if (!file_exists($baseDir) || !is_dir($baseDir)) {
            throw new \RuntimeException('Invalid baseDir: \"'.$baseDir.'\" not exists or is not a dir.');
        }
        $this->baseDir = rtrim($baseDir, '\/');

        return $this;
    }

    /**
     * Helpers: Get the path to the application directory.
     * @return string
     */
    public function appPath()
    {
        return $this->baseDir.DIRECTORY_SEPARATOR.'app';
    }

    /**
     * Helpers: Get the path to the application configuration files.
     * @return string
     */
    public function configPath()
    {
        return $this->baseDir.DIRECTORY_SEPARATOR.'config';
    }

    /**
     * Helpers: Get the path to the log directory.
     * @return string
     */
    public function logPath()
    {
        return $this->baseDir.DIRECTORY_SEPARATOR.'log';
    }

    /**
     * Helpers: Get the path to the tmp directory.
     * @return string
     */
    public function tmpPath()
    {
        return $this->baseDir.DIRECTORY_SEPARATOR.'tmp';
    }
}
