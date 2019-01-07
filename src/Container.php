<?php
/**
 * Uniondrug Api Framework
 * 容器
 * @author Unindrug
 */
namespace Uniondrug\Framework;

use Phalcon\Di;
use Phalcon\Di\Service;
use Phalcon\Text;
use Uniondrug\Framework\Providers\ConfigProvider;
use Uniondrug\Framework\Providers\DatabaseProvider;
use Uniondrug\Framework\Providers\LoggerProvider;
use Uniondrug\Framework\Providers\RouterProvider;

/**
 * 以下是可以通过 __call() 魔术方法调用的服务（注：依赖其他组件的，需要先引入组件）
 * @method \Phalcon\Annotations\AdapterInterface getAnnotation()
 * @method \Phalcon\Mvc\DispatcherInterface getDispatcher()
 * @method \Phalcon\Events\Manager getEventsManager()
 * @method \Phalcon\Mvc\Url|\Phalcon\Mvc\UrlInterface getUrl()
 * @method \Phalcon\Mvc\Model\Manager|\Phalcon\Mvc\Model\ManagerInterface getModelsManager()
 * @method \Phalcon\Mvc\Model\MetaData\Memory|\Phalcon\Mvc\Model\MetadataInterface getModelsMetadata()
 * @method \Phalcon\Http\Response|\Phalcon\Http\ResponseInterface getResponse()
 * @method \Phalcon\Http\Request|\Phalcon\Http\RequestInterface getRequest()
 * @method \Phalcon\Filter|\Phalcon\FilterInterface getFilter()
 * @method \Phalcon\Crypt|\Phalcon\CryptInterface getEscaper()
 * @method \Phalcon\Security getSecurity()
 * @method \Phalcon\Db\AdapterInterface getDb()
 * @method \Phalcon\Db\AdapterInterface getDbSlave()
 * @method \Phalcon\Crypt|\Phalcon\CryptInterface getCrypt()
 * @method \Phalcon\Mvc\Model\Transaction\Manager getTransactionManager()
 * @method \Phalcon\Mvc\Router|\Phalcon\Mvc\RouterInterface getRouter()
 * @method \Phalcon\Logger\AdapterInterface getLogger(string $name = null)
 * @method \Phalcon\Config getConfig()
 * @method \Uniondrug\Middleware\MiddlewareManager getMiddlewareManager()
 * @method \Phalcon\Cache\BackendInterface getCache(int $lifetime = null)
 * @method \Uniondrug\HttpClient\Client getHttpClient()
 * @method \Uniondrug\Trace\TraceClient getTraceClient()
 */
class Container extends Di
{
    /**
     * 版本号
     */
    const VERSION = '2.11.2';
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
        // 从.env设置环境变量
        $this->initEnv();
        // 设置默认的服务
        $this->_services = [
            "annotations" => new Service("annotations", "Phalcon\\Annotations\\Adapter\\Memory", true),
            "dispatcher" => new Service("dispatcher", "Phalcon\\Mvc\\Dispatcher", true),
            "url" => new Service("url", "Phalcon\\Mvc\\Url", true),
            "modelsManager" => new Service("modelsManager", "Phalcon\\Mvc\\Model\\Manager", true),
            "modelsMetadata" => new Service("modelsMetadata", "Phalcon\\Mvc\\Model\\MetaData\\Memory", true),
            "response" => new Service("response", "Phalcon\\Http\\Response", true),
            "request" => new Service("request", "Uniondrug\\Framework\\Request", true),
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
        // 注入Framework定义的服务
        $this->registerServices($this->_providers);
    }

    /**
     * Init Env from .env
     * @return void
     */
    public function initEnv()
    {
        $envFile = $this->baseDir.DIRECTORY_SEPARATOR.'.env';
        if (file_exists($envFile) && class_exists('Symfony\\Component\\Dotenv\\Dotenv')) {
            $dotenv = new \Symfony\Component\Dotenv\Dotenv();
            $dotenv->load($envFile);
        }
    }

    /**
     * @return string
     */
    public function environment()
    {
        $default = 'development';
        // 1. 读环境变量
        $value = getenv('APP_ENV');
        if ($value && Text::startsWith($value, '"') && Text::endsWith($value, '"')) {
            $value = substr($value, 1, -1);
        }
        // 2. 使用默认
        $value || $value = $default;
        // 3. 同步属性并返回
        return strtolower($value);
    }

    /**
     * 是否为开发环境
     * @return bool
     */
    public function isDevelopment()
    {
        return $this->environment() === "development";
    }

    /**
     * 是否为生产环境
     * @return bool
     */
    public function isProduction()
    {
        return $this->environment() === "production";
    }

    /**
     * 是否为测试环境
     * @return bool
     */
    public function isTesting()
    {
        return $this->environment() === "testing";
    }

    /**
     * 注册服务列表
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
     * @throws \Exception
     */
    public function run($applicationClassName)
    {
        try {
            $application = $this->getShared($applicationClassName);
            $response = $application->boot()->handle();
        } catch(\Throwable $e) {
            $response = $this->handleException($e);
        } finally {
            $response->send();
        }
    }

    /**
     * @param \Exception|\Throwable|\Error $e
     * @return \Phalcon\Http\Response
     */
    public function handleException($e)
    {
        // Log
        $logContext = [
            'error' => $e->getMessage(),
            'errno' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ];
        $this->getLogger("framework")->error("{error} ({errno}) in {file}:{line}\nStack trace:\n{trace}", $logContext);
        /**
         * @var \Uniondrug\Service\Server $server
         */
        $server = $this->getShared(\Uniondrug\Service\Server::class);
        $response = $server->withError($e->getMessage(), $e->getCode());
        return $response;
    }

    /**
     * @param $baseDir
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
     * Helpers: Get the base path.
     * @return string
     */
    public function basePath()
    {
        return $this->baseDir;
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
        if (defined("PHAR_WORKING_DIR")) {
            return PHAR_WORKING_DIR.DIRECTORY_SEPARATOR.'log';
        }
        return $this->baseDir.DIRECTORY_SEPARATOR.'log';
    }

    /**
     * Helpers: Get the path to the tmp directory.
     * @return string
     */
    public function tmpPath()
    {
        if (defined("PHAR_WORKING_DIR")) {
            return PHAR_WORKING_DIR.DIRECTORY_SEPARATOR.'tmp';
        }
        return $this->baseDir.DIRECTORY_SEPARATOR.'tmp';
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasSharedInstance($name)
    {
        return isset($this->_sharedInstances[$name]);
    }

    /**
     * @param $name
     */
    public function removeSharedInstance($name)
    {
        unset($this->_sharedInstances[$name]);
    }
}
