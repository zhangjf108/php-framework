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

use Kerisy\Cache\Simple\RedisCache;

class RedisCacheTest extends AbstractRedisCacheTest
{
    public static function setupBeforeClass()
    {
        parent::setupBeforeClass();
        self::$redis = RedisCache::createConnection('redis://'.getenv('REDIS_HOST'));
    }

    public function testCreateConnection()
    {
        $redisHost = getenv('REDIS_HOST');

        $redis = RedisCache::createConnection('redis://'.$redisHost);
        $this->assertInstanceOf(\Redis::class, $redis);
        $this->assertTrue($redis->isConnected());
        $this->assertSame(0, $redis->getDbNum());

        $redis = RedisCache::createConnection('redis://'.$redisHost.'/2');
        $this->assertSame(2, $redis->getDbNum());

        $redis = RedisCache::createConnection('redis://'.$redisHost, array('timeout' => 3));
        $this->assertEquals(3, $redis->getTimeout());

        $redis = RedisCache::createConnection('redis://'.$redisHost.'?timeout=4');
        $this->assertEquals(4, $redis->getTimeout());

        $redis = RedisCache::createConnection('redis://'.$redisHost, array('read_timeout' => 5));
        $this->assertEquals(5, $redis->getReadTimeout());
    }

    /**
     * @dataProvider provideFailedCreateConnection
     * @expectedException \Kerisy\Cache\Exception\InvalidArgumentException
     * @expectedExceptionMessage Redis connection failed
     */
    public function testFailedCreateConnection($dsn)
    {
        RedisCache::createConnection($dsn);
    }

    public function provideFailedCreateConnection()
    {
        return array(
            array('redis://localhost:1234'),
            array('redis://foo@localhost'),
            array('redis://localhost/123'),
        );
    }

    /**
     * @dataProvider provideInvalidCreateConnection
     * @expectedException \Kerisy\Cache\Exception\InvalidArgumentException
     * @expectedExceptionMessage Invalid Redis DSN
     */
    public function testInvalidCreateConnection($dsn)
    {
        RedisCache::createConnection($dsn);
    }

    public function provideInvalidCreateConnection()
    {
        return array(
            array('foo://localhost'),
            array('redis://'),
        );
    }
}
