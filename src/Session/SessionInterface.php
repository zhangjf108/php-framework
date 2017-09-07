<?php
/**
 * 上海葡萄纬度科技有限公司.
 * @copyright Copyright (c) 2017 Shanghai Putao Technology Co., Ltd.
 * @license http://www.putao.com/
 * @author Zhang Jianfeng <zhangjianfeng@putao.com>
 * @date: 2017/7/12 01:05
 * @version: 3.0
 */

namespace Kerisy\Session;


interface SessionInterface
{
    /**
     * Stores a given value in the session
     *
     * @param string $sessionId
     * @param string $key
     * @param int|bool|string|float|array|object $value allows any nested combination of the previous
     * @return bool
     */
    public function set(string $sessionId, string $key, $value): bool;

    /**
     * Retrieves a value from the session - if the value doesn't exist, then it uses the given $default, but transformed
     * into a immutable and safely manipulated scalar or array
     *
     * @param string $sessionId
     * @param string $key
     * @param int|bool|string|float|array|object $default
     * @return int|bool|string|float|array
     */
    public function get(string $sessionId, string $key, $default = null);

    /**
     * Removes an item from the session
     *
     * @param string $sessionId
     * @param string $key
     *
     * @return bool
     */
    public function remove(string $sessionId, string $key): bool;

    /**
     * Clears the contents of the session
     *
     * @param string $sessionId
     * @return bool
     */
    public function clear(string $sessionId): bool;

    /**
     * Checks whether a given key exists in the session
     *
     * @param string $sessionId
     * @param string $key
     * @return bool
     */
    public function has(string $sessionId, string $key): bool;

    /**
     * Checks whether the session contains any data
     *
     * @param string $sessionId
     * @return bool
     */
    public function isEmpty(string $sessionId): bool;
}