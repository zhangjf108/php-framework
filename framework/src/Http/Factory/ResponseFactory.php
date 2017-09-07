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

use Psr\Http\Message\ResponseInterface;
use Kerisy\Http\Response;
use Kerisy\Http\Utils\HttpStatus;

class ResponseFactory implements ResponseFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    
    final public function createResponse(int $statusCode = 200) : ResponseInterface
    {
        $reasonPhrase = HttpStatus::getReasonPhrase($statusCode);
        $body = (new StreamFactory)->createStream();

        return new Response($statusCode, $reasonPhrase, [], $body, '1.1');
    }
}