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


use Kerisy\Http\Stream;
use Kerisy\Http\Exception\FileException;
use Psr\Http\Message\StreamInterface;

class StreamFactory implements StreamFactoryInterface
{
    /**
     * {@inheritdoc}
     */

    final public function createStream(string $content = '') : StreamInterface
    {
        $resource = fopen('php://temp', 'r+');
        fwrite($resource, $content);
        rewind($resource);

        return $this->createStreamFromResource($resource);
    }

    /**
     * {@inheritdoc}
     */

    final public function createStreamFromFile(string $filename, string $mode = 'r') : StreamInterface
    {
        if (substr($filename, 0, 6) !== 'php://' and !file_exists($filename)) {
            throw new FileException(sprintf('The file %s does not exists', $filename));
        }

        try {
            $resource = fopen($filename, $mode);
        } catch (\ErrorException $e) {
            throw new FileException(sprintf('The file %s can not be opened', $filename));
        }

        return $this->createStreamFromResource($resource);
    }

    /**
     * {@inheritdoc}
     */
    
    final public function createStreamFromResource($resource) : StreamInterface
    {
        return new Stream($resource);
    }
}