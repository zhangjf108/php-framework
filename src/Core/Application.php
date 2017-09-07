<?php
/**
 * 上海葡萄纬度科技有限公司.
 * @copyright Copyright (c) 2017 Shanghai Putao Technology Co., Ltd.
 * @license http://www.putao.com/
 * @author Zhang Jianfeng <zhangjianfeng@putao.com>
 * @date: 2017/6/26 14:41
 * @version 2.0.3
 */

namespace Kerisy\Core;

use Kerisy;
use Kerisy\Log\Logger;
use Kerisy\Config\ConfigFactory;
use Kerisy\Config\ConfigInterface;
use Kerisy\Di\Container;
use Kerisy\Di\ServiceLocator;
use Kerisy\Exception\InvalidParamException;
use Kerisy\Exception\InvalidConfigException;
use Kerisy\Exception\NotSupportedException;
use Kerisy\Exception\NotFoundException;
use Kerisy\View\Engine;
use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;
use Kerisy\NoSQL\Redis;
use Kerisy\Db\Connection;
use \swoole_server as SwooleServer;
use \swoole_websocket_server as SwooleWebSocketServer;
use \swoole_http_server as SwooleHttpServer;


/**
 * Application is the base class for all application classes.
 *
 * @property \Kerisy\Config\ConfigInterface $config The config component.
 * @property \Kerisy\Log\Logger $log The log component. This property is read-only.
 * @property \Psr\Cache\CacheItemPoolInterface $cache the cache component. This property is read-only.
 * @property \Psr\SimpleCache\CacheInterface $simpleCache the cache component. This property is read-only.
 * @property \Kerisy\NoSQL\Redis $redis the cache component. This property is read-only.
 * @property \Kerisy\Db\Connection $db the db component.
 *
 * @package Kerisy\Core
 * @since 3.0
 */
class Application extends ServiceLocator
{
    /**
     * Application component definitions.
     *
     * @var array
     */
    public $components = [];

    /**
     * Is debug mode
     *
     * @var bool
     */
    public $debug = false;

    /**
     * The environment that the application is running on. development, test or production.
     *
     * @var string
     */
    public $environment = 'development';

    /**
     * The language config
     *    eg. zh_cn,en,fr,ja
     *
     * @var string
     */
    public $language = 'zh_cn';

    /**
     * The root path
     *
     * @var string
     */
    public $applicationPath = '/';

    /**
     * The config file path
     *
     * @var string
     */
    public $configPath = '';

    /**
     * The view file path
     *
     * @var string
     */
    public $viewPath = '';

    /**
     * The runtime path
     * @var string
     */
    public $runtime = '';

    /**
     * The timezone config
     *
     * @var string
     */
    public $timezone = 'UTC';

    /**
     * The Controller Namespace
     *
     * @var string
     */
    public $controllerNamespace = 'App\\';

    /**
     * @var Dispatcher
     */
    public $dispatcher;

    /**
     * Is bootstraped
     *
     * @var bool
     */
    protected $bootstrapped = false;

    /**
     * @var SwooleServer|SwooleWebSocketServer|SwooleHttpServer|null
     */
    public $server = null;

    /**
     * All Configs
     *
     * @var array
     */
    private $_configs = [];

    /**
     * Application constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
    }

    public function init()
    {
        if (!$this->applicationPath || !file_exists($this->applicationPath)) {
            throw new InvalidParamException("The param: 'APPLICATION_PATH' is invalid");
        }

        !$this->configPath && $this->configPath = $this->applicationPath . 'config/';
        !$this->viewPath && $this->viewPath = $this->applicationPath . 'views/';
        !$this->runtime && $this->runtime = $this->applicationPath . 'runtime/';

        $this->components = array_merge($this->defaultComponents(), $this->getConfig('components')->all());

        Kerisy::$app = $this;
        Kerisy::$container = new Container();
    }

    /**
     * Initialize application configs
     *
     */
    protected function initializeConfig()
    {
        date_default_timezone_set($this->timezone);
    }

    /**
     * Register the components
     *
     */
    protected function registerComponents()
    {
        $this->setComponents($this->components);
    }

    /**
     * Get Config Instance
     *
     * @param string $configGroup
     * @param string $filetype the config file type eg. php,json
     * @return ConfigInterface
     * @throws InvalidConfigException
     */
    public function getConfig(string $configGroup = 'config', string $filetype = 'php'): ConfigInterface
    {
        if (!isset($this->_configs[$configGroup])) {
            $this->_configs[$configGroup] = (new ConfigFactory())
                ->createConfig($this->configPath, $configGroup, $filetype, $this->environment, $this->language);
        }

        return $this->_configs[$configGroup];
    }

