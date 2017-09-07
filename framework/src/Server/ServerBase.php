<?php
/**
 * 上海葡萄纬度科技有限公司.
 * @copyright Copyright (c) 2017 Shanghai Putao Technology Co., Ltd.
 * @license http://www.putao.com/
 * @author Zhang Jianfeng <zhangjianfeng@putao.com>
 * @date: 2017/6/29 21:05
 * @version 2.0.3
 */

namespace Kerisy\Server;


use Kerisy;
use Kerisy\Core\Component;
use Kerisy\Core\Application;
use Kerisy\Exception\InvalidParamException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * The base class for Application Server.
 *
 * @package Kerisy\Server
 */
abstract class ServerBase extends Component
{
    public $host = '0.0.0.0';

    public $port = 8888;

    public $name = 'kerisy-server';

    public $pidFile;

    /**
     * Boot the app.
     *
     * @var Application\Web|Application\WebSocket|Application\Rpc
     */
    public $bootstrap;

    public function startApp($server = null)
    {
        if (!$this->bootstrap instanceof Application) {
            throw new InvalidParamException('Bootstrap fail.');
        }

        $this->bootstrap->bootstrap($server);
    }

    /**
     * Handle Request.
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handleRequest(ServerRequestInterface $request)
    {
        return $this->bootstrap->handleRequest($request);
    }

    /**
     * Handle task.
     *
     * @param string $path
     * @param $data
     * @return string
     */
    public function handleTask(string $path, $data)
    {
        return $this->bootstrap->handleTask($path, $data);
    }

    /**
     * Handle websocket message
     *
     * @param int $opcode
     * @param string $path
     * @param array $params
     * @return mixed
     */
    public function handleWebSocket(int $opcode = 1, string $path, array $params = [])
    {
        return $this->bootstrap->handleWebSocket($opcode, $path, $params);
    }

    abstract public function run();
}
