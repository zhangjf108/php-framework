<?php
/**
 * 上海葡萄纬度科技有限公司.
 * @copyright Copyright (c) 2017 Shanghai Putao Technology Co., Ltd.
 * @license http://www.putao.com/
 * @author Zhang Jianfeng <zhangjianfeng@putao.com>
 * @Date: 2017/6/29 21:21
 */

namespace Kerisy\Exception;


class NotFoundException extends HttpException implements ExceptionInterface
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Not Found';
    }
}