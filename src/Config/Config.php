<?php
/**
 * @author    Jérémie Broutier <jeremie.broutier@gmail.com>
 * @copyright 2017 Jérémie Broutier
 * @license   https://opensource.org/licenses/MIT MIT License
 * @package   Canephora\Config
 * @version   0.1.0
 */

namespace Kerisy\Config;

abstract class Config implements ConfigInterface
{
    /**
     * @var string|null
     */
    
    protected $filename = null;

    /**
     * @var array
     */
    
    protected $configs = [];
    
    /**
     * {@inheritdoc}
     */

    final public function clear() : bool
    {
        $this->configs = [];
    }

    /**
     * Gets the number of configs in the configuration.
     *
     * @return int The number of configs.
     */

    final public function count() : int
    {
        return count($this->configs, COUNT_RECURSIVE);
    }

    /**
     * {@inheritdoc}
     */

    final public function get(string $key, $default = null, int $as_type = self::AS_DEFAULT)
    {
        $currentArray = $this->configs;
        $returnValue = $default;
        $keyParts = explode('.', $key);

        foreach ($keyParts as $index => $value) {
            if (!array_key_exists($value, $currentArray)) {
                break;
            }

            if (is_array($currentArray[$value]) and $index < sizeof($keyParts) - 1) {
                $currentArray = $currentArray[$value];
                continue;
            }

            $returnValue = $currentArray[$value];
        }

        switch ($as_type) {
            case self::AS_ARRAY:
                return (array) $returnValue;
                break;

            case self::AS_BOOL:
                return boolval($returnValue);
                break;

            case self::AS_FLOAT:
                return floatval($returnValue);
                break;

            case self::AS_INT:
                return intval($returnValue);
                break;

            case self::AS_STRING:
                if (is_array($returnValue)) {
                    return implode(',', $returnValue);
                }

                return (string) $returnValue;
                break;

            default:
                return $returnValue;
                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    
    final public function has(string $key) : bool
    {
        $currentArray = $this->configs;

        foreach (explode('.', $key) as $key) {
            if (!array_key_exists($key, $currentArray)) {
                return false;
            }

            if (is_array($currentArray[$key])) {
                $currentArray = $currentArray[$key];
            }
        }

        return true;
    }

    final public function all()
    {
        return $this->configs;
    }
}