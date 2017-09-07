<?php
/**
 * 上海葡萄纬度科技有限公司.
 * @copyright Copyright (c) 2017 Shanghai Putao Technology Co., Ltd.
 * @license http://www.putao.com/
 * @author Zhang Jianfeng <zhangjianfeng@putao.com>
 * @Date: 2017/6/27 16:44
 */

namespace Kerisy\Exception;


class InvalidParamException extends Exception
{
    public function getName()
    {
        return 'Invalid Param';
    }
}