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
use Psr\Http\Message\UriInterface;
use Kerisy\Http\Exception\RuntimeException;
use Kerisy\Http\Exception\InvalidArgumentException;

interface ServerRequestFactoryInterface
{
    /**
     * Creates a new server-side request.
     *
     * @param string              $method The request method.
     * @param UriInterface|string $uri    An UriInterface instance or a string representing an URI.
     *
     * @throws InvalidArgumentException if the given URI can not be parsed.
     *
     * @return ServerRequestInterface The server-side request.
     */
    
    public function createServerRequest(string $method, $uri) : ServerRequestInterface;

    /**
     * Creates a new server-side request from the $_SERVER variable.
     *
     * @param array $server The $_SERVER variable (or similar structure).
     *
     * @throws RuntimeException if an attribute of the request can not be determined.
     *
     * @return ServerRequestInterface The server-side request.
     */
    
    public function createServerRequestFromArray(array $server) : ServerRequestInterface;
}