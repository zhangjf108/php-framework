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


use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use Kerisy\Http\Exception\InvalidArgumentException;

interface RequestFactoryInterface
{
    /**
     * Creates a new request.
     *
     * @param string              $method The request method.
     * @param UriInterface|string $uri    An UriInterface instance or a string representing an URI.
     *
     * @throws InvalidArgumentException if the given URI is not an instance of UriInterface or can not be parsed.
     *
     * @return RequestInterface The request.
     */
    
    public function createRequest(string $method, $uri) : RequestInterface;
}