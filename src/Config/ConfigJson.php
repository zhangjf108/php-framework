<?php
/**
 * @author    Jérémie Broutier <jeremie.broutier@gmail.com>
 * @copyright 2017 Jérémie Broutier
 * @license   https://opensource.org/licenses/MIT MIT License
 * @package   Canephora\Config
 * @version   0.1.0
 */

namespace Kerisy\Config;

use Kerisy\Config\Exception\DepthException;
use Kerisy\Config\Exception\FileException;
use Kerisy\Config\Exception\SyntaxErrorException;
use Kerisy\Config\Exception\UnsupportedValueException;

class ConfigJson extends Config
{
    /**
     * Loads the configuration from a file.
     *
     * @param string $filename The path of a file containing the configuration data.
     *
     * @throws DepthException if the maximum depth has been exceeded.
     * @throws FileException if the configuration file is not readable.
     * @throws SyntaxErrorException if the configuration contains syntax errors.
     */

    final public function load(string $filename) : void
    {
        $this->filename = $filename;

        if (!is_readable($this->filename)) {
            throw new FileException(sprintf('The file %s is not readable', $this->filename));
        }
            
        $config = file_get_contents($this->filename);
        $this->loadString($config);
    }

    /**
     * Loads the configuration from a string.
     *
     * @param string $config A string containing the configuration data.
     *
     * @throws DepthException if the maximum depth has been exceeded.
     * @throws SyntaxErrorException if the configuration contains syntax errors.
     */

    final public function loadString(string $config)
    {
        $array = json_decode($config, true, 512, JSON_BIGINT_AS_STRING);

        switch (json_last_error()) {
            case JSON_ERROR_DEPTH:
                throw new DepthException('The maximum depth has been exceeded');
                break;

            case JSON_ERROR_STATE_MISMATCH:
                throw new SyntaxErrorException('The configuration contains malformed or invalid JSON data');
                break;

            case JSON_ERROR_CTRL_CHAR:
                throw new SyntaxErrorException('The configuration contains unexpected control characters');
                break;

            case JSON_ERROR_SYNTAX:
                throw new SyntaxErrorException('The configuration contains one or more syntax errors');
                break;

            case JSON_ERROR_UTF8:
                throw new SyntaxErrorException('The configuration contains malformed UTF-8 characters');
                break;

            case JSON_ERROR_UTF16:
                throw new SyntaxErrorException('The configuration contains malformed UTF-16 characters');
                break;

            default:
                break;
        }
        
        $this->configs = (array) $array;
    }

    /**
     * Saves the configuration to a file.
     *
     * @param string $filename The path of the destination file. The file is created if it does not exists.
     *
     * @throws DepthException if the maximum depth has been exceeded.
     * @throws FileException if the configuration file is not writable or can not be created.
     * @throws SyntaxErrorException if the configuration contains syntax errors.
     * @throws UnsupportedValueException if the configuration contains unsupported values.
     *
     * @return bool Returns true on success or false on failure.
     */

    final public function toFile(string $filename) : bool
    {
        if (file_exists($filename) and !is_writable($filename)) {
            throw new FileException(sprintf('The file %s is not writable', $filename));
        }

        if (!is_writable(dirname($filename))) {
            throw new FileException(sprintf('The file %s can not be created', $filename));
        }

        $json = json_encode($this->configs, JSON_PRESERVE_ZERO_FRACTION | JSON_PRETTY_PRINT, 512);

        switch (json_last_error()) {
            case JSON_ERROR_DEPTH:
                throw new DepthException('The maximum depth has been exceeded');
                break;

            case JSON_ERROR_STATE_MISMATCH:
                throw new SyntaxErrorException('The configuration contains malformed or invalid JSON data');
                break;

            case JSON_ERROR_CTRL_CHAR:
                throw new SyntaxErrorException('The configuration contains unexpected control characters');
                break;

            case JSON_ERROR_SYNTAX:
                throw new SyntaxErrorException('The configuration contains one or more syntax errors');
                break;

            case JSON_ERROR_INF_OR_NAN:
                throw new UnsupportedValueException('The configuration contains one or more INF or NAN values');
                break;

            case JSON_ERROR_UNSUPPORTED_TYPE:
                throw new UnsupportedValueException('The configuration contains one or more unsupported data types');
                break;

            case JSON_ERROR_INVALID_PROPERTY_NAME:
                throw new UnsupportedValueException('The configuration contains one or more invalid property names');
                break;

            case JSON_ERROR_UTF8:
                throw new SyntaxErrorException('The configuration contains malformed UTF-8 characters');
                break;

            case JSON_ERROR_UTF16:
                throw new SyntaxErrorException('The configuration contains malformed UTF-16 characters');
                break;

            default:
                break;
        }
            
        return file_put_contents($filename, $json);
    }
}