<?php
/**
 * 上海葡萄纬度科技有限公司.
 * @copyright Copyright (c) 2017 Shanghai Putao Technology Co., Ltd.
 * @license http://www.putao.com/
 * @author Zhang Jianfeng <zhangjianfeng@putao.com>
 * @Date: 2017/6/30 23:22
 */

namespace Kerisy\Config;


use Kerisy\Exception\InvalidConfigException;

class ConfigFactory implements ConfigFactoryInterface
{

    /**
     * {@inheritdoc}
     */

    public function createConfig(string $configPath = '/', string $configGroup = '', string $filetype = 'php', string $environment = '', string $language = ''): ConfigInterface
    {
        switch ($filetype) {
            case 'php':
                $config = new ConfigPhp();
                $fileExt = '.php';
                break;
            case 'json':
                $config = new ConfigJson();
                $fileExt = '.json';
                break;
            default:
                throw new InvalidConfigException('Invalid config file type!');
        }

        //加载多语言配置
        $filename = $configPath . $configGroup . '_' . $environment . '_' . $language . $fileExt;
        if (!file_exists($filename)) {
            $filename = $configPath . $configGroup . '_' . $environment . $fileExt;
            if (!file_exists($filename)) {
                $filename = $configPath . $configGroup . '_' . $language . $fileExt;
                if (!file_exists($filename)) {
                    $filename = $configPath . $configGroup . $fileExt;
                    if (!file_exists($filename)) {
                        throw new InvalidConfigException("The config file {$filename} not found!");
                    }
                }
            }
        }

        $config->load($filename);

        return $config;
    }
}