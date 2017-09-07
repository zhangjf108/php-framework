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

namespace Kerisy\Server\Websocket;

use Kerisy\Server\ServerBase;
use \swoole_http_request as SwooleHttpRequest;
use \swoole_http_response as SwooleHttpResponse;
use \swoole_websocket_server as SwooleWebsocketServer;
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
    public $maxRequests = 200000;

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
     * @var int
     */
    public $packageMaxLength;

    /**
     * 1:text 2:binary
     *
     * @var int
     */
    public $opcode = 1;

    /**
     * @var int
     */
    public $heartbeatCheckInterval = 30;

    /**
     * @var int
     */
    public $heartbeatIdleTime = 60;

    /**
     * @var \Closure
     */
    public $onOpenCallback = null;

    /**
     * @var \Closure
     */
    public $onCloseCallback = null;

    /**
     * @var \Closure
     */
    public $onMessageCallbask = null;

    /**
     * @var SwooleWebsocketServer
     */
    private $swooleWebsocketServer;

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

        if ($this->heartbeatCheckInterval) {
            $config['heartbeat_check_interval'] = $this->heartbeatCheckInterval;
        }

        if ($this->heartbeatIdleTime) {
            $config['heartbeat_idle_time'] = $this->heartbeatIdleTime;
        }

        return $config;
    }

    /**
     * Create Swoole Websocket Server.
     *
     * @return SwooleWebsocketServer
     */
    private function createWebsocketServer()
    {
        $server = new SwooleWebsocketServer($this->host, $this->port);

        $server->on('start', [$this, 'onServerStart']);
        $server->on('shutdown', [$this, 'onServerStop']);

        $server->on('managerStart', [$this, 'onManagerStart']);

        $server->on('workerStart', [$this, 'onWorkerStart']);
        $server->on('workerStop', [$this, 'onWorkerStop']);

        $server->on('request', [$this, 'onRequest']);

        if (is_callable($this->onOpenCallback)) {
            $server->on('open', $this->onOpenCallback);
        } else if (method_exists($this, 'onOpen')) {
            $server->on('open', [$this, 'onOpen']);
        }

        if (is_callable($this->onCloseCallback)) {
            $server->on('close', $this->onCloseCallback);
        } else if (method_exists($this, 'onClose')) {
            $server->on('close', [$this, 'onClose']);
        }

        /*
         * Swoole 内部处理handshake
        if (method_exists($this, 'onWsHandshake')) {
            $server->on('handshake', [$this, 'onWsHandshake']);
        }
        */

        if (is_callable($this->onMessageCallbask)) {
            $server->on('message', $this->onMessageCallbask);
        } else if (method_exists($this, 'onWsMessage')) {
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
     * @param SwooleWebsocketServer $swooleWebsocketServer
     */
    public function onServerStart(SwooleWebsocketServer $swooleWebsocketServer)
    {
        PHP_OS != 'Darwin' && cli_set_process_title($this->name . ': master');
        if ($this->pidFile) {
            file_put_contents($this->pidFile, $swooleWebsocketServer->master_pid);
        }
    }

    /**
     * Listen Manager Start Event.
     *
     * @param SwooleWebsocketServer $swooleWebsocketServer
     */
    public function onManagerStart(SwooleWebsocketServer $swooleWebsocketServer)
    {
        PHP_OS != 'Darwin' && cli_set_process_title($this->name . ': manager');
    }

    /**
     * Listen Server Stop Event.
     *
     * @param SwooleWebsocketServer $swooleWebsocketServer
     */
    public function onServerStop(SwooleWebsocketServer $swooleWebsocketServer)
    {
        if ($this->pidFile) {
            unlink($this->pidFile);
        }
    }

    /**
     * Listen Worker Start Event.
     *
     * @param SwooleWebsocketServer $swooleWebsocketServer
     */
    public function onWorkerStart(SwooleWebsocketServer $swooleWebsocketServer)
    {
        PHP_OS != 'Darwin' && cli_set_process_title($this->name . ': worker');
        $this->startApp($swooleWebsocketServer);
    }

    /**
     * Listen Worker Stop Event.
     *
     * @param SwooleWebsocketServer $swooleWebsocketServer
     */
    public function onWorkerStop(SwooleWebsocketServer $swooleWebsocketServer)
    {

    }

    public function onOpen(SwooleWebsocketServer $swooleWebsocketServer, $request)
    {

    }

    public function onWsMessage(SwooleWebsocketServer $swooleWebsocketServer, $frame)
    {
        if ($frame->finish) {
            //todo handle opcode
            $data = json_decode($frame->data, true);
            $path = isset($data['path']) ? $data['path'] : '/';
            $params = isset($data['params']) ? $data['params'] : [];
            $response = $this->handleWebSocket($frame->opcode, $path, $params);

            $finish = true;
            $response && $swooleWebsocketServer->push($frame->fd, $response, $frame->opcode, $finish);
            //!$finish && /*todo log*/
        }
    }

    public function onClose(SwooleWebsocketServer $swooleWebsocketServer, $fd)
    {
        $clientInfo = $swooleWebsocketServer->connection_info($fd);
        /**
         * WEBSOCKET_STATUS_CONNECTION = 1，连接进入等待握手
         * WEBSOCKET_STATUS_HANDSHAKE = 2，正在握手
         * WEBSOCKET_STATUS_FRAME = 3，已握手成功等待浏览器发送数据帧
         */
        if ($clientInfo && $clientInfo['websocket_status'] == 3) {
            echo "client {$fd} closed" . PHP_EOL;
        }
    }

    public function onTask(SwooleWebsocketServer $swooleWebsocketServer, int $taskId, int $srcWorkerId, $data)
    {
        $path = isset($data['path']) ? $data['path'] : '/';
        unset($data['path']);
        $this->handleTask($path, $data);
    }

    public function onFinish(SwooleWebsocketServer $swooleWebsocketServer, int $taskId, $data)
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
        $this->swooleWebsocketServer = $this->createWebsocketServer();
        $this->swooleWebsocketServer->start();
    }

    public function reload()
    {

    }

    public function stop()
    {

    }
}
