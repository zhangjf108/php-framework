<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kerisy\Cache\Tests\Simple;

use Kerisy\Cache\Adapter\FilesystemAdapter;
use Kerisy\Cache\Simple\Psr6Cache;

/**
 * @group time-sensitive
 */
class Psr6CacheTest extends CacheTestCase
{
    public function createSimpleCache($defaultLifetime = 0)
    {
        return new Psr6Cache(new FilesystemAdapter('', $defaultLifetime));
    }
}
