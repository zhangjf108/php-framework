<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kerisy\NoSQL\Traits;

use Kerisy\Exception\InvalidParamException;
use Predis\Connection\Factory;

/**
 * @author Aurimas Niekis <aurimas@niekis.lt>
 * @author Nicolas Grekas <p@tchwork.com>
 *
 * @internal
 */
trait RedisTrait
{
    private static $defaultConnectionOptions = array(
        'persistent' => 0,
        'persistent_id' => null,
        'timeout' => 30,
        'read_timeout' => 0,
        'retry_interval' => 0,
    );

    /**
     * @param $redisClient \Redis|\RedisArray|\RedisCluster|\Predis\Client $redisClient
     * @throws InvalidParamException
     */
    public function initialize($redisClient)
    {
        if (preg_match('#[^-+_.A-Za-z0-9]#', $this->prefix, $match)) {
            throw new InvalidParamException(sprintf('Redis prefix contains "%s" but only characters in [-+_.A-Za-z0-9] are allowed.', $match[0]));
        }
        if (!$redisClient instanceof \Redis && !$redisClient instanceof \RedisArray && !$redisClient instanceof \RedisCluster && !$redisClient instanceof \Predis\Client) {
            throw new InvalidParamException(sprintf('%s() expects parameter 1 to be Redis, RedisArray, RedisCluster or Predis\Client, %s given', __METHOD__, is_object($redisClient) ? get_class($redisClient) : gettype($redisClient)));
        }
        $this->redis = $redisClient;
    }

    /**
     * Creates a Redis connection using a DSN configuration.
     *
     * Example DSN:
     *   - redis://localhost
     *   - redis://example.com:1234
     *   - redis://secret@example.com/13
     *   - redis:///var/run/redis.sock
     *   - redis://secret@/var/run/redis.sock/13
     *
     * @param string $dsn
     * @param array $options See self::$defaultConnectionOptions
     *
     * @throws InvalidParamException When the DSN is invalid.
     *
     * @return \Redis|\Predis\Client According to the "class" option
     */
    public static function createConnection($dsn, array $options = array())
    {
        if (0 !== strpos($dsn, 'redis://')) {
            throw new InvalidParamException(sprintf('Invalid Redis DSN: %s does not start with "redis://"', $dsn));
        }
        $params = preg_replace_callback('#^redis://(?:(?:[^:@]*+:)?([^@]*+)@)?#', function ($m) use (&$auth) {
            if (isset($m[1])) {
                $auth = $m[1];
            }

            return 'file://';
        }, $dsn);

        if (false === $params = parse_url($params)) {
            throw new InvalidParamException(sprintf('Invalid Redis DSN: %s', $dsn));
        }
        if (!isset($params['host']) && !isset($params['path'])) {
            throw new InvalidParamException(sprintf('Invalid Redis DSN: %s', $dsn));
        }
        if (isset($params['path']) && preg_match('#/(\d+)$#', $params['path'], $m)) {
            $params['dbindex'] = $m[1];
            $params['path'] = substr($params['path'], 0, -strlen($m[0]));
        }

        $params += array(
            'host' => isset($params['host']) ? $params['host'] : $params['path'],
            'port' => isset($params['host']) ? 6379 : null,
            'dbindex' => 0,
        );

        if (isset($params['query'])) {
            parse_str($params['query'], $query);
            $params += $query;
        }

        $params += $options + self::$defaultConnectionOptions;

        if (extension_loaded('redis')) {
            $connect = $params['persistent'] || $params['persistent_id'] ? 'pconnect' : 'connect';
            $redis = new \Redis();
            @$redis->{$connect}($params['host'], $params['port'], $params['timeout'], $params['persistent_id'], $params['retry_interval']);

            if (@!$redis->isConnected()) {
                $e = ($e = error_get_last()) && preg_match('/^Redis::p?connect\(\): (.*)/', $e['message'], $e) ? sprintf(' (%s)', $e[1]) : '';
                throw new InvalidParamException(sprintf('Redis connection failed%s: %s', $e, $dsn));
            }

            if ((null !== $auth && !$redis->auth($auth))
                || ($params['dbindex'] && !$redis->select($params['dbindex']))
                || ($params['read_timeout'] && !$redis->setOption(\Redis::OPT_READ_TIMEOUT, $params['read_timeout']))
            ) {
                $e = preg_replace('/^ERR /', '', $redis->getLastError());
                throw new InvalidParamException(sprintf('Redis connection failed (%s): %s', $e, $dsn));
            }
        } else {
            throw new InvalidParamException(sprintf('Class "%s" does not exist', \Redis::class));
        }

        return $redis;
    }
}
