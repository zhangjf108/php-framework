<?php
/**
 * 上海葡萄纬度科技有限公司.
 * @copyright Copyright (c) 2017 Shanghai Putao Technology Co., Ltd.
 * @license http://www.putao.com/
 * @author Zhang Jianfeng <zhangjianfeng@putao.com>
 * @Date: 2017/6/29 16:22
 */

namespace Kerisy\Controller;

use Kerisy;
use Kerisy\Core\Context;
use Kerisy\Core\Route;
use Kerisy\Http\Cookie;
use Kerisy\Http\Factory\ResponseFactory;
use Kerisy\Http\Factory\StreamFactory;
use kerisy\View\Engine;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * The base http controller named controller
 *
 * Class Controller
 * @package Kerisy\Controller
 */
class WebSocketController extends BaseController
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var string
     */
    protected $path;

    /**
     * websocket opcode 1:text 2:binary
     * @var int
     */
    protected $opcode = 1;

    /**
     * @var array
     */
    protected $params = [];

    public function __construct(int $opcode, string $path, array $params, Context $context)
    {
        $this->opcode = $opcode;
        $this->path = $path;
        $this->params = $params;
        $this->context = $context;
    }

    /**
     * Render string data.
     *
     * @param string $content
     * @return string
     */
    public function renderContent(string $content = '')
    {
        return $content;
    }

    /**
     * Render json data.
     *
     * @param mixed $data
     * @return ResponseInterface
     */
    public function renderJson($data)
    {
        $ret = [];
        $ret['path'] = $this->path;
        $ret['data'] = $data;
        return json_encode($ret, JSON_UNESCAPED_UNICODE);
    }

    public function renderBinary($data)
    {

    }
}