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


use Psr\Http\Message\UriInterface;
use Kerisy\Http\Exception\InvalidArgumentException;

class Uri implements UriInterface
{
    /**
     * @var string
     */
    
    private $fragment = '';

    /**
     * @var string
     */

    private $host = '';

    /**
     * @var string
     */
    
    private $pass = '';

    /**
     * @var string
     */

    private $path = '';

    /**
     * @var int|null
     */
    
    private $port = null;

    /**
     * @var array
     */
    
    private $query = [];

    /**
     * @var string
     */
    
    private $scheme = '';

    /**
     * @var string
     */
    
    private $user = '';

    /**
     * Creates an Uri instance.
     *
     * @param string $uri A string representing an URI. Defaults to an empty string.
     *
     * @throws InvalidArgumentException if the given URI can not be parsed.
     */

    public function __construct(string $uri = '')
    {
        $uriParts = parse_url($uri);

        if ($uriParts === false or empty($uriParts)) {
            throw new InvalidArgumentException('The given URI can not be parsed');
        }

        if (array_key_exists('scheme', $uriParts)) {
            $this->scheme = strtolower($uriParts['scheme']);
        }

        if (array_key_exists('host', $uriParts)) {
            $this->host = strtolower(urldecode($uriParts['host']));
        }

        if (array_key_exists('user', $uriParts)) {
            $this->user = $uriParts['user'];
        }

        if (array_key_exists('pass', $uriParts)) {
            $this->pass = $uriParts['pass'];
        }

        if (array_key_exists('path', $uriParts)) {
            $this->path = urldecode($uriParts['path']);
        }

        if (array_key_exists('port', $uriParts)) {
            $this->port = $uriParts['port'];
        }

        if (array_key_exists('query', $uriParts)) {
            parse_str($uriParts['query'], $this->query);
        }

        if (array_key_exists('fragment', $uriParts)) {
            $this->fragment = urldecode($uriParts['fragment']);
        }
    }

    /**
     * Composes a URI reference string from the given components.
     *
     * @param string $scheme    The URI scheme component.
     * @param string $authority The URI authority component.
     * @param string $path      The URI path component.
     * @param string $query     The URI query component.
     * @param string $fragment  The URI fragment component.
     *
     * @return string The URI reference string.
     */
    
    final public static function composeUri(string $scheme, string $authority, string $path, string $query,
        string $fragment) : string
    {
        $uri = '';

        if (!empty($scheme)) {
            $uri .= $scheme . ':';
        }

        if (!empty($authority) or $scheme === 'file') {
            $uri .= '//' . $authority;
        }

        $uri .= $path; 

        if (!empty($query)) {
            $uri .= '?' . $query;
        }

        if (!empty($fragment)) {
            $uri .= '#' . $fragment;
        }

        return $uri;
    }

    /**
     * Checks if the URI has the default port of the current scheme. 
     *
     * @return bool Returns true if the URI has the default port, false otherwise.
     */
    
    final public function hasDefaultPort() : bool
    {
        $ports = [
            'http'  => 80,
            'https' => 443
        ];

        return (is_null($this->port) or empty($this->scheme) or !array_key_exists($this->scheme, $ports)
            or $ports[$this->scheme] == $this->port);
    }

    /**
     * {@inheritdoc}
     */

    final public function __toString() : string
    {
        return self::composeUri(
            $this->getScheme(),
            $this->getAuthority(),
            $this->getPath(),
            $this->getQuery(),
            $this->getFragment()
        );
    }

    /**
     * {@inheritdoc}
     */
    
    final public function getAuthority() : string
    {
        $authority = $this->getHost();

        if (!empty($this->getUserInfo())) {
            $authority = $this->getUserInfo() . '@' . $authority;
        }

        if (!is_null($this->getPort())) {
            $authority .= ':' . $this->getPort();
        }

        return $authority;
    }
    
    /**
     * {@inheritdoc}
     */
    
    final public function getFragment() : string
    {
        return $this->fragment;
    }

    /**
     * {@inheritdoc}
     */
    
    final public function getHost() : string
    {
        return $this->host;
    }

    /**
     * {@inheritdoc}
     */

    final public function getPath() : string
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     */
    
    final public function getPort() : ?int
    {
        return (!$this->hasDefaultPort()) ? $this->port : null;
    }

    /**
     * {@inheritdoc}
     */
    
    final public function getQuery() : string
    {
        return http_build_query($this->query, null, '&', PHP_QUERY_RFC3986);
    }

    /**
     * {@inheritdoc}
     */

    final public function getScheme() : string
    {
        return $this->scheme;
    }

    /**
     * {@inheritdoc}
     */
    
    final public function getUserInfo() : string
    {
        $user_info = $this->user;

        if (!empty($this->pass)) {
            $user_info .= ':' . $this->pass;
        }

        return $user_info;
    }

    /**
     * {@inheritdoc}
     */
    
    final public function withFragment($fragment) : UriInterface
    {
        $instance = clone $this;
        $instance->fragment = urldecode($fragment);

        return $instance;
    }

    /**
     * {@inheritdoc}
     */

    final public function withHost($host) : UriInterface
    {
        $instance = clone $this;
        $instance->host = strtolower(urldecode($host));

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    
    final public function withPath($path) : UriInterface
    {
        $options = [
            'flags'   => FILTER_FLAG_PATH_REQUIRED,
            'options' => [
                'default' => null
            ]
        ];

        $testUri = Uri::composeUri('http', 'example.com', $path, '', '');

        if (is_null(filter_var($testUri, FILTER_VALIDATE_URL, $options))) {
            throw new InvalidArgumentException('The given path is invalid');
        }

        $instance = clone $this;
        $instance->path = urldecode($path);

        return $instance;
    }

    /**
     * {@inheritdoc}
     */

    final public function withPort($port) : UriInterface
    {
        if ($port < 1 or $port > 65535) {
            throw new InvalidArgumentException('The given port is invalid');
        }

        $instance = clone $this;
        $instance->port = $port;

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    
    final public function withQuery($query) : UriInterface
    {
        $instance = clone $this;
        parse_str($query, $instance->query);

        return $instance;
    }

    /**
     * {@inheritdoc}
     */

    final public function withScheme($scheme) : UriInterface
    {
        if (!in_array($scheme, ['http', 'https'])) {
            throw new InvalidArgumentException('The given scheme is invalid');
        }

        $instance = clone $this;
        $instance->scheme = strtolower($scheme);

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    
    final public function withUserInfo($user, $pass = null) : UriInterface
    {
        $instance = clone $this;
        $instance->user = $user;
        $instance->pass = (string) $pass;

        return $instance;
    }
}