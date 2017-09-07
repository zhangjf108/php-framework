<?php
/**
 * 上海葡萄纬度科技有限公司.
 * @copyright Copyright (c) 2017 Shanghai Putao Technology Co., Ltd.
 * @license http://www.putao.com/
 * @author Zhang Jianfeng <zhangjianfeng@putao.com>
 * @date: 2017/6/29 21:58
 * @version 2.0.3
 */

namespace Kerisy\Exception;

use Kerisy\Http\Utils\HttpStatus;

abstract class CustomException extends HttpException
{
    private $statusCode = 200;

    /**
     * Custom exception constructor.
     *
     * @param string|null $message
     * @param int $code
     * @param Exception $previous The previous exception used for the exception chaining.
     */
    public function __construct($message = null, $code = 0, Exception $previous = null)
    {
        if ($message === null) {
            $message = HttpStatus::getReasonPhrase($this->statusCode);
        }

        parent::__construct($code, $message, $previous);
    }

    /**
     * Get the http status code.
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Set the http status code.
     *
     * @param int $statusCode
     */
    public function setStatusCode(int $statusCode = 200)
    {
        $this->statusCode = $statusCode;
    }

    abstract public function getResponseHeaders(): array;

    abstract public function getResponseData(): string;
}