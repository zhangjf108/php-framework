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
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;
use Kerisy\Http\Exception\InvalidArgumentException;

class ServerRequest extends Request implements ServerRequestInterface
{
    /**
     * @var string[]
     */

    private $attributes = [];

    /**
     * @var string[]
     */

    private $cookies = [];

    /**
     * @var null|array|object
     */

    private $data = null;

    /**
     * @var UploadedFileInterface[]
     */

    private $files = [];

    /**
     * @var string[]
     */

    private $query = [];

    /**
     * @var array
     */

    private $server;

    /**
     * Creates a ServerRequest instance.
     *
     * @param string $method The request method.
     * @param UriInterface $uri The request URI.
     * @param string[][] $headers The request headers.
     * @param StreamInterface $body The request body.
     * @param string $version The HTTP protocol version as a string.
     * @param array $server The $_SERVER variable (or similar structure).
     */

    public function __construct(string $method, UriInterface $uri, array $headers, StreamInterface $body, string $version,
                                array $server)
    {
        parent::__construct($method, $uri, $headers, $body, $version);

        $this->server = $server;
    }

    /**
     * Clones a ServerRequest instance.
     */

    public function __clone()
    {
        $this->uri = clone $this->uri;

        foreach ($this->files as $key => $file) {
            $this->files[$key] = clone $file;
        }

        if (is_object($this->data)) {
            $this->data = clone $this->data;
        }
    }

    /**
     * Retrieves the list of accepted content types from the "Accept" header.
     *
     * @return string[] An array containing the list of content types. Returns an empty array if the header is not found.
     */

    final public function getAcceptedContentTypes(): array
    {
        return $this->getHeader('Accept');
    }

    /**
     * Retrieves the list of accepted encodings from the "Accept-Encoding" header.
     *
     * @return string[] An array containing the list of encodings. Returns an empty array if the header is not found.
     */

    final public function getAcceptedEncodings(): array
    {
        return $this->getHeader('Accept-Encoding');
    }

    /**
     * Retrieves the list of accepted languages from the "Accept-Language" header.
     *
     * @return string[] An array containing the list of languages. Returns an empty array if the header is not found.
     */

    final public function getAcceptedLanguages(): array
    {
        return $this->getHeader('Accept-Language');
    }

    /**
     * {@inheritdoc}
     */

    final public function getAttribute($name, $default = null)
    {
        if (array_key_exists($name, $this->getAttributes())) {
            return $this->getAttributes()[$name];
        }

        return $default;
    }

    /**
     * {@inheritdoc}
     */

    final public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Gets the client remote address.
     *
     * @return string The client address.
     */

    final public function getClientAddress(): string
    {
        return $this->server['REMOTE_ADDR'];
    }

    /**
     * Gets the client remote port.
     *
     * @return int The client port.
     */

    final public function getClientPort(): int
    {
        return intval($this->server['REMOTE_PORT']);
    }

    /**
     * {@inheritdoc}
     */
    final public function getCookieParams(): array
    {
        return $this->cookies;
    }

    /**
     * {@inheritdoc}
     */

    final public function getParsedBody()
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */

    final public function getQueryParams(): array
    {
        return $this->query;
    }

    /**
     * Retrieves the HTTP referer from the "Referer" header.
     *
     * Warning: The "Referer" header is not reliable and sould not be trusted.
     *
     * @return string The HTTP referer. Returns an empty string if the header is not found.
     */

    final public function getReferer(): string
    {
        return $this->getHeaderLine('Referer');
    }

    /**
     * Gets the UNIX timestamp at the beginning of the request.
     *
     * @return int The timestamp.
     */

    final public function getRequestTime(): int
    {
        return intval($this->server['REQUEST_TIME']);
    }

    /**
     * Gets the UNIX timestamp at the beginning of the request, including microseconds.
     *
     * @return float The timestamp.
     */

    final public function getRequestTimeFloat(): float
    {
        return floatval($this->server['REQUEST_TIME_FLOAT']);
    }

    /**
     * {@inheritdoc}
     */

    final public function getServerParams(): array
    {
        return $this->server;
    }

    /**
     * {@inheritdoc}
     */

    final public function getUploadedFiles(): array
    {
        return $this->files;
    }

    /**
     * Get the UploadedFile instance.
     *
     * @param string $name
     * @return UploadedFileInterface|UploadedFileInterface[]
     */
    final public function getUploadedFile(string $name)
    {
        return isset($this->files[$name]) ? $this->files[$name] : null;
    }

    /**
     * Retrieves the user-agent from the "User-Agent" header.
     *
     * Warning: The "User-Agent" header is not reliable and sould not be trusted.
     *
     * @return string The user agent. Returns an empty string if the header is not found.
     */
    
    final public function getUserAgent(): string
    {
        return $this->getHeaderLine('User-Agent');
    }

    /**
     * Checks if the request contains a Do Not Track request.
     *
     * @return bool Returns true if the request contains a Do Not Track request, false otherwise.
     */
    
    final public function hasDntRequest(): bool
    {
        return (!empty($this->getHeaderLine('Dnt') and intval($this->getHeaderLine('Dnt')) == 1));
    }

    /**
     * {@inheritdoc}
     */

    final public function withAttribute($name, $value): ServerRequestInterface
    {
        $instance = clone $this;
        unset($instance->attributes[$name]);
        $instance->attributes[$name] = $value;

        return $instance;
    }

    /**
     * {@inheritdoc}
     */

    final public function withCookieParams(array $cookies): ServerRequestInterface
    {
        $instance = clone $this;
        $instance->cookies = $cookies;

        return $instance;
    }

    /**
     * {@inheritdoc}
     */

    final public function withParsedBody($data): ServerRequestInterface
    {
        if (!in_array(gettype($data), ['NULL', 'array', 'object'])) {
            throw new InvalidArgumentException('The data must be an array, an object or a null value');
        }

        $instance = clone $this;
        $instance->data = $data;

        return $instance;
    }

    /**
     * {@inheritdoc}
     */

    final public function withQueryParams(array $query): ServerRequestInterface
    {
        $instance = clone $this;
        $instance->query = $query;

        return $instance;
    }

    /**
     * {@inheritdoc}
     */

    final public function withUploadedFiles(array $files): ServerRequestInterface
    {
        $instance = clone $this;
        $instance->files = $files;

        return $instance;
    }

    /**
     * {@inheritdoc}
     */

    final public function withoutAttribute($name): ServerRequestInterface
    {
        $instance = clone $this;
        unset($instance->attributes[$name]);

        return $instance;
    }
}