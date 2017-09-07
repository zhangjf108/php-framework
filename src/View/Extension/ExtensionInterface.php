<?php

namespace Kerisy\View\Extension;

use Kerisy\View\Engine;

/**
 * A common interface for extensions.
 */
interface ExtensionInterface
{
    public function register(Engine $engine);
}
