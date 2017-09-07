<?php

namespace Kerisy\Log\Target;

use Kerisy\Log\Target;
use Monolog\Handler\StreamHandler;

/**
 * Class StreamTarget
 *
 * @package Kerisy\Log\Target
 */
class StreamTarget extends Target
{
    /**
     * The stream to logging into.
     *
     * @var resource|string
     */
    public $stream;

    protected $handler;

    public function getUnderlyingHandler()
    {
        if (!$this->handler) {
            $this->handler = new StreamHandler($this->stream, $this->level, true, null, true);
        }
        return $this->handler;
    }
}
