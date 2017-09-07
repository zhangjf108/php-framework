<?php
/**
 * 上海葡萄纬度科技有限公司.
 * @copyright Copyright (c) 2017 Shanghai Putao Technology Co., Ltd.
 * @license http://www.putao.com/
 * @author Zhang Jianfeng <zhangjianfeng@putao.com>
 * @date: 2017/7/14 21:50
 * @version 3.0.0
 */

namespace Kerisy\Db;


class ConnectionException extends Exception
{
    public function getName()
    {
        return 'DB Connection Error';
    }
}