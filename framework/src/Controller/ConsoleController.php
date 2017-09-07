<?php
/**
 * 上海葡萄纬度科技有限公司.
 * @copyright Copyright (c) 2017 Shanghai Putao Technology Co., Ltd.
 * @license http://www.putao.com/
 * @author Zhang Jianfeng <zhangjianfeng@putao.com>
 * @Date: 2017/6/29 16:23
 */

namespace Kerisy\Controller;


class ConsoleController extends BaseController
{
    protected $argv = [];

    public function __construct(array $argv)
    {
        $this->argv = $argv;
    }
}