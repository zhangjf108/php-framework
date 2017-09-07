<?php
/**
 * 上海葡萄纬度科技有限公司.
 * @copyright Copyright (c) 2017 Shanghai Putao Technology Co., Ltd.
 * @license http://www.putao.com/
 * @author Zhang Jianfeng <zhangjianfeng@putao.com>
 * @date: 2017/7/24 17:04
 * @version 3.0.0
 */

namespace Kerisy\Core\Application;

use Kerisy\Core\Context;
use Kerisy\Exception\NotFoundException;

class WebSocket extends Web
{


    public function __construct(array $config)
    {
        parent::__construct($config);
    }

    public function handleWebSocket(int $opcode, string $path, array $params = [])
    {
        $context = new Context();

        try {
            $return = $this->dispatchWebSocket($opcode, $path, $params, $context);
        } catch (\Exception $e) {
            //todo
            $return = json_encode(['path' => $path, 'data' => [], 'msg' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        } finally {

        }

        return $return;
    }

    /**
     * Dispatch
     *
     * @param int $opcode
     * @param string $path
     * @param array $params
     * @param Context $context
     * @return mixed
     * @throws NotFoundException
     */
    protected function dispatchWebSocket(int $opcode, string $path, array $params = [], Context $context)
    {
        list($controller, $action) = $this->getWebSocketControllerAction($path);

        if (!class_exists($controller)) {
            throw new NotFoundException("Controller '{$controller}' Not Found", 404);
        }

        $controller = new $controller($opcode, $path, $params, $context);

        if (!is_callable([$controller, $action])) {
            throw new NotFoundException("Action '{$controller}:{$action}' Not Found", 404);
        }

        return $controller->$action();
    }

    /**
     * Get the controller class
     *
     * @param string $path
     * @return array
     */
    private function getWebSocketControllerAction(string $path): array
    {
        $pathInfo = explode('/', trim($path, '/'));

        $module = (isset($pathInfo[0]) && $pathInfo[0]) ? ucfirst($pathInfo[0]) : 'Core';
        $controllerName = (isset($pathInfo[1]) && $pathInfo[1]) ? ucfirst($pathInfo[1]) : 'Index';
        $action = (isset($pathInfo[2])) ? ucfirst($pathInfo[2]) : 'index';
        $controller = $this->controllerNamespace . $module . "\\WebSocket\\" . $controllerName . "Controller";

        return [$controller, $action];
    }

}