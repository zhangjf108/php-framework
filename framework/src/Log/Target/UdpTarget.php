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

/**
 * Class UdpTarget
 *
 * @package Kerisy\Log\Target
 */
class UdpTarget extends Target
{
    protected $handler;

    public function getUnderlyingHandler()
    {
        //todo
        return $this->handler;
    }
}