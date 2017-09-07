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
use Psr\Http\Message\MessageInterface;
use Kerisy\Http\Exception\InvalidArgumentException;

abstract class Message implements MessageInterface
{
    /**
     * @var StreamInterface
     */
    
    protected $body;

    /**
     * @var array
     */
    
    protected $headers = [];

    /**
     * @var string
     */
    
    protected $version;

    /**
     * {@inheritdoc}
     */

    final public function getBody() : StreamInterface
    {
        return $this->body;
    }

    /**
     * {@inheritdoc}
     */

    final public function getHeader($name) : array
    {
        if ($this->hasHeader($name)) {
            return $this->headers[$name];
        }
        
        return [];
    }

    /**
     * {@inheritdoc}
     */

    final public function getHeaderLine($name) : string
    {
        return implode(', ', $this->getHeader($name));
    }

    /**
     * {@inheritdoc}
     */

    final public function getHeaders() : array
    {
        return $this->headers;
    }

    /**
     * {@inheritdoc}
     */
    
    final public function getProtocolVersion() : string
    {
        return $this->version;
    }

    /**
     * {@inheritdoc}
     */
    
    final public function hasHeader($name) : bool
    {
        return array_key_exists(strtolower($name), array_change_key_case($this->headers, CASE_LOWER));
    }

    /**
     * {@inheritdoc}
     */

    final public function withAddedHeader($name, $values) : MessageInterface
    {
        $instance = clone $this;
        $instance->headers[$name] = array_merge($this->headers[$name], (array) $values);

        return $instance;
    }

    /**
     * {@inheritdoc}
     */

    final public function withBody(StreamInterface $body) : MessageInterface
    {
        $instance = clone $this;
        $instance->body = $body;

        return $instance;
    }

    /**
     * {@inheritdoc}
     */

    final public function withHeader($name, $values) : MessageInterface
    {
        $instance = clone $this;
        unset($instance->headers[$name]);
        $instance->headers[$name] = (array) $values;

        return $instance;
    }

    /**
     * {@inheritdoc}
     */

    final public function withProtocolVersion($version) : MessageInterface
    {
        if (!in_array($version, ['1.0', '1.1', '2.0'])) {
            throw new InvalidArgumentException('The given protocol version is invalid');
        }

        $instance = clone $this;
        $instance->version = $version;

        return $instance;
    }

    /**
     * {@inheritdoc}
     */

    final public function withoutHeader($name) : MessageInterface
    {
        $instance = clone $this;

        if (array_key_exists(strtolower($name), array_change_key_case($instance->headers))) {
            $value = array_change_key_case($instance->headers)[strtolower($name)];

            if (($key = array_search($value, $instance->headers)) !== false) {
                unset($instance->headers[$key]);
            }
        }

        return $instance;
    }
}