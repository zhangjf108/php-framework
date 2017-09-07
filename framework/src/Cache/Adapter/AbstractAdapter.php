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

use Kerisy\Core\Component;
use Psr\Cache\CacheItemInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Kerisy\Cache\CacheItem;
use Kerisy\Cache\Exception\InvalidArgumentException;
use Kerisy\Cache\Traits\AbstractTrait;

/**
 * @author Nicolas Grekas <p@tchwork.com>
 */
abstract class AbstractAdapter extends Component implements AdapterInterface, LoggerAwareInterface
{
    use AbstractTrait;

    public $defaultLifetime = 0;

    public $namespace = 'kerisy';

    public $logComponent = 'log';

    private $createCacheItem;
    private $mergeByLifetime;

    public function __construct(array $config = [])
    {
        parent::__construct($config);

        if (null !== $this->maxIdLength && strlen($this->namespace) > $this->maxIdLength - 24) {
            throw new InvalidArgumentException(sprintf('Namespace must be %d chars max, %d given ("%s")', $this->maxIdLength - 24, strlen($namespace), $namespace));
        }

        $this->setLogger(\Kerisy::$app->get($this->logComponent));

        $defaultLifetime = $this->defaultLifetime;

        $this->createCacheItem = \Closure::bind(
            function ($key, $value, $isHit) use ($defaultLifetime) {
                $item = new CacheItem();
                $item->key = $key;
                $item->value = $value;
                $item->isHit = $isHit;
                $item->defaultLifetime = $defaultLifetime;

                return $item;
            },
            null,
            CacheItem::class
        );
        $this->mergeByLifetime = \Closure::bind(
            function ($deferred, $namespace, &$expiredIds) {
                $byLifetime = array();
                $now = time();
                $expiredIds = array();

                foreach ($deferred as $key => $item) {
                    if (null === $item->expiry) {
                        $byLifetime[0 < $item->defaultLifetime ? $item->defaultLifetime : 0][$namespace.$key] = $item->value;
                    } elseif ($item->expiry > $now) {
                        $byLifetime[$item->expiry - $now][$namespace.$key] = $item->value;
                    } else {
                        $expiredIds[] = $namespace.$key;
                    }
                }

                return $byLifetime;
            },
            null,
            CacheItem::class
        );
    }

    public static function createConnection($dsn, array $options = array())
    {
        if (!is_string($dsn)) {
            throw new InvalidArgumentException(sprintf('The %s() method expect argument #1 to be string, %s given.', __METHOD__, gettype($dsn)));
        }
        if (0 === strpos($dsn, 'redis://')) {
            return RedisAdapter::createConnection($dsn, $options);
        }
        if (0 === strpos($dsn, 'memcached://')) {
            return MemcachedAdapter::createConnection($dsn, $options);
        }

        throw new InvalidArgumentException(sprintf('Unsupported DSN: %s.', $dsn));
    }

    /**
     * {@inheritdoc}
     */
    public function getItem($key)
    {
        if ($this->deferred) {
            $this->commit();
        }
        $id = $this->getId($key);

        $f = $this->createCacheItem;
        $isHit = false;
        $value = null;

        try {
            foreach ($this->doFetch(array($id)) as $value) {
                $isHit = true;
            }
        } catch (\Exception $e) {
            CacheItem::log($this->logger, 'Failed to fetch key "{key}"', array('key' => $key, 'exception' => $e));
        }

        return $f($key, $value, $isHit);
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(array $keys = array())
    {
        if ($this->deferred) {
            $this->commit();
        }
        $ids = array();

        foreach ($keys as $key) {
            $ids[] = $this->getId($key);
        }
        try {
            $items = $this->doFetch($ids);
        } catch (\Exception $e) {
            CacheItem::log($this->logger, 'Failed to fetch requested items', array('keys' => $keys, 'exception' => $e));
            $items = array();
        }
        $ids = array_combine($ids, $keys);

        return $this->generateItems($items, $ids);
    }

    /**
     * {@inheritdoc}
     */
    public function save(CacheItemInterface $item)
    {
        if (!$item instanceof CacheItem) {
            return false;
        }
        $this->deferred[$item->getKey()] = $item;

        return $this->commit();
    }

    /**
     * {@inheritdoc}
     */
    public function saveDeferred(CacheItemInterface $item)
    {
        if (!$item instanceof CacheItem) {
            return false;
        }
        $this->deferred[$item->getKey()] = $item;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        $ok = true;
        $byLifetime = $this->mergeByLifetime;
        $byLifetime = $byLifetime($this->deferred, $this->namespace, $expiredIds);
        $retry = $this->deferred = array();

        if ($expiredIds) {
            $this->doDelete($expiredIds);
        }
        foreach ($byLifetime as $lifetime => $values) {
            try {
                $e = $this->doSave($values, $lifetime);
            } catch (\Exception $e) {
            }
            if (true === $e || array() === $e) {
                continue;
            }
            if (is_array($e) || 1 === count($values)) {
                foreach (is_array($e) ? $e : array_keys($values) as $id) {
                    $ok = false;
                    $v = $values[$id];
                    $type = is_object($v) ? get_class($v) : gettype($v);
                    CacheItem::log($this->logger, 'Failed to save key "{key}" ({type})', array('key' => substr($id, strlen($this->namespace)), 'type' => $type, 'exception' => $e instanceof \Exception ? $e : null));
                }
            } else {
                foreach ($values as $id => $v) {
                    $retry[$lifetime][] = $id;
                }
            }
        }

        // When bulk-save failed, retry each item individually
        foreach ($retry as $lifetime => $ids) {
            foreach ($ids as $id) {
                try {
                    $v = $byLifetime[$lifetime][$id];
                    $e = $this->doSave(array($id => $v), $lifetime);
                } catch (\Exception $e) {
                }
                if (true === $e || array() === $e) {
                    continue;
                }
                $ok = false;
                $type = is_object($v) ? get_class($v) : gettype($v);
                CacheItem::log($this->logger, 'Failed to save key "{key}" ({type})', array('key' => substr($id, strlen($this->namespace)), 'type' => $type, 'exception' => $e instanceof \Exception ? $e : null));
            }
        }

        return $ok;
    }

    public function __destruct()
    {
        if ($this->deferred) {
            $this->commit();
        }
    }

    private function generateItems($items, &$keys)
    {
        $f = $this->createCacheItem;

        try {
            foreach ($items as $id => $value) {
                if (!isset($keys[$id])) {
                    $id = key($keys);
                }
                $key = $keys[$id];
                unset($keys[$id]);
                yield $key => $f($key, $value, true);
            }
        } catch (\Exception $e) {
            CacheItem::log($this->logger, 'Failed to fetch requested items', array('keys' => array_values($keys), 'exception' => $e));
        }

        foreach ($keys as $key) {
            yield $key => $f($key, null, false);
        }
    }
}