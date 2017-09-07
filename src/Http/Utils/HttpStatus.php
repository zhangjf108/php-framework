<?php
/**
 * 上海葡萄纬度科技有限公司.
 * @copyright Copyright (c) 2017 Shanghai Putao Technology Co., Ltd.
 * @license   http://www.putao.com/
 * @author    Zhang Jianfeng <zhangjianfeng@putao.com>
 * @date:     2017/6/14 17:52
 * @version   2.0.1
 */

namespace Kerisy\Http\Utils;

abstract class HttpStatus
{
    const CONTINUE                        = 100;
    const SWITCHING_PROTOCOLS             = 101;

    const OK                              = 200;
    const CREATED                         = 201;
    const ACCEPTED                        = 202;
    const NON_AUTHORITATIVE_INFORMATION   = 203;
    const NO_CONTENT                      = 204;
    const RESET_CONTENT                   = 205;
    const PARTIAL_CONTENT                 = 206;

    const MULTIPLE_CHOICES                = 300;
    const MOVED_PERMANENTLY               = 301;
    const FOUND                           = 302;
    const SEE_OTHER                       = 303;
    const NOT_MODIFIED                    = 304;
    const USE_PROXY                       = 305;
    const TEMPORARY_REDIRECT              = 307;

    const BAD_REQUEST                     = 400;
    const UNAUTHORIZED                    = 401;
    const PAYMENT_REQUIRED                = 402;
    const FORBIDDEN                       = 403;
    const NOT_FOUND                       = 404;
    const METHOD_NOT_ALLOWED              = 405;
    const NOT_ACCEPTABLE                  = 406;
    const PROXY_AUTHENTICATION_REQUIRED   = 407;
    const REQUEST_TIMEOUT                 = 408;
    const CONFLICT                        = 409;
    const GONE                            = 410;
    const LENGTH_REQUIRED                 = 411;
    const PRECONDITION_FAILED             = 412;
    const REQUEST_ENTITY_TOO_LARGE        = 413;
    const REQUEST_URI_TOO_LONG            = 414;
    const UNSUPPORTED_MEDIA_TYPE          = 415;
	const REQUESTED_RANGE_NOT_SATISFIABLE = 416;
	const EXPECTATION_FAILED              = 417;
    const UNAVAILABLE_FOR_LEGAL_REASONS   = 451;
    
    const INTERNAL_SERVER_ERROR           = 500;
    const NOT_IMPLEMENTED                 = 501;
    const BAD_GATEWAY                     = 502;
    const SERVICE_UNAVAILABLE             = 503;
    const GATEWAY_TIMEOUT                 = 504;
    const VERSION_NOT_SUPPORTED           = 505;
    
    /**
     * Gets the RFC 7231 recommended reason phrase associated with a status code.
     *
     * @param int $statusCode The status code using the HttpStatus::* constants.
     *
     * @return string Returns the reason phrase, or an empty string if the status code is not found.
     */
    
    public static function getReasonPhrase(int $statusCode) : string
    {
        switch ($statusCode) {
            case self::CONTINUE:
                return 'Continue';
                break;
            case self::SWITCHING_PROTOCOLS:
                return 'Switching Protocols';
                break;
            
            case self::OK:
                return 'OK';
                break;
            case self::CREATED:
                return 'Created';
                break;
            case self::ACCEPTED:
                return 'Accepted';
                break;
            case self::NON_AUTHORITATIVE_INFORMATION:
                return 'Non-Authoritative Information';
                break;
            case self::NO_CONTENT:
                return 'No Content';
                break;
            case self::RESET_CONTENT:
                return 'Reset Content';
                break;
            case self::PARTIAL_CONTENT:
                return 'Partial Content';
                break;
            
            case self::MULTIPLE_CHOICES:
                return 'Multiple Choices';
                break;
            case self::MOVED_PERMANENTLY:
                return 'Moved Permanently';
                break;
            case self::FOUND:
                return 'Found';
                break;
            case self::SEE_OTHER:
                return 'See Other';
                break;
            case self::NOT_MODIFIED:
                return 'Not Modified';
                break;
            case self::USE_PROXY:
                return 'Use Proxy';
                break;
            case self::TEMPORARY_REDIRECT:
                return 'Temporary Redirect';
                break;
            
            case self::BAD_REQUEST:
                return 'Bad Request';
                break;
            case self::UNAUTHORIZED:
                return 'Unauthorized';
                break;
            case self::PAYMENT_REQUIRED:
                return 'Payment Required';
                break;
            case self::FORBIDDEN:
                return 'Forbidden';
                break;
            case self::NOT_FOUND:
                return 'Not Found';
                break;
            case self::METHOD_NOT_ALLOWED:
                return 'Method Not Allowed';
                break;
            case self::NOT_ACCEPTABLE:
                return 'Not Acceptable';
                break;
            case self::PROXY_AUTHENTICATION_REQUIRED:
                return 'Proxy Authentication Required';
                break;
            case self::REQUEST_TIMEOUT:
                return 'Request Time-out';
                break;
            case self::CONFLICT:
                return 'Conflict';
                break;
            case self::GONE:
                return 'Gone';
                break;
            case self::LENGTH_REQUIRED:
                return 'Length Required';
                break;
            case self::PRECONDITION_FAILED:
                return 'Precondition Failed';
                break;
            case self::REQUEST_ENTITY_TOO_LARGE:
                return 'Request Entity Too Large';
                break;
            case self::REQUEST_URI_TOO_LONG:
                return 'Request-URI Too Long';
                break;
            case self::UNSUPPORTED_MEDIA_TYPE:
                return 'Unsupported Media Type';
                break;
            case self::REQUESTED_RANGE_NOT_SATISFIABLE:
                return 'Requested Range Not Satisfiable';
                break;
            case self::EXPECTATION_FAILED:
                return 'Expectation Failed';
                break;
            case self::UNAVAILABLE_FOR_LEGAL_REASONS:
                return 'Unavailable For Legal Reasons';
                break;
            
            case self::INTERNAL_SERVER_ERROR:
                return 'Internal Server Error';
                break;
            case self::NOT_IMPLEMENTED:
                return 'Not Implemented';
                break;
            case self::BAD_GATEWAY:
                return 'Bad Gateway';
                break;
            case self::SERVICE_UNAVAILABLE:
                return 'Service Unavailable';
                break;
            case self::GATEWAY_TIMEOUT:
                return 'Gateway Timeout';
                break;
            case self::VERSION_NOT_SUPPORTED:
                return 'HTTP Version Not Supported';
                break;

            default:
                return '';
                break;
        }
    }
}