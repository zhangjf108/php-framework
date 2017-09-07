<?php

namespace Kerisy\Ppm;

use PHPPM\Bootstraps\BootstrapInterface;
use PHPPM\Bootstraps\HooksInterface;
use PHPPM\Bootstraps\RequestClassProviderInterface;
use PHPPM\Bootstraps\ApplicationEnvironmentAwareInterface;

/**
 * A default bootstrap for the Kerisy framework
 */
class Kerisy implements BootstrapInterface, HooksInterface, RequestClassProviderInterface,
    ApplicationEnvironmentAwareInterface
{
    /**
     * @var string|null The application environment
     */
    protected $appenv;

    /**
     * @var bool
     */
    protected $debug = false;

    /**
     * Instantiate the bootstrap, storing the $appenv
     *
     * @param string|null $appenv The environment your application will use to bootstrap (if any)
     * @param $debug
     */
    public function initialize($appenv, $debug)
    {
        $this->appenv = $appenv;
        $this->debug = $debug;
        putenv("APP_DEBUG=" . ($debug ? 'true' : 'false'));
        putenv("APP_ENV=" . $this->appenv);
    }

    /**
     * {@inheritdoc}
     */
    public function getStaticDirectory() {
        return 'public/';
    }

    /**
     * {@inheritdoc}
     */
    public function requestClass() {
        return '\Kerisy\Http\ServerRequest';
    }

    /**
     * Create a Kerisy application
     */
    public function getApplication()
    {
        $baseDir = '/Users/zhangjianfeng/workspace/php/kerisy_new/kerisy';

        require $baseDir . '/vendor/kerisy/framework/src/Kerisy.php';

        $app = require_once $baseDir . '/application/bootstrap.php';

        if (!$app) {
            throw new \RuntimeException('Kerisy bootstrap file not found');
        }

        return $app;
    }

    /**
     * @param \Kerisy\Core\Application\Web $app
     */
    public function preHandle($app)
    {

    }

    /**
     * @param \Kerisy\Core\Application\Web $app
     */
    public function postHandle($app)
    {

    }
}
