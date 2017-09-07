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
 * Class Controller
 * @package Kerisy\Controller
 */
class RpcController extends BaseController
{
    /**
     * @var Context
     */
    protected $context;

    public function __construct(Context $context)
    {
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
     * Render data.
     *
     * @param mixed $data
     * @return array
     */
    public function renderData($data)
    {
        $ret = [];
        $ret['data'] = $data;
        return $ret;
    }
}