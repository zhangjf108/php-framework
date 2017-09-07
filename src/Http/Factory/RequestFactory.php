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

use Kerisy\Http\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use Kerisy\Http\Exception\InvalidArgumentException;
use Kerisy\Http\Utils\RequestMethod;

class RequestFactory extends RequestMethod implements RequestFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    
    final public function createRequest(string $method = self::METHOD_GET, $uri) : RequestInterface
    {
        if (!is_string($uri) and !$uri instanceof UriInterface) {
            throw new InvalidArgumentException(sprintf('The URI must be a valid URI string or an instance of %s',
                UriInterface::class));
        }

        if (is_string($uri)) {
            $uri = (new UriFactory)->createUri($uri);
        }

        $body = (new StreamFactory)->createStream();

        return new Request($method, $uri, [], $body, '1.1');
    }
}