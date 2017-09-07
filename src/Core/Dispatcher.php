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


use Psr\Http\Message\ServerRequestInterface;

/**
 * Class Dispatcher
 *
 * @package Kerisy\Core
 */
class Dispatcher extends Component
{
    /**
     * @var Router
     */
    protected $router;

    public function init()
    {
        $this->router = Router::getInstance();
    }

    public function getRouter()
    {
        return $this->router;
    }

    public function dispatch(ServerRequestInterface $request)
    {
        $route = $this->router->routing($request);
        return $route;
    }
}
