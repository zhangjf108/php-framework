<?php
/**
 * 上海葡萄纬度科技有限公司.
 * @copyright Copyright (c) 2017 Shanghai Putao Technology Co., Ltd.
 * @license   http://www.putao.com/
 * @author    Zhang Jianfeng <zhangjianfeng@putao.com>
 * @date:     2017/6/14 17:52
 * @version   2.0.1
 */

namespace Kerisy\Exception;


use Throwable;

class Exception extends \Exception implements ExceptionInterface
{
    public function __construct($message = "", $code = 0,  Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}