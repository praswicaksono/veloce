<?php

namespace Veloce;

use Slim\App;

/**
 * Class Stack
 * @package Veloce
 */
class Stack
{
    /**
     * @var App
     */
    private $app;

    /**
     * @var string
     */
    private $host;

    /**
     * @var int
     */
    private $port;

    /**
     * @param App $app
     * @param array $serverSettings
     */
    public function __construct(App $app, array $serverSettings = [])
    {
        $app->getContainer()['http.server.config'] = $serverSettings;

        $app->getContainer()['swoole.http.server'] = function ($container) {
            $httpServer = new \swoole_http_server($this->host, $this->port);
            $httpServer->set($container['http.server.config']);
            $httpServer->setGlobal(HTTP_GLOBAL_ALL);
            $httpServer->on('request', $container['http.request.handler']);
            return $httpServer;
        };

        $app->getContainer()['http.request.handler'] = function() use ($app) {
            return new RequestHandler($app);
        };

        $this->app = $app;
    }

    /**
     * @param $port
     * @param string $hostname
     */
    public function listen($port, $hostname = '127.0.0.1')
    {
        $this->host = $hostname;
        $this->port = $port;
        $this->app->getContainer()['swoole.http.server']->start();
    }
}
