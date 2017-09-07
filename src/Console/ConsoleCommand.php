<?php
/**
 * 上海葡萄纬度科技有限公司.
 * @copyright Copyright (c) 2017 Shanghai Putao Technology Co., Ltd.
 * @license http://www.putao.com/
 * @author Zhang Jianfeng <zhangjianfeng@putao.com>
 * @date: 2017/7/20 16:50
 * @version 3.0.0
 */

namespace Kerisy\Console;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleCommand extends Command
{
    private $name = 'console';
    private $description = 'Kerisy console command';

    protected function configure()
    {
        ini_set('memory_limit', '512M');

        parent::configure();

        $this->setName($this->name)
             ->setDescription($this->description)
             ->addOption('router', '-r', InputArgument::OPTIONAL, 'define shell path')
             ->addArgument('args', InputArgument::OPTIONAL, 'define the args, like argv[]');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $argv = $input->getArgument('args');

        $argv = $argv ?? [];

        $app = require './application/bootstrap.php';

        $path = $input->getOption('router');
        $return = $app->bootstrap()->handleConsole($path, $argv);
        exit($return);
    }
}