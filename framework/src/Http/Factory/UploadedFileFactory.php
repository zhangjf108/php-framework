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

use Kerisy\Http\UploadedFile;
use Psr\Http\Message\UploadedFileInterface;

class UploadedFileFactory implements UploadedFileFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    
    final public function createUploadedFile(string $filename, int $size = null, int $error = UPLOAD_ERR_OK,
        string $clientFilename = null, string $clientFiletype = null) : UploadedFileInterface
    {
        return new UploadedFile($filename, $size, $error, $clientFilename, $clientFiletype);
    }
}