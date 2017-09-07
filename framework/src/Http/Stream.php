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
use Kerisy\Http\Exception\InvalidArgumentException;
use Kerisy\Http\Exception\RuntimeException;
use Kerisy\Http\Exception\StreamException;

class Stream implements StreamInterface
{
    /**
     * @var array
     */
    
    private $metadata = [];

    /**
     * @var resource|null
     */
    
    private $resource = null;

    /**
     * @var int|null
     */
    
    private $size = null;

    /**
     * Creates a Stream instance.
     *
     * @param resource $resource The corresponding PHP resource.
     *
     * @throws InvalidArgumentException if the given resource is not a valid stream resource.
     */
    
    public function __construct($resource)
    {
        if (!is_resource($resource)) {
            throw new InvalidArgumentException('The given resource is not a valid stream resource');
        }

        $this->resource = $resource;
        $this->metadata = stream_get_meta_data($this->resource);

        if (array_key_exists('size', (array) fstat($this->resource))) {
            $this->size = fstat($this->resource)['size'];
        }
    }

    /**
     * Destructs the instance.
     */

    final public function __destruct()
    {
        $this->close();
    }

    /**
     * {@inheritdoc}
     */
    
    final public function __toString() : string
    {
        try {
            return $this->getContents();
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * {@inheritdoc}
     */

    final public function close() : void
    {
        if (is_resource($this->resource)) {
            fclose($this->resource);
        }

        $this->detach();
    }

    /**
     * {@inheritdoc}
     */

    final public function detach()
    {
        if (is_null($this->resource)) {
            return null;
        }

        $resource = $this->resource;
        $this->metadata = [];
        $this->resource = null;
        $this->size = null;

        return $resource;
    }

    /**
     * {@inheritdoc}
     */
    
    final public function eof() : bool
    {
        return (is_null($this->resource) or feof($this->resource));
    }

    /**
     * {@inheritdoc}
     */
    
    final public function getContents() : string
    {
        if (!$this->isReadable()) {
            throw new StreamException('The stream is not readable');
        }

        $this->rewind();
        $content = stream_get_contents($this->resource);

        if ($content === false) {
            throw new RuntimeException('An error occured while reading the data');
        }

        return $content;
    }

    /**
     * {@inheritdoc}
     */
    
    final public function getMetadata($key = null)
    {
        if (is_null($key)) {
            return $this->metadata;
        }

        if (!is_null($key) and array_key_exists($key, $this->metadata)) {
            return $this->metadata[$key];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    
    final public function getSize() : ?int
    {
        if (!is_resource($this->resource)) {
            return null;
        }

        if (!is_null($this->getMetadata('uri')) and array_key_exists('size', fstat($this->resource))) {
            $this->size = fstat($this->resource)['size'];
        }

        return $this->size;
    }

    /**
     * {@inheritdoc}
     */
    
    final public function isReadable() : bool
    {
        if (is_null($this->getMetadata('mode'))) {
            return false;
        }

        $readable_modes = ['a+', 'c+', 'r', 'r+', 'w+', 'x+'];

        return (in_array(str_replace(['b', 't'], '', $this->getMetadata('mode')), $readable_modes));
    }

    /**
     * {@inheritdoc}
     */
    
    final public function isSeekable() : bool
    {
        if (is_null($this->getMetadata('seekable'))) {
            return false;
        }

        return $this->getMetadata('seekable');
    }

    /**
     * {@inheritdoc}
     */
    
    final public function isWritable() : bool
    {
        if (is_null($this->getMetadata('mode'))) {
            return false;
        }

        $writable_modes = ['a', 'a+', 'c', 'c+', 'r+', 'w', 'w+' ,'x', 'x+'];

        return (in_array(str_replace(['b', 't'], '', $this->getMetadata('mode')), $writable_modes));
    }

    /**
     * {@inheritdoc}
     */
    
    final public function read($length) : string
    {
        if ($length < 0) {
            throw new InvalidArgumentException('The length must be positive');
        }

        if (!$this->isReadable()) {
            throw new StreamException('The stream is not readable');
        }

        $data = fread($this->resource, $length);

        if ($data === false) {
            throw new RuntimeException('An error occured while reading the data');
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    
    final public function rewind() : void
    {
        $this->seek(0);
    }

    /**
     * {@inheritdoc}
     */
    
    final public function seek($offset, $whence = SEEK_SET) : void
    {
        if (!$this->isSeekable()) {
            throw new StreamException('The stream is not seekable');
        }

        if (fseek($this->resource, $offset, $whence) === -1) {
            throw new RuntimeException('An error occured while seeking to the specified position');
        }
    }

    /**
     * {@inheritdoc}
     */
    
    final public function tell() : int
    {
        $position = ftell($this->resource);

        if ($position === false) {
            throw new RuntimeException('An error occured while retrieving the current position');
        }

        return $position;
    }

    /**
     * {@inheritdoc}
     */
    
    final public function write($data) : int
    {
        if (!$this->isWritable()) {
            throw new StreamException('The stream is not writable');
        }

        $written = 0;
        $written = fwrite($this->resource, $data);

        if ($written === false) {
            throw new RuntimeException('An error occured while writing the data');
        }

        $this->size = null;

        return $written;
    }
}