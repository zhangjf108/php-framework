<?php
/**
 * 上海葡萄纬度科技有限公司.
 * @copyright Copyright (c) 2017 Shanghai Putao Technology Co., Ltd.
 * @license http://www.putao.com/
 * @author Zhang Jianfeng <zhangjianfeng@putao.com>
 * @date: 2017/6/26 14:41
 * @version 2.0.3
 */

namespace Kerisy\Core;


class Context
{
    private $map = [];

    public function __construct()
    {
        $this->map = [];
    }

    public function get($key, $default = null, $class = null)
    {
        if (!isset($this->map[$key])) {
            return $default;
        }

        if (null === $class) {
            return $this->map[$key];
        }

        if ($this->map[$key] instanceof $class
            || is_subclass_of($this->map[$key], $class)
        ) {
            return $this->map[$key];
        }

        return $default;
    }

    public function set($key, $value)
    {
        $this->map[$key] = $value;
    }

    public function clear()
    {
        foreach ($this->map as $value) {
            unset($value);
        }
        unset($this->map);
        $this->map = null;
    }
}