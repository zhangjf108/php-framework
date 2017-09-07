<?php
/**
 * 上海葡萄纬度科技有限公司.
 * @copyright Copyright (c) 2017 Shanghai Putao Technology Co., Ltd.
 * @license http://www.putao.com/
 * @author Zhang Jianfeng <zhangjianfeng@putao.com>
 * @Date: 2017/7/2 02:14
 */

namespace Kerisy\Exception;


class ContextErrorException extends \ErrorException
{
    public function __construct($message, $code, $severity, $filename, $lineno, $context = array())
    {
        parent::__construct($message, $code, $severity, $filename, $lineno);
    }
}