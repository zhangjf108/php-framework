<?php
/**
 * @author    Jérémie Broutier <jeremie.broutier@gmail.com>
 * @copyright 2017 Jérémie Broutier
 * @license   https://opensource.org/licenses/MIT MIT License
 * @package   Canephora\Config
 * @version   0.1.0
 */

namespace Kerisy\Config;

interface ConfigInterface extends \Countable
{
    const AS_ARRAY = 1;
    const AS_BOOL = 2;
    const AS_DEFAULT = 3;
    const AS_FLOAT = 4;
    const AS_INT = 5;
    const AS_STRING = 6;

    /**
     * Removes all the parameters from the configuration.
     *
     * @return bool Returns true on success or false on failure.
     */

    public function clear(): bool;

    /**
     * Gets the value of a configs.
     *
     * @param string $key The configs key.
     * @param mixed $default The default value to return if the key is not found.
     * @param int $as_type An optional data type for the return value using AS_* constants. Defaults to the native
     *     configs type.
     *
     * @return mixed Returns the configs value, or the default value if the key is not found.
     */

    public function get(string $key, $default = null, int $as_type = self::AS_DEFAULT);

    /**
     * Checks if a configs exists.
     *
     * @param string $key The configs key.
     *
     * @return bool Returns true if the configs exists, false otherwise.
     */

    public function has(string $key): bool;

    /**
     * Gets all value of the config
     *
     * @return mixed Returns all the config value
     */

    public function all();
}