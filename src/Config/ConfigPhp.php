<?php
/**
 * 上海葡萄纬度科技有限公司.
 * @copyright Copyright (c) 2017 Shanghai Putao Technology Co., Ltd.
 * @license http://www.putao.com/
 * @author Zhang Jianfeng <zhangjianfeng@putao.com>
 * @Date: 2017/6/27 17:39
 */

namespace Kerisy\Config;


use Kerisy\Config\Exception\FileException;

class ConfigPhp extends Config
{
    /**
     * Loads the configuration from a file.
     *
     * @param string $filename The path of a file containing the configuration data.
     *
     * @throws FileException if the configuration file is not readable.
     */

    final public function load(string $filename) : void
    {
        $this->filename = $filename;

        if (!is_readable($this->filename)) {
            throw new FileException(sprintf('The file %s is not readable', $this->filename));
        }

        $this->configs = require $this->filename;
    }
}