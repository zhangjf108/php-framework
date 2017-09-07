<?php
/**
 * 上海葡萄纬度科技有限公司.
 * @copyright Copyright (c) 2017 Shanghai Putao Technology Co., Ltd.
 * @license http://www.putao.com/
 * @author Zhang Jianfeng <zhangjianfeng@putao.com>
 * @date: 2017/7/20 16:42
 * @version 3.0.0
 */

namespace Kerisy\Console;


use Kerisy\Exception\InvalidConfigException;
use Kerisy\Exception\InvalidParamException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WebSocketCommand extends Command
{
    private $name = 'websocket';
    private $description = 'Kerisy WebSocket management';
    private $serverConfig = [];

    protected function configure()
    {
        parent::configure();

        $this->setName($this->name)
            ->setDescription($this->description)
            ->addArgument('operation', InputArgument::REQUIRED, 'the operation: start, reload, restart, stop status');
        $this->addOption('alias_name', '-i', InputArgument::OPTIONAL, 'please add operation alias name');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initConfig();

        $operation = $input->getArgument('operation');
        !is_null($input->getOption('alias_name')) && $this->setAliases(['alias_name' => $input->getOption('alias_name')]);
        if (!in_array($operation, ['run', 'start', 'restart', 'stop', 'reload', 'status'])) {
            throw new InvalidParamException('The <operation> argument is invalid');
        }

        return call_user_func([$this, 'handle' . $operation]);
    }

    protected function handleRun()
    {
        $this->serverConfig['asDaemon'] = 0;

        if (isset($this->serverConfig['pidFile'])) {
            unset($this->serverConfig['pidFile']);
        }

        return $this->serverRun();
    }

    protected function handleStart()
    {
        $pidFile = $this->serverConfig['pidFile'];

        if (file_exists($pidFile)) {
            throw new InvalidConfigException('The pidfile exists, it seems the server is already started.');
        }

        $this->serverConfig['asDaemon'] = 1;

        return $this->serverRun();
    }

    protected function handleRestart()
    {
        $this->handleStop();
        return $this->handleStart();
    }

    protected function handleStop()
    {
        $pidFile = $this->serverConfig['pidFile'];
        if (!file_exists($pidFile)) {
            throw new InvalidConfigException('The pidfile not exists, it seems the server is already stoped.');
        }

        if (posix_kill(file_get_contents($pidFile), SIGTERM)) {
            do {
                unlink($pidFile);
                usleep(100000);
            } while (file_exists($pidFile));
            return 0;
        }

        return 1;
    }

    protected function handleReload()
    {
        $pidFile = $this->serverConfig['pidFile'];
        if (!file_exists($pidFile)) {
            throw new InvalidConfigException('The pidfile not exists, it seems the server is already stoped.');
        }

        if (posix_kill(file_get_contents($pidFile), SIGUSR1)) {
            return 0;
        }
        return 1;
    }

    private function serverRun()
    {
        $class = $this->serverConfig['class'];
        unset($this->serverConfig['class']);

        $server = new $class($this->serverConfig);

        isset($this->getAliases()['alias_name']) && $server->setProcessName($this->getAliases()['alias_name']);

        return $server->run();
    }

    private function initConfig()
    {
        //初始化配置
        $this->serverConfig = require './application/websocket.php';
    }
}