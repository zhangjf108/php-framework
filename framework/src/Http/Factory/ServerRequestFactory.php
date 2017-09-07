<?php
/**
 * 上海葡萄纬度科技有限公司.
 * @copyright Copyright (c) 2017 Shanghai Putao Technology Co., Ltd.
 * @license   http://www.putao.com/
 * @author    Zhang Jianfeng <zhangjianfeng@putao.com>
 * @date:     2017/6/14 17:52
 * @version   2.0.1
 */

namespace Kerisy\Http\Factory;


use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;
use Kerisy\Http\ServerRequest;
use swoole_http_request as SwooleHttpRequest;
use React\Http\Request as ReactHttpRequest;

class ServerRequestFactory implements ServerRequestFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    
    final public function createServerRequest(string $method, $uri) : ServerRequestInterface
    {
        $body = (new StreamFactory)->createStream();

        return new ServerRequest($method, $uri, [], $body, '1.1', []);
    }

    /**
     * {@inheritdoc}
     */
    
    final public function createServerRequestFromArray(array $server) : ServerRequestInterface
    {
        $method  = self::getMethodFromArray($server);
        $uri     = self::getUriFromArray($server);
        $headers = self::getHeadersFromArray($server);
        $version = self::getProtocolVersionFromArray($server);

        $body = (new StreamFactory)->createStream();

        return new ServerRequest($method, $uri, $headers, $body, $version, $server);
    }

    /**
     * Creates a new request from swoole http request.
     *
     * @param SwooleHttpRequest $swooleHttpRequest
     * @return ServerRequestInterface
     */
    final public static function createServerRequestFromSwoole(SwooleHttpRequest $swooleHttpRequest) : ServerRequestInterface
    {
        $get = isset($swooleHttpRequest->get) ? $swooleHttpRequest->get : [];
        $post = isset($swooleHttpRequest->post) ? $swooleHttpRequest->post : [];
        $cookie = isset($swooleHttpRequest->cookie) ? $swooleHttpRequest->cookie : [];
        $files = isset($swooleHttpRequest->files) ? $swooleHttpRequest->files : [];

        $server = isset($swooleHttpRequest->server) ? array_change_key_case($swooleHttpRequest->server, CASE_UPPER) : [];

        if (isset($swooleHttpRequest->header)) {
            foreach ($swooleHttpRequest->header as $key => $value) {
                $newKey = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
                $server[$newKey] = $value;
            }
        }

        $body = (new StreamFactory)->createStream($swooleHttpRequest->rawContent());

        $uploadFiles = self::getFilesFromArray($files);

        return (new ServerRequestFactory)->createServerRequestFromArray($server)
            ->withBody($body)
            ->withCookieParams($cookie)
            ->withQueryParams($get)
            ->withParsedBody($post)
            ->withUploadedFiles($uploadFiles);
    }

    /**
     * Creates a new request from react http request.
     *
     * @param ReactHttpRequest $reactHttpRequest
     * @return ServerRequestInterface
     */
    final public static function createServerRequestFromReact(ReactHttpRequest $reactHttpRequest) : ServerRequestInterface
    {
        $get = $reactHttpRequest->getQuery() ?? [];
        $post = $reactHttpRequest->getPost() ?? [];
        $header = $reactHttpRequest->getHeaders() ?? [];
        $files = $reactHttpRequest->getFiles() ?? [];

        $server = [
            'QUERY_STRING' => http_build_query($reactHttpRequest->getQuery()),
            'REQUEST_METHOD' => $reactHttpRequest->getMethod(),
            'REQUEST_URI' => $reactHttpRequest->getPath(),
            'PATH_INFO' => $reactHttpRequest->getPath(),
            'REQUEST_TIME' => time(),
            'REQUEST_TIME_FLOAT' => microtime(true),
        ];

        foreach ($header as $key => $value) {
            $newKey = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
            $server[$newKey] = $value;
        }

        $cookie = [];

        if (isset($headers['Cookie']) || isset($headers['cookie'])) {
            $headersCookie = explode(';', isset($headers['Cookie']) ? $headers['Cookie'] : $headers['cookie']);
            foreach ($headersCookie as $cookie) {
                list($name, $value) = explode('=', trim($cookie));
                $cookie[$name] = $value;
            }
        }

        $body = (new StreamFactory)->createStream($reactHttpRequest->getBody());

        //$uploadFiles = self::getFilesFromArray($files);
        $uploadFiles = [];

        return (new ServerRequestFactory)->createServerRequestFromArray($server)
            ->withBody($body)
            ->withCookieParams($cookie)
            ->withQueryParams($get)
            ->withParsedBody($post)
            ->withUploadedFiles($uploadFiles);
    }

    /**
     * Creates a new request from the server superglobals.
     *
     * @return ServerRequestInterface The server-side request.
     */
    
    final public static function createServerRequestFromGlobals() : ServerRequestInterface
    {
        $body = (new StreamFactory)->createStreamFromFile('php://input', 'r');
        $files = self::getFilesFromArray($_FILES);

        return (new ServerRequestFactory)->createServerRequestFromArray($_SERVER)
            ->withBody($body)
            ->withCookieParams($_COOKIE)
            ->withQueryParams($_GET)
            ->withParsedBody($_POST)
            ->withUploadedFiles($files);
    }

    /**
     * Retrieves the uploaded files from the $_FILES variable.
     *
     * @param array $files The $_FILES variable (or similar structure).
     *
     * @return UploadedFileInterface[] An array of UploadedFileInterface instances representing the files. Returns an empty
     *     array if no data is present.
     */
    
    final public static function getFilesFromArray(array $files) : array
    {
        $uploadFiles = [];

        foreach ($files as $key => $file) {
            if (isset($file['name'])) { //此file是单个文件
                $uploadFiles[$key] = (new UploadedFileFactory())
                    ->createUploadedFile($file['tmp_name'], $file['size'], $file['error'], $file['name'], $file['type']);
            } else {
                foreach ($file as $f) { //此file是文件数组
                    $uploadFiles[$key][] = (new UploadedFileFactory())
                        ->createUploadedFile($f['tmp_name'], $f['size'], $f['error'], $f['name'], $f['type']);
                }
            }
        }
        
        return $uploadFiles;
    }

    /**
     * Retrieves the request headers from the $_SERVER variable.
     *
     * @param array $server The $_SERVER variable (or similar structure).
     *
     * @return string[][] An array of key/values-array pairs representing the headers. Returns an empty array if no data is
     *     present.
     */
    
    final public static function getHeadersFromArray(array $server) : array
    {
        $headers = [];

        array_walk($server, function($value, $key) use (&$headers) {
            if (substr($key, 0, 5) === 'HTTP_') {
                $name = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));

                if (!array_key_exists($name, $headers)) {
                    $headers[$name] = [];
                }

                if (!in_array($name, ['Cookie', 'Set-Cookie', 'User-Agent'])) {
                    $values = array_map('trim', explode(',', $value));
                    $headers[$name] = array_merge($headers[$name], $values);
                } else {
                    $headers[$name] = [$value];
                }
            }
        });

        return $headers;
    }

    /**
     * Retrieves the request method from the $_SERVER variable.
     *
     * @param array $server The $_SERVER variable (or similar structure).
     *
     * @return string The request method. Defaults to 'GET' if the method is not specified.
     */
    
    final public static function getMethodFromArray(array $server) : string
    {
        if (!array_key_exists('REQUEST_METHOD', $server) or empty($server['REQUEST_METHOD'])) {
            return 'GET';
        }
        
        return $server['REQUEST_METHOD'];
    }

    /**
     * Retrieves the request's HTTP protocol version from the $_SERVER variable.
     *
     * @param array $server The $_SERVER variable (or similar structure).
     *
     * @return string The HTTP protocol version, as a string. Defaults to '1.1' if the version is not specified.
     */
    
    final public static function getProtocolVersionFromArray(array $server) : string
    {
        if (!array_key_exists('SERVER_PROTOCOL', $server) or empty($server['SERVER_PROTOCOL'])) {
            return '1.1';
        }
        
        return str_replace('HTTP/', '', $server['SERVER_PROTOCOL']);
    }

    /**
     * Retrieves the request URI from the $_SERVER variable.
     *
     * @param array $server The $_SERVER variable (or similar structure).
     *
     * @return UriInterface An UriInterface instance representing the request URI.
     */
    
    final public static function getUriFromArray(array $server) : UriInterface
    {
        $requestUri = (array_key_exists('REQUEST_URI', $server)) ? $server['REQUEST_URI'] : '';
        $uri = (new UriFactory)->createUri($requestUri);

        if (array_key_exists('REQUEST_SCHEME', $server)) {
            $uri = $uri->withScheme($server['REQUEST_SCHEME']);
        } elseif (array_key_exists('HTTPS', $server) and boolval($server['HTTPS'])) {
            $uri = $uri->withScheme('https');
        } else {
            $uri = $uri->withScheme('http');
        }
        
        if (array_key_exists('HTTP_HOST', $server)) {
            $uri = $uri->withHost($server['HTTP_HOST']);
        }

        if (array_key_exists('SERVER_PORT', $server)) {
            $uri = $uri->withPort($server['SERVER_PORT']);
        }
        
        return $uri;
    }
}