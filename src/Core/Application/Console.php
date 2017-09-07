<?php
/**
 * 上海葡萄纬度科技有限公司.
 * @copyright Copyright (c) 2017 Shanghai Putao Technology Co., Ltd.
 * @license http://www.putao.com/
 * @author Zhang Jianfeng <zhangjianfeng@putao.com>
 * @Date: 2017/6/27 21:05
 */

namespace Kerisy\Core\Application;

use Kerisy;
use Kerisy\Core\Application;
use Kerisy\Core\Context;
use Kerisy\Exception\NotFoundException;

class Console extends Application
{
    public function __construct(array $config)
    {
        parent::__construct($config);
    }

    public function bootstrap()
    {
        $this->initializeConfig();
        $this->registerComponents();

        Kerisy::$app = $this;

        return $this;
    }

    public function handleConsole(string $path, array $argv = [])
    {
        $context = new Context();

        try {
            $return = $this->dispatchConsole($path, $argv, $context);
        } catch (\Exception $e) {
            $return = "Exception:" . $e->getMessage() . ';' . $e->getFile() . "(" . $e->getLine() . ")";
        } finally {

        }

        return $return;
    }

    /**
     * Dispatch the request.
     *
     * @param string $path
     * @param array $argv
     * @param Context $context
     * @return int
     * @throws NotFoundException
     */
    protected function dispatchConsole(string $path, array $argv, Context $context)
    {
        list($controller, $action) = $this->getConsoleControllerAction($path);

        if (!class_exists($controller)) {
            throw new NotFoundException("Controller '{$controller}' Not Found", 404);
        }

        $controller = new $controller($argv, $context);

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
    private function getConsoleControllerAction(string $path): array
    {
        $pathInfo = explode('/', trim($path, '/'));

        $module = isset($pathInfo[0]) ? ucfirst($pathInfo[0]) : 'Core';
        $controller = isset($pathInfo[1]) ? ucfirst($pathInfo[1]) : 'Index';
        $action = isset($pathInfo[2]) ? ucfirst($pathInfo[2]) : 'Index';
        $controller = $this->controllerNamespace . $module . "\\Console\\" . $controller . "Controller";

        return [$controller, $action];
    }
}