<?php
/**
 * 上海葡萄纬度科技有限公司.
 * @copyright Copyright (c) 2017 Shanghai Putao Technology Co., Ltd.
 * @license http://www.putao.com/
 * @author Zhang Jianfeng <zhangjianfeng@putao.com>
 * @Date: 2017/6/30 23:11
 */

namespace Kerisy\Log\Target;


use Kerisy\Log\Target;
use Monolog\Handler\RotatingFileHandler;

/**
 * Class RotatingFileTarget
 *
 * @package Kerisy\Log\Target
 */
class RotatingFileTarget extends Target
{
    public $filename;

    public $maxFile = 0;

    protected $handler;

    public function getUnderlyingHandler()
    {
        if (!$this->handler) {
            $this->handler = new RotatingFileHandler($this->filename, $this->maxFile, $this->level, true, null, false);
        }
        return $this->handler;
    }
}