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

use Psr\Http\Message\UriInterface;
use Kerisy\Http\Exception\InvalidArgumentException;

interface UriFactoryInterface
{
    /**
     * Creates a new URI.
     *
     * @param string $uri A string representing an URI. Defaults to an empty string.
     *
     * @throws InvalidArgumentException if the given URI can not be parsed.
     *
     * @return UriInterface The URI.
     */
    
    public function createUri(string $uri = '') : UriInterface;
}