<?php

namespace Kerisy\Log\Target;


use Kerisy\Log\Target;

/**
 * Class SocketTarget
 *
 * @package Kerisy\Log\Target
 */
class SocketTarget extends Target
{
    protected $handler;

    public function getUnderlyingHandler()
    {
        if (!$this->handler) {

        }
        return $this->handler;
    }
}
