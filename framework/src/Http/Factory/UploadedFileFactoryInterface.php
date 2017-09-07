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


use Psr\Http\Message\UploadedFileInterface;

interface UploadedFileFactoryInterface
{
    /**
     * Creates a new uploaded file.
     *
     * @see http://php.net/manual/en/features.file-upload.errors.php for a list of error constants.
     *
     * @param string      $filename        The file path.
     * @param int|null    $size            An optional file size.
     * @param int         $error           An optional error associated with the file, using the PHP UPLOAD_ERR_* constants.
     * @param string|null $client_filename An optional file name sent by the client.
     * @param string|null $client_filetype An optional file type sent by the client.
     *
     * @return UploadedFileInterface The uploaded file.
     */
    
    public function createUploadedFile(string $filename, int $size = null, int $error = UPLOAD_ERR_OK,
        string $client_filename = null, string $client_filetype = null) : UploadedFileInterface;
}