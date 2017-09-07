<?php

namespace Kerisy\Ppm;

use Kerisy\Http\Factory\ServerRequestFactory;
use PHPPM\Bootstraps\ApplicationEnvironmentAwareInterface;
use PHPPM\Bootstraps\BootstrapInterface;
use PHPPM\Bootstraps\HooksInterface;
use PHPPM\Bridges\BridgeInterface;
use PHPPM\React\HttpResponse;
use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\LoopInterface;
use React\Http\Request as ReactRequest;

class KerisyKernel implements BridgeInterface
{
    /**
     * An application implementing the HttpKernelInterface
     *
     * @var \Kerisy\Core\Application\Web
     */
    protected $application;

    /**
     * @var BootstrapInterface
     */
    protected $bootstrap;

    /**
     * Bootstrap an application implementing the HttpKernelInterface.
     *
     * In the process of bootstrapping we decorate our application with any number of
     * *middlewares* using StackPHP's Stack\Builder.
     *
     * The app bootstraping itself is actually proxied off to an object implementing the
     * PHPPM\Bridges\BridgeInterface interface which should live within your app itself and
     * be able to be autoloaded.
     *
     * @param string $appBootstrap The name of the class used to bootstrap the application
     * @param string|null $appenv The environment your application will use to bootstrap (if any)
     * @param boolean $debug If debug is enabled
     * @see http://stackphp.com
     */
    public function bootstrap($appBootstrap, $appenv, $debug, LoopInterface $loop)
    {
        $appBootstrap = $this->normalizeAppBootstrap($appBootstrap);

        $this->bootstrap = new $appBootstrap();
        if ($this->bootstrap instanceof ApplicationEnvironmentAwareInterface) {
            $this->bootstrap->initialize($appenv, $debug);
        }

        if ($this->bootstrap instanceof BootstrapInterface) {
            $this->application = $this->bootstrap->getApplication();
        }

        $this->application->bootstrap();
    }

    /**
     * {@inheritdoc}
     */
    public function getStaticDirectory()
    {
        return $this->bootstrap->getStaticDirectory();
    }

    /**
     * Handle a request using a HttpKernelInterface implementing application.
     *
     * @param ReactRequest $reactRequest
     * @param HttpResponse $reactResponse
     *
     * @throws \Exception
     */
    public function onRequest(ReactRequest $reactRequest, HttpResponse $reactResponse)
    {
        if (null === $this->application) {
            return;
        }

        $request = $this->mapRequest($reactRequest);

        // start buffering the output, so cgi is not sending any http headers
        // this is necessary because it would break session handling since
        // headers_sent() returns true if any unbuffered output reaches cgi stdout.

        if ($this->bootstrap instanceof HooksInterface) {
            $this->bootstrap->preHandle($this->application);
        }

        $response = $this->application->handleRequest($request);

        $headers = $response->getHeaders();
        $content = $response->getBody();

        //$headers['Content-Length'] = $content->getSize();

        $reactResponse->writeHead($response->getStatusCode(), $headers);
        $reactResponse->end($content);

        if ($this->bootstrap instanceof HooksInterface) {
            $this->bootstrap->postHandle($this->application);
        }
    }

    /**
     * Convert React\Http\Request to Psr\Http\Message\ServerRequestInterface
     *
     * @param ReactRequest $reactRequest
     * @return ServerRequestInterface $request
     */
    protected function mapRequest(ReactRequest $reactRequest)
    {
        return ServerRequestFactory::createServerRequestFromReact($reactRequest);
    }

    /**
     * @param $appBootstrap
     * @return string
     * @throws \RuntimeException
     */
    protected function normalizeAppBootstrap($appBootstrap)
    {
        $appBootstrap = str_replace('\\\\', '\\', $appBootstrap);

        $bootstraps = [
            $appBootstrap,
            '\\' . $appBootstrap,
            '\\PHPPM\Bootstraps\\' . ucfirst($appBootstrap)
        ];

        foreach ($bootstraps as $class) {
            if (class_exists($class)) {
                return $class;
            }
        }
    }
}
