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
class Controller extends BaseController
{
    /**
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var Route
     */
    private $route;

    /**
     * @var Engine
     */
    private $view = null;

    public function __construct(ServerRequestInterface $request, Context $context)
    {
        $this->request = $request;
        $this->response = (new ResponseFactory())->createResponse();
        $this->context = $context;

        if ($context->get('route')) {
            $this->route = $context->get('route');
        }

        //刷新Session过期时间
        $this->refreshSession();
    }

    /**
     * Renders a view and applies layout if available.
     *
     * @param string $view
     * @param array $params
     * @return ResponseInterface
     */
    public function render($view, $params = []): ResponseInterface
    {
        $content = $this->getView()->render($view, $params);
        $body = (new StreamFactory())->createStream($content);
        $this->response = $this->response->withHeader('Content-Type', 'text/html; charset=utf-8')->withBody($body);
        return $this->response;
    }

    /**
     * Renders a view without applying layout.
     *
     * @param string $view
     * @param array $params
     */
    public function renderPartial($view, $params = [])
    {

    }

    /**
     * Render string data.
     *
     * @param string $content
     * @return ResponseInterface
     */
    public function renderContent(string $content = ''): ResponseInterface
    {
        $this->response = $this->response->withBody((new StreamFactory())->createStream($content));
        return $this->response;
    }

    /**
     * Render json data.
     *
     * @param mixed $data
     * @return ResponseInterface
     */
    public function renderJson($data): ResponseInterface
    {
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        $body = (new StreamFactory())->createStream($data);

        $this->response = $this->response->withHeader('Content-Type', 'application/json;charset=utf-8')->withBody($body);

        return $this->response;
    }

    public function renderFile($file)
    {

    }

    /**
     * Redirects the browser to the specified URL.
     * This method is a shortcut to [[Response::redirect()]].
     *
     * You can use it in an action by returning the [[Response]] directly:
     *
     * ```php
     * // stop executing this action and redirect to login page
     * return $this->redirect(['login']);
     * ```
     *
     * @param string $url the URL to be redirected to. This can be in one of the following formats:
     *
     * - a string representing a URL (e.g. "http://example.com")
     * - a string representing a URL alias (e.g. "@example.com")
     *
     * @param integer $statusCode the HTTP status code. Defaults to 302.
     * See <http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html>
     * for details about HTTP status code
     * @return ResponseInterface the current response object
     */
    public function redirect($url, $statusCode = 302): ResponseInterface
    {
        $this->response = $this->response->redirect($url, $statusCode);
        return $this->response;
    }

    /**
     * Redirects the browser to the home page.
     *
     * You can use this method in an action by returning the [[Response]] directly:
     *
     * ```php
     * // stop executing this action and redirect to home page
     * return $this->goHome();
     * ```
     *
     * @return ResponseInterface the current response object
     */
    public function goHome(): ResponseInterface
    {
        $this->response = $this->response->redirect($this->request->getUri()->getHost());
        return $this->response;
    }

    /**
     * 添加Response Cookie的快捷方法
     *
     * @param Cookie $cookie
     */
    public function addCookie(Cookie $cookie)
    {
        if ($this->response->hasHeader('Set-Cookie')) {
            $this->response = $this->response->withAddedHeader('Set-Cookie', $cookie->__toString());
        } else {
            $this->response = $this->response->withHeader('Set-Cookie', $cookie->__toString());
        }
    }

    /**
     * Return session id
     *
     * @return string
     */
    public function getSessionId()
    {
        $session = \Kerisy::$app->session;

        $cookies = $this->request->getCookieParams();

        if (isset($cookies[$session->name])) {
            $sessionId = $cookies[$session->name];
        } else {
            $sessionId = $session->generateSessionId();
            $sessionCookie = new Cookie($session->name, $sessionId);
            $this->addCookie($sessionCookie);
            $session->initialize($sessionId);
        }

        return $sessionId;
    }

    /**
     * 获取Session数据
     *
     * @param string|null $key key===null 返回所有session数据
     * @return array
     */
    public function getSession(string $key = null)
    {
        $sessionId = $this->getSessionId();

        return $key === null ? Kerisy::$app->session->all($sessionId) : Kerisy::$app->session->get($sessionId, $key);
    }

    /**
     * 设置Session数据
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function setSession(string $key, $value)
    {
        $sessionId = $this->getSessionId();
        return Kerisy::$app->session->set($sessionId, $key, $value);
    }

    /**
     * Returns the view engine object
     *
     * @return Engine
     */
    public function getView()
    {
        if (!$this->view) {
            $this->view = Kerisy::$app->get('view');
            $this->view->setDirectory($this->getViewPath());
        }
        return $this->view;
    }

    /**
     * Sets the view object to be used by this controller.
     * @param Engine $view the view object that can be used to render views or view files.
     */
    public function setView($view)
    {
        $this->view = $view;
    }

    /**
     * Returns the directory containing view files for this controller.
     * The default implementation returns the directory named as controller [[prefix]] under the [[module]]'s
     * [[viewPath]] directory.
     * @return string the directory containing the view files for this controller.
     */
    public function getViewPath()
    {
        return Kerisy::$app->viewPath . strtolower($this->route->getPrefix());
    }

    /**
     * 刷新Session过期时间
     */
    private function refreshSession()
    {
        //todo
    }
}