<?php
/**
 * 上海葡萄纬度科技有限公司.
 * @copyright Copyright (c) 2017 Shanghai Putao Technology Co., Ltd.
 * @license   http://www.putao.com/
 * @author    Zhang Jianfeng <zhangjianfeng@putao.com>
 * @date:     2017/6/14 17:52
 * @version   2.0.1
 */

namespace Kerisy\Http;


use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Kerisy\Http\Exception\FileException;
use Kerisy\Http\Exception\InvalidArgumentException;
use Kerisy\Http\Exception\RuntimeException;
use Kerisy\Http\Factory\StreamFactory;

class UploadedFile implements UploadedFileInterface
{
    /**
     * @var string|null
     */
    
    private $clientFilename;

    /**
     * @var string|null
     */
    
    private $clientFiletype;

    /**
     * @var int
     */
    
    private $error;

    /**
     * @var string
     */

    private $filename;

    /**
     * @var bool
     */
    
    private $moved = false;

    /**
     * @var int|null
     */
    
    private $size;

    /**
     * Creates an UploadedFile instance.
     *
     * @see http://php.net/manual/en/features.file-upload.errors.php for a list of error constants.
     *
     * @param string      $filename        The file path.
     * @param int|null    $size            An optional file size.
     * @param int         $error           An optional error associated with the file, using the PHP UPLOAD_ERR_* constants.
     * @param string|null $clientFilename An optional file name sent by the client.
     * @param string|null $clientFiletype An optional file type sent by the client.
     */
    
    public function __construct(string $filename, int $size = null, int $error = UPLOAD_ERR_OK,
        string $clientFilename = null, string $clientFiletype = null)
    {
        $this->filename = $filename;
        $this->size = $size;
        $this->error = $error;
        $this->clientFilename = $clientFilename;
        $this->clientFiletype = $clientFiletype;
    }

    /**
     * {@inheritdoc}
     */

    final public function getClientFilename() : ?string
    {
        return $this->clientFilename;
    }

    /**
     * {@inheritdoc}
     */
    
    final public function getClientMediaType() : ?string
    {
        return $this->clientFiletype;
    }

    /**
     * {@inheritdoc}
     */
    
    final public function getError() : int
    {
        return $this->error;
    }

    /**
     * {@inheritdoc}
     */
    
    final public function getSize() :?int
    {
        return $this->size;
    }

    /**
     * {@inheritdoc}
     */
    
    final public function getStream() : StreamInterface
    {
        if ($this->moved) {
            throw new RuntimeException('The uploaded file was already moved');
        }

        try {
            return (new StreamFactory)->createStreamFromFile($this->filename, 'r');
        } catch (FileException $e) {
            throw new RuntimeException('An error occured during the creation of the stream');
        }
    }

    /**
     * {@inheritdoc}
     */
    
    final public function moveTo($filename) : void
    {
        if ($this->moved) {
            throw new RuntimeException('The uploaded file was already moved');
        }

        if (!is_uploaded_file($this->filename)) {
            throw new RuntimeException('An error occured during the move operation');
        }

        if (!move_uploaded_file($this->filename, $filename)) {
            throw new InvalidArgumentException('The given destination file path is invalid');
        }

        $this->moved = true;
    }
}