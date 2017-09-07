<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kerisy\Cache\Simple;

use Kerisy\Cache\Traits\RedisTrait;

class RedisCache extends AbstractCache
{
    use RedisTrait;

    public $dsn = 'redis://127.0.0.1:6379';

    public $options = [];

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $client = static::createConnection($this->dsn, $this->options);
        $this->initialize($client);
    }
}
