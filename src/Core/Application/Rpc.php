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

use Kerisy\Core\Application;
use Kerisy\Core\Context;
use Kerisy\Exception\NotFoundException;

class Rpc extends Application
{
    public function __construct(array $config)
    {
        parent::__construct($config);
    }

    /**
     * Boot the application
     *
     * @param $server
     * @return $this
     */
    public function bootstrap($server = null)
    {
        if (!$this->bootstrapped) {
            $this->server = $server;

            $taskMsg = $this->server->taskworker ? ' task' : '';

            try {
                $this->initializeConfig();
                $this->registerComponents();
                $this->bootstrapped = true;
                $this->log->info("application {$taskMsg} worker started");
            } catch (\Exception $t) {
                $this->log->error("application {$taskMsg} worker start error:" . $t->getMessage());
            }
        }

        return $this;
    }

    public function handleRpc(string $path, array $params = [])
    {
        $context = new Context();

        try {
            $return = $this->dispatchRpc($path, $params, $context);
        } catch (\Exception $e) {
            $return = json_encode(['data' => [], 'msg' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        } finally {

        }

        return $return;
    }

    /**
     * Dispatch
     *
     * @param string $path
     * @param array $params
     * @param Context $context
     * @return mixed
     * @throws NotFoundException
     */
    protected function dispatchRpc(string $path, array $params = [], Context $context)
    {
        list($controller, $action) = $this->getRpcControllerAction($path);

        if (!class_exists($controller)) {
            throw new NotFoundException("Controller '{$controller}' Not Found", 404);
        }

        $controller = new $controller($context);

        if (!is_callable([$controller, $action])) {
            throw new NotFoundException("Action '{$controller}:{$action}' Not Found", 404);
        }

        return call_user_func_array([$controller, $action], $params);
    }

    /**
     * Get the controller class
     *
     * @param string $path
     * @return array
     */
    private function getRpcControllerAction(string $path): array
    {
        $pathInfo = explode('/', trim($path, '/'));

        $module = (isset($pathInfo[0]) && $pathInfo[0]) ? ucfirst($pathInfo[0]) : 'Core';
        $controllerName = (isset($pathInfo[1]) && $pathInfo[1]) ? ucfirst($pathInfo[1]) : 'Index';
        $action = (isset($pathInfo[2])) ? ucfirst($pathInfo[2]) : 'index';
        $controller = $this->controllerNamespace . $module . "\\Rpc\\" . $controllerName . "Controller";

        return [$controller, $action];
    }

}