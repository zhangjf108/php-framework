<?php
/**
 * 上海葡萄纬度科技有限公司.
 * @copyright Copyright (c) 2017 Shanghai Putao Technology Co., Ltd.
 * @license http://www.putao.com/
 * @author Zhang Jianfeng <zhangjianfeng@putao.com>
 * @Date: 2017/7/3 11:06
 */

namespace Kerisy\Session;


use Kerisy\Core\Component;


class Session extends Component implements SessionInterface
{
    /**
     * @var SessionHandler
     */
    private $handler;

    /**
     * @var string|array
     */
    public $handle;

    /**
     * @var string The session name
     */
    public $name;

    /**
     * @var int
     */
    public $expire = 1440;

    public function init()
    {
        $this->handler = new SessionHandler($this->handle, $this->expire);
        parent::init();
    }

    /**
     * Generate session id
     *
     * @param int $len
     * @return string
     */
    public function generateSessionId(int $len = 26)
    {
        $time = microtime(true);
        $rand = mt_rand(100000, 999999);
        $sessionId = md5(md5($time) . md5($rand) . uniqid());
        $sessionId = substr($sessionId, 0, $len);
        return $sessionId;
    }

    /**
     * Stores a given value in the session
     *
     * @param string $sessionId
     * @param string $key
     * @param int|bool|string|float|array|object|\JsonSerializable $value allows any nested combination of the previous
     * @return bool
     */
    final public function set(string $sessionId, string $key, $value): bool
    {
        $session = $this->readFromHandler($sessionId);
        $session[$key] = $value;
        return $this->writeToHandler($sessionId, $session);
    }

    /**
     * Retrieves a value from the session - if the value doesn't exist, then it uses the given $default, but transformed
     * into a immutable and safely manipulated scalar or array
     *
     * @param string $sessionId
     * @param string $key
     * @param int|bool|string|float|array|object|\JsonSerializable $default
     * @return int|bool|string|float|array
     */
    final public function get(string $sessionId, string $key, $default = null)
    {
        $session = $this->readFromHandler($sessionId);
        return isset($session[$key]) ? $session[$key] : $default;
    }

    /**
     * Return session data
     *
     * @param string $sessionId
     * @return array
     */
    final public function all(string $sessionId)
    {
        return $this->readFromHandler($sessionId);
    }

    /**
     * Removes an item from the session
     *
     * @param string $sessionId
     * @param string $key
     * @return bool
     */
    final public function remove(string $sessionId, string $key): bool
    {
        $session = $this->readFromHandler($sessionId);
        if (isset($session[$key])) {
            unset($session[$key]);
            return $this->writeToHandler($sessionId, $session);
        }
        return true;
    }

    /**
     * Clears the contents of the session
     *
     * @param string $sessionId
     * @return bool
     */
    final public function clear(string $sessionId): bool
    {
        return $this->handler->destroy($sessionId);
    }

    /**
     * Checks whether a given key exists in the session
     *
     * @param string $sessionId
     * @param string $key
     * @return bool
     */
    final public function has(string $sessionId, string $key): bool
    {
        $session = $this->readFromHandler($sessionId);
        return isset($session[$key]) ? true : false;
    }

    /**
     * Checks whether the session contains any data
     *
     * @param string $sessionId
     * @return bool
     */
    final public function isEmpty(string $sessionId): bool
    {
        $session = $this->readFromHandler($sessionId);
        return empty($session) ? true : false;
    }

    /**
     * Initialize session
     *
     * @param string|null $sessionId
     * @param array $sessionData
     * @return bool
     */
    public function initialize(string $sessionId = null, array $sessionData = []): bool
    {
        if (!$sessionId) {
            $sessionId = $this->generateSessionId();
        }
        return $this->writeToHandler($sessionId, $sessionData);
    }

    /**
     * Read session data
     *
     * @param string $sessionId
     * @return array
     */
    private function readFromHandler(string $sessionId)
    {
        $session = $this->handler->read($sessionId);
        return $session ? json_decode($session, true) : [];
    }

    /**
     * Write session data to handler
     *
     * @param string $sessionId
     * @param array $session
     * @return bool
     */
    private function writeToHandler(string $sessionId, array $session)
    {
        return $this->handler->write($sessionId, json_encode($session, JSON_UNESCAPED_UNICODE));
    }
}