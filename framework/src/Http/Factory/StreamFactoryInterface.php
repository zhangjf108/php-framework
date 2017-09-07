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


use Psr\Http\Message\StreamInterface;
use Kerisy\Http\Exception\FileException;
use Kerisy\Http\Exception\RuntimeException;

interface StreamFactoryInterface
{
    /**
     * Creates a new stream from a string.
     *
     * @param string $content The stream content.
     *
     * @throws RuntimeException if the stream can not be created.
     *
     * @return StreamInterface The stream.
     */
    
    public function createStream(string $content = '') : StreamInterface;

    /**
     * Creates a stream from an existing file.
     *
     * @param string $filename The source file.
     * @param string $mode     An optional mode to open the file. Defaults to 'r' (read-only).
     *
     * @throws FileException if the file does not exists or can not be opened.
     *
     * @return StreamInterface The stream.
     */
    
    public function createStreamFromFile(string $filename, string $mode = 'r') : StreamInterface;

    /**
     * Creates a stream from an existing resource.
     *
     * @param resource $resource The resource.
     *
     * @return StreamInterface The stream.
     */
    
    public function createStreamFromResource($resource) : StreamInterface;
}