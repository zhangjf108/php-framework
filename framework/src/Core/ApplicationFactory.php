<?php
/**
 * 上海葡萄纬度科技有限公司.
 * @copyright Copyright (c) 2017 Shanghai Putao Technology Co., Ltd.
 * @license http://www.putao.com/
 * @author Zhang Jianfeng <zhangjianfeng@putao.com>
 * @date: 2017/7/26 09:55
 * @version 3.0.0
 */

namespace Kerisy\Core;


use Kerisy\Core\Application\Rpc;
use Kerisy\Core\Application\Web;
use Kerisy\Core\Application\WebSocket;
use Kerisy\Core\Application\Console;

class ApplicationFactory
{
    /**
     * 创建不同类型App
     *
     * @param string $type
     * @param array $params
     * @return Web|WebSocket|Console|Rpc
     */
    public static function createApplicaton(string $type = 'http', array $params)
    {
        switch ($type) {
            case 'http':
                $app = static::createHttpApplication($params);
                break;
            case 'websocket':
                $app = static::createWebSocketApplication($params);
                break;
            case 'tcp':
                $app = static::createTcpApplication($params);
                break;
            case 'console':
                $app = static::createConsoleApplication($params);
                break;
            case 'rpc':
                $app = static::createRpcApplication($params);
                break;
            default:
                $app = false;
        }
        return $app;
    }

    public static function createHttpApplication(array $config)
    {
        return new Web($config);
    }

    public static function createWebSocketApplication(array $config)
    {
        return new WebSocket($config);
    }

    public static function createTcpApplication(array $config)
    {

    }

    public static function createConsoleApplication(array $config)
    {
        return new Console($config);
    }

    public static function createRpcApplication(array $config)
    {
        return new Rpc($config);
    }
}