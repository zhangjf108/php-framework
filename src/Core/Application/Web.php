<?php
/**
 * 上海葡萄纬度科技有限公司.
 * @copyright Copyright (c) 2017 Shanghai Putao Technology Co., Ltd.
 * @license http://www.putao.com/
 * @author Zhang Jianfeng <zhangjianfeng@putao.com>
 * @date: 2017/6/29 21:05
 * @version 2.0.3
 */

namespace Kerisy\Core\Application;

use Kerisy;
use Kerisy\Core\Application;
use Kerisy\Core\Context;
use Kerisy\Core\Dispatcher;
use Kerisy\Core\Route;
use Kerisy\Exception\BadRequestException;
use Kerisy\Exception\CustomException;
use Kerisy\Exception\NotFoundException;
use Kerisy\Http\Factory\ResponseFactory;
use Kerisy\Http\Factory\StreamFactory;
use Kerisy\Session\Session;
use kerisy\View\Engine;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Kerisy\Http\Utils\HttpStatus;

/**
 * The web application
 *
 * @property \Kerisy\Session\Session $session The session component.
 * @property \Kerisy\View\Engine $view The view component.
 *
 * @package Kerisy\Core\Application
 */
class Web extends Application
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

            $taskMsg = $this->server->taskworker ? 'task' : '';

            try {
                $this->initializeConfig();
                $this->registerRoutes();
                $this->registerComponents();
                $this->bootstrapped = true;
                $this->log->info("application {$taskMsg} worker started");
            } catch (\Exception $t) {
                $this->log->error("application {$taskMsg} worker start error:" . $t->getMessage());
            }
        }

        return $this;
    }

    /**
     * Returns the session component.
     *
     * @return Session
     */
    public function getSession(): Session
    {
        return $this->get('session');
    }

    /**
     *  Returns the view component.
     *
     * @return Engine
     */
    public function getView(): Engine
    {
        return $this->get('view');
    }

    /**
     * Register default routes
     */
    protected function registerRoutes()
    {
        $this->dispatcher = new Dispatcher();
        $this->dispatcher->getRouter()->setConfig($this->getConfig('routes')->all());
    }

    /**
     * Handle Request
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handleRequest(ServerRequestInterface $request): ResponseInterface
    {
        $context = new Context();
        //$context->set('before', []);
        //$context->set('after', []);
        try {
            $response = $this->dispatch($request, $context);
        } catch (CustomException $e) { //可自定义输出异常
            $statusCode = $e->getStatusCode();
            $headers = $e->getResponseHeaders();

            $response = (new ResponseFactory())->createResponse($statusCode);

            foreach ($headers as $name => $header) {
                $response = $response->withHeader($name, $header);
            }

            $response = $response->withBody((new StreamFactory())->createStream($e->getResponseData()));

        } catch (\Exception $e) {
            $this->get('errorHandler')->handleException($e, $request);

            //response exception
            $statusCode = $e->getCode();

            if (!($body = HttpStatus::getReasonPhrase($statusCode))) {
                $statusCode = 500;
                $body = 'Internal Server Error';
            }

            //显示输入异常
            $formatedException = $this->formatException($e);

            if (!empty($formatedException)) {
                $data = json_encode($formatedException, JSON_UNESCAPED_UNICODE);

                if (json_last_error()) {
                    $data = json_last_error_msg();
                }

                $body = (new StreamFactory())->createStream($data);

                $response = (new ResponseFactory())
                    ->createResponse($statusCode)
                    ->withHeader('Content-Type', 'application/json;charset=utf-8')
                    ->withBody($body);
            } else {
                $response = (new ResponseFactory())
                    ->createResponse($statusCode)
                    ->withBody((new StreamFactory())->createStream($body));
            }
        } finally {

        }

        return $response;
    }

    /**
     * Dispatch the request.
     *
     * @param ServerRequestInterface $request
     * @param Context $context
     * @return ResponseInterface
     * @throws BadRequestException
     * @throws NotFoundException
     */
    protected function dispatch(ServerRequestInterface $request, Context $context): ResponseInterface
    {
        if (!$route = $this->dispatcher->dispatch($request)) {
            throw new NotFoundException(null, 404);
        }

        $controller = $this->getControllerClass($route);

        if (!class_exists($controller)) {
            throw new NotFoundException(null, 404);
        }

        $context->set('route', $route);

        $controller = new $controller($request, $context);

        $action = $route->getAction();

        if (!is_callable([$controller, $action])) {
            throw new BadRequestException(null, 403);
        }

        return $controller->$action();
    }

    /**
     * Get the controller class
     *
     * @param Route $route
     * @return string
     */
    private function getControllerClass(Route $route): string
    {
        $class = $this->controllerNamespace . ucfirst($route->getModule()) . "\\Controller\\" . ucfirst($route->getPrefix()) . "\\" . ucfirst($route->getController()) . "Controller";
        return $class;
    }
}