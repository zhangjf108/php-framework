<?php
/**
 * @author    Jérémie Broutier <jeremie.broutier@gmail.com>
 * @copyright 2017 Jérémie Broutier
 * @license   https://opensource.org/licenses/MIT MIT License
 * @package   Canephora\Config\Traits
 * @version   0.1.0
 */

namespace Kerisy\Config\Traits;

use Kerisy\Config\ConfigInterface;

interface ConfigAwareTrait
{
    /**
     * Gets the configuration.
     *
     * @return ConfigInterface The configuration.
     */
    
    public function getConfig() : ConfigInterface;
}