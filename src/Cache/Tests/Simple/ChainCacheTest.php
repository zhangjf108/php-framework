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

use Kerisy\Cache\Simple\ArrayCache;
use Kerisy\Cache\Simple\ChainCache;
use Kerisy\Cache\Simple\FilesystemCache;

/**
 * @group time-sensitive
 */
class ChainCacheTest extends CacheTestCase
{
    public function createSimpleCache($defaultLifetime = 0)
    {
        return new ChainCache(array(new ArrayCache($defaultLifetime), new FilesystemCache('', $defaultLifetime)), $defaultLifetime);
    }

    /**
     * @expectedException \Kerisy\Cache\Exception\InvalidArgumentException
     * @expectedExceptionMessage At least one cache must be specified.
     */
    public function testEmptyCachesException()
    {
        new ChainCache(array());
    }

    /**
     * @expectedException \Kerisy\Cache\Exception\InvalidArgumentException
     * @expectedExceptionMessage The class "stdClass" does not implement
     */
    public function testInvalidCacheException()
    {
        new Chaincache(array(new \stdClass()));
    }
}