    /**
     * Returns the log component.
     *
     * @return Logger
     */
    public function getLog(): Logger
    {
        return $this->get('log');
    }

    /**
     * Returns the cache component.
     *
     * @return \Psr\Cache\CacheItemPoolInterface
     */
    public function getCache(): CacheItemPoolInterface
    {
        return $this->get('cache');
    }

    /**
     * Returns the simple cache component.
     *
     * @return CacheInterface
     */
    public function getSimpleCache(): CacheInterface
    {
        return $this->get('simpleCache');
    }

    /**
     * Returns the redis connection.
     *
     * @param string $instance redis instance name
     * @return Redis the redis application component.
     */
    public function getRedis(string $instance = 'redis'): Redis
    {
        return $this->get($instance);
    }

    /**
     * Returns the db connection.
     *
     * @param string $dbInstance
     * @return Connection
     */
    public function getDb($dbInstance = 'db'): Connection
    {
        return $this->get($dbInstance);
    }

    /**
     * Send dato to swoole task worker.
     *
     * @param string $path
     * @param mixed $data
     * @return bool
     * @throws NotSupportedException
     */
    public function task(string $path, $data)
    {
        if ($this->server instanceof SwooleServer) {
            $ret = $this->server->task(['path' => $path, 'data' => $data]);
            return $ret;
        } else {
            throw new NotSupportedException('The server not support task.');
        }
    }

    /**
     * Handle the task
     *
     * @param string $path
     * @param mixed $data
     * @return string
     */
    public function handleTask(string $path, $data)
    {
        $context = new Context();

        try {
            $return = $this->dispatchTask($path, $data, $context);
        } catch (\Exception $e) {
            $return =  "Exception:" . $e->getMessage() . ';' . $e->getFile() . "(" . $e->getLine() . ")";
            echo $return . PHP_EOL;
        } finally {

        }

        return $return;
    }

    /**
     * 默认加载的组件
     * @return array
     */
    protected function defaultComponents()
    {
        return [
            'log' => [
                'class' => Logger::class,
            ],
            'errorHandler' => [
                'class' => ErrorHandler::class,
            ],
            'view' => [
                'class' => Engine::class
            ],
        ];
    }

    /**
     * Handle the exception
     *
     * @param \Exception $exception
     * @param mixed $context
     */
    protected function handleException(\Exception $exception, $context = null)
    {
        $message =  "Exception '" . get_class($exception) . "' with message '{$exception->getMessage()}' in "
            . $exception->getFile() . '(' . $exception->getLine() . ')';

        $this->log->warning($message, $context);
    }

    /**
     * Exception to array
     *
     * @param \Exception $exception
     * @return array
     */
    protected function formatException(\Exception $exception): array
    {
        $array = [];

        if ($this->debug) {
            $array['name'] = get_class($exception);
            $array['file'] = $exception->getFile();
            $array['line'] = $exception->getLine();
            $array['message'] = $exception->getMessage();
        }

        return $array;
    }

    /**
     * Dispatch the request.
     *
     * @param string $path
     * @param mixed $data
     * @param Context $context
     * @return int
     * @throws NotFoundException
     */
    private function dispatchTask(string $path, $data, Context $context)
    {
        list($controller, $action) = $this->getTaskControllerAction($path);

        if (!class_exists($controller)) {
            throw new NotFoundException("Task controller '{$controller}' Not Found", 404);
        }

        $controller = new $controller($data, $context);

        if (!is_callable([$controller, $action])) {
            throw new NotFoundException("Task action '{$controller}:{$action}' Not Found", 404);
        }

        return $controller->$action();
    }

    /**
     * Get the controller class
     *
     * @param string $path
     * @return array
     */
    private function getTaskControllerAction(string $path): array
    {
        $pathInfo = explode('/', trim($path, '/'));

        $module = isset($pathInfo[0]) ? ucfirst($pathInfo[0]) : 'Core';
        $controllerName = isset($pathInfo[1]) ? ucfirst($pathInfo[1]) : 'Index';
        $action = isset($pathInfo[2]) ? ucfirst($pathInfo[2]) : 'index';
        $controller = $this->controllerNamespace . $module . "\\Task\\" . $controllerName . "Controller";

        return [$controller, $action];
    }
}
