<?php
/**
 * 上海葡萄纬度科技有限公司.
 * @copyright Copyright (c) 2017 Shanghai Putao Technology Co., Ltd.
 * @license   http://www.putao.com/
 * @author    Zhang Jianfeng <zhangjianfeng@putao.com>
 * @date:     2017/6/14 17:52
 * @version   2.0.1
 */

namespace Kerisy\Http;


use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\ResponseInterface;
use Kerisy\Http\Exception\LogicException;
use Kerisy\Http\Factory\StreamFactory;
use Kerisy\Http\Utils\HttpStatus;

class Response extends Message implements ResponseInterface
{
    /**
     * @var int
     */

    private $statusCode;

    /**
     * @var string
     */
    private $reasonPhrase = '';

    /**
     * Creates a Response instance.
     *
     * @param int $statusCode The response status code.
     * @param string          $reasonPhrase The response reason phrase.
     * @param string[][]      $headers The response headers.
     * @param StreamInterface $body The response body.
     * @param string $version The HTTP protocol version as a string.
     */

    public function __construct(int $statusCode, string $reasonPhrase, array $headers,
                                StreamInterface &$body, string $version)
    {
        $this->statusCode = $statusCode;
        $this->reasonPhrase = $reasonPhrase;
        $this->headers = $headers;
        $this->body = $body;
        $this->version = $version;
    }

    /**
     * {@inheritdoc}
     */

    final public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * {@inheritdoc}
     */

    final public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    /**
     * {@inheritdoc}
     */

    final public function withStatus($statusCode, $reasonPhrase = ''): ResponseInterface
    {
        if (!$reasonPhrase) {
            $reasonPhrase = HttpStatus::getReasonPhrase($statusCode);
        }

        $instance = clone $this;
        $instance->statusCode = $statusCode;
        $instance->reasonPhrase = $reasonPhrase;

        return $instance;
    }

    /**
     * Sends the response to the user.
     *
     * @throws LogicException if the headers were already sent.
     */

    final public function send()
    {
        if (headers_sent($file, $line)) {
            throw new LogicException(sprintf('Headers already sent in %s:%d', $file, $line));
        }

        header(sprintf('Status: %d %s', $this->getStatusCode(), $this->getReasonPhrase()), true, $this->getStatusCode());

        foreach ($this->getHeaders() as $name => $content) {
            header(sprintf('%s: %s', $name, $this->getHeaderLine($name)));
        }

        return $this->getBody();
    }

    final public function redirect(string $url, int $statusCode = 302)
    {
        if (empty($url)) {
            throw new \InvalidArgumentException('Cannot redirect to an empty URL.');
        }

        $content = sprintf('<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <meta http-equiv="refresh" content="0;url=%1$s" />
        <title>Redirecting to %1$s</title>
    </head>
    <body>
        Redirecting to <a href="%1$s">%1$s</a>.
    </body>
</html>', htmlspecialchars($url, ENT_QUOTES, 'UTF-8'));


        $body = (new StreamFactory())->createStream($content);

        $instance = clone $this;
        $instance->statusCode = $statusCode;
        $instance->reasonPhrase = HttpStatus::getReasonPhrase($statusCode);

        return $instance->withHeader('Location', $url)->withBody($body);
    }
}