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

use Kerisy\Cache\Traits\MemcachedTrait;

class MemcachedCache extends AbstractCache
{
    use MemcachedTrait;

    public $servers;

    public $options = [];

    protected $maxIdLength = 250;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $client = static::createConnection($this->servers, $this->options);
        $this->initialize($client);
    }
}
