<?php

namespace Kerisy\Log;


use Kerisy;
use Kerisy\Core\Component;
use Kerisy\Core\Object;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Monolog\Logger as BaseMonoLogger;
use Monolog\Handler\HandlerInterface;
use Kerisy\Exception\InvalidParamException;

/**
 * Class Logger
 *
 * @package Kerisy\Log
 */
class Logger extends Component implements LoggerInterface
{
    use LoggerTrait;

    public $name = 'kerisy';

    public $targets = [];

    /**
     * @var MonoLogger
     */
    protected $monolog;

    protected $levelMap = [
        'emergency' => MonoLogger::EMERGENCY,
        'alert' => MonoLogger::ALERT,
        'critical' => MonoLogger::CRITICAL,
        'error' => MonoLogger::ERROR,
        'warning' => MonoLogger::WARNING,
        'notice' => MonoLogger::NOTICE,
        'info' => MonoLogger::INFO,
        'debug' => MonoLogger::DEBUG,
    ];

    public function init()
    {
        $this->monolog = new MonoLogger($this->name);

        foreach ($this->targets as &$target) {
            $target = Kerisy::make($target);
            $this->monolog->pushHandler($target);
        }
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @throws InvalidParamException
     */
    public function log($level, $message, array $context = [])
    {
        if (!isset($this->levelMap[$level])) {
            throw new InvalidParamException('Level "' . $level . '" is not defined, use one of: ' . implode(', ', array_keys($this->levelMap)));
        }

        $this->monolog->addRecord($this->levelMap[$level], $message, $context);
    }
}

class MonoLogger extends BaseMonoLogger
{
    /**
     * Hack to remove the default logger support of Monolog.
     *
     * @param HandlerInterface $handler
     * @return $this
     */
    public function pushHandler(HandlerInterface $handler)
    {
        if ($handler instanceof Component) {
            array_unshift($this->handlers, $handler);
        }

        return $this;
    }
}
