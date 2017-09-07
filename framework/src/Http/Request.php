<?php
/**
 * 上海葡萄纬度科技有限公司.
 * @copyright Copyright (c) 2017 Shanghai Putao Technology Co., Ltd.
 * @license   http://www.putao.com/
 * @author    Zhang Jianfeng <zhangjianfeng@putao.com>
 * @date:     2017/6/14 17:52
 * @version   2.0.1
 */

namespace Kerisy\Http;


use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

class Request extends Message implements RequestInterface
{
    /**
     * @var StreamInterface
     */
    
    protected $body;

    /**
     * @var string[][]
     */
    
    protected $headers;

    /**
     * @var string
     */
    
    protected $method;

    /**
     * @var string
     */
    
    protected $target;

    /**
     * @var UriInterface
     */
    
    protected $uri;

    /**
     * @var string
     */
    
    protected $version;

    /**
     * Creates a Request instance.
     *
     * @param string          $method  The request method.
     * @param UriInterface    $uri     The request URI.
     * @param string[][]      $headers The request headers.
     * @param StreamInterface $body    The request body.
     * @param string          $version The HTTP protocol version as a string.
     */
    
    public function __construct(string $method, UriInterface $uri, array $headers, StreamInterface $body, string $version)
    {
        $this->method = $method;
        $this->uri = $uri;
        $this->headers = $headers;
        $this->body = $body;
        $this->version = $version;
    }

    /**
     * Clones a Request instance.
     */
    
    public function __clone()
    {
        $this->uri = clone $this->uri;
    }

    /**
     * {@inheritdoc}
     */

    final public function getMethod() : string
    {
        return $this->method;
    }

    /**
     * {@inheritdoc}
     */

    final public function getRequestTarget() : string
    {
        return $this->target ?? '/';
    }

    /**
     * {@inheritdoc}
     */

    final public function getUri() : UriInterface
    {
        return $this->uri;
    }

    /**
     * {@inheritdoc}
     */

    final public function withMethod($method) : RequestInterface
    {
        $instance = clone $this;
        $instance->method = $method;

        return $instance;
    }

    /**
     * {@inheritdoc}
     */

    final public function withRequestTarget($target) : RequestInterface
    {
        $instance = clone $this;
        $instance->target = $target;

        return $instance;
    }

    /**
     * {@inheritdoc}
     */

    final public function withUri(UriInterface $uri, $preserve_host = false) : RequestInterface
    {
        $instance = clone $this;
        $instance->uri = $uri;

        if (!$preserve_host and $instance->hasHeader('Host') and !empty($instance->uri->getHost())) {
            $instance = $instance->withoutHeader('Host')->withHeader('Host', $instance->uri->getHost());
        }

        return $instance;
    }
}