<?php
/**
 * Kerisy Framework
 *
 * PHP Version 7
 *
 * @author             Jiaqing Zou <zoujiaqing@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @package            kerisy/framework
 * @subpackage         Server
 * @since              2015/11/11
 * @version            2.0.0
 */

namespace Kerisy\Server\Http;


use Kerisy\Server\ServerBase;
use \swoole_http_request as SwooleHttpRequest;
use \swoole_http_response as SwooleHttpResponse;
use \swoole_http_server as SwooleHttpServer;
use Kerisy\Http\Factory\ServerRequestFactory;

/**
 * A Swoole based server implementation.
 *
 * @package Kerisy\Server
 */
class Swoole extends ServerBase
{
    /**
     * The number of requests each process should execute before respawning, This can be useful to work around
     * with possible memory leaks.
     *
     * @var int
     */
    public $maxRequests = 65535;

    /**
     * The number of workers should be started to server requests.
     *
     * @var int
     */
    public $numWorkers;

    /**
     * The number of task workers should be started.
     *
     * @var int
     */
    public $taskWorkers = 0;

    /**
     * Detach the server process and run as daemon.
     *
     * @var bool
     */
    public $asDaemon = false;

    /**
     * Specifies the path where logs should be stored in.
     *
     * @var string
     */
    public $logFile;

    /**
     * @var SwooleHttpServer
     */
    private $swooleHttpServer;

    /**
     * @var int
     */
    public $packageMaxLength;

    /**
     * Normalized Swoole Http Server Config
     *
     * @return array
     */
    private function normalizedConfig()
    {
        $config = [];

        $config['max_request'] = $this->maxRequests;
        $config['daemonize'] = $this->asDaemon;

        if ($this->numWorkers) {
            $config['worker_num'] = $this->numWorkers;
        }

        if ($this->taskWorkers) {
            $config['task_worker_num'] = $this->taskWorkers;
        }

        if ($this->logFile) {
            $config['log_file'] = $this->logFile;
        }

        if ($this->packageMaxLength) {
            $config['package_max_length'] = $this->packageMaxLength;
        }

        return $config;
    }

    /**
     * Create Swoole Http Server.
     *
     * @return SwooleHttpServer
     */
    private function createHttpServer()
    {
        $server = new SwooleHttpServer($this->host, $this->port);

        $server->on('start', [$this, 'onServerStart']);
        $server->on('shutdown', [$this, 'onServerStop']);

        $server->on('managerStart', [$this, 'onManagerStart']);

        $server->on('workerStart', [$this, 'onWorkerStart']);
        $server->on('workerStop', [$this, 'onWorkerStop']);

        $server->on('request', [$this, 'onRequest']);

        if (method_exists($this, 'onOpen')) {
            $server->on('open', [$this, 'onOpen']);
        }
        if (method_exists($this, 'onClose')) {
            $server->on('close', [$this, 'onClose']);
        }

        if (method_exists($this, 'onWsHandshake')) {
            $server->on('handshake', [$this, 'onWsHandshake']);
        }
        if (method_exists($this, 'onWsMessage')) {
            $server->on('message', [$this, 'onWsMessage']);
        }

        if (method_exists($this, 'onTask')) {
            $server->on('task', [$this, 'onTask']);
        }
        if (method_exists($this, 'onFinish')) {
            $server->on('finish', [$this, 'onFinish']);
        }

        $server->set($this->normalizedConfig());

        return $server;
    }

    /**
     * Listen Server Start Event.
     *
     * @param SwooleHttpServer $swooleHttpServer
     */
    public function onServerStart(SwooleHttpServer $swooleHttpServer)
    {
        PHP_OS != 'Darwin' && cli_set_process_title($this->name . ': master');
        if ($this->pidFile) {
            file_put_contents($this->pidFile, $swooleHttpServer->master_pid);
        }
    }

    /**
     * Listen Manager Start Event.
     *
     * @param SwooleHttpServer $swooleHttpServer
     */
    public function onManagerStart(SwooleHttpServer $swooleHttpServer)
    {
        PHP_OS != 'Darwin' && cli_set_process_title($this->name . ': manager');
    }

    /**
     * Listen Server Stop Event.
     *
     * @param SwooleHttpServer $swooleHttpServer
     */
    public function onServerStop(SwooleHttpServer $swooleHttpServer)
    {
        if ($this->pidFile) {
            unlink($this->pidFile);
        }
    }

    /**
     * Listen Worker Start Event.
     *
     * @param SwooleHttpServer $swooleHttpServer
     */
    public function onWorkerStart(SwooleHttpServer $swooleHttpServer)
    {
        PHP_OS != 'Darwin' && cli_set_process_title($this->name . ': worker');
        $this->startApp($swooleHttpServer);
    }

    /**
     * Listen Worker Stop Event.
     *
     * @param SwooleHttpServer $swooleHttpServer
     */
    public function onWorkerStop(SwooleHttpServer $swooleHttpServer)
    {

    }

    public function onTask(SwooleHttpServer $swooleHttpServer, int $taskId, int $srcWorkerId, $data)
    {
        //var_dump($srcWorkerId);
        //var_dump($swooleHttpServer, $taskId, $srcWorkerId, $data);
        //print_r($this->swooleHttpServer->stats());
        $path = isset($data['path']) ? $data['path'] : '/';
        unset($data['path']);

        $this->handleTask($path, $data);
    }

    public function onFinish(SwooleHttpServer $swooleHttpServer, int $taskId, $data)
    {

    }

    /**
     * Listen Swoole Http Server Request Event.
     *
     * @param SwooleHttpRequest $swooleHttpRequest
     * @param SwooleHttpResponse $swooleHttpResponse
     */
    public function onRequest(SwooleHttpRequest $swooleHttpRequest, SwooleHttpResponse $swooleHttpResponse)
    {
        //$this->xhprofBegin($swooleHttpRequest);

        $request = ServerRequestFactory::createServerRequestFromSwoole($swooleHttpRequest);

        $response = $this->handleRequest($request);

        foreach ($response->getHeaders() as $name => $values) {
            $name = str_replace(' ', '-', ucwords(str_replace('-', ' ', $name)));
            foreach ($values as $value) {
                $swooleHttpResponse->header($name, $value);
            }
        }

        @ob_start();

        $content = $response->getBody();

        @ob_end_flush();

        //$swooleHttpResponse->header('Content-Length', $content->getSize());
        $swooleHttpResponse->status($response->getStatusCode());

        //$this->xhprofEnd();

        $swooleHttpResponse->end($content);
    }

    /**
     * 设置进程名
     * @param string $name
     */
    public function setProcessName(string $name)
    {
        $this->name = $name;
    }

    /**
     * Server Start
     */
    public function run()
    {
        $this->swooleHttpServer = $this->createHttpServer();
        $this->swooleHttpServer->start();
    }

    public function reload()
    {

    }

    public function stop()
    {

    }

    private function xhprofBegin(SwooleHttpRequest $request)
    {
        $filePath = '/Users/zhangjianfeng/workspace/php/xhgui/external/header.php';
        if (!is_file($filePath)) return;
        $tmp = [];
        $server = $request->server;
        $header = $request->header;
        foreach ($server as $k => $v) {
            $tmpKey = strtoupper($k);
            $tmp[$tmpKey] = $v;
        }
        $_SERVER = $tmp;
        $host = isset($header['host']) ? $header['host'] : "";
        $requestUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : "";
        $_SERVER['REQUEST_URI'] = $host . $requestUri;
        if (!class_exists('XHGheader', false)) {
            require $filePath;
        }
        $obj = new \XHGheader();
        $obj->start();
    }

    private function xhprofEnd()
    {
        if (class_exists('XHGheader')) {
            $obj = new \XHGheader();
            $diffTime = $obj->getDiffTime();
            if ($diffTime <= 1) {
                xhprof_disable();
                return;
            }
            $obj->doXHG();
        }
    }
}
