<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kerisy\Cache\Adapter;

use Kerisy\Cache\Traits\FilesystemTrait;

class FilesystemAdapter extends AbstractAdapter
{
    use FilesystemTrait;

    public function __construct($namespace = '', $defaultLifetime = 0, $directory = null)
    {
        parent::__construct('', $defaultLifetime);
        $this->init($namespace, $directory);
    }
}
