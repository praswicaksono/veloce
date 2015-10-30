<?php

namespace Veloce;

use Psr\Http\Message\ResponseInterface;
use Slim\App;
use Slim\Http\Environment;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class RequestHandler
 * @package Veloce
 */
class RequestHandler
{
    /**
     * @var App
     */
    private $app;

    /**
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * @param \swoole_http_request $request
     * @param \swoole_http_response $response
     * @throws \Exception
     */
    public function __invoke($request, $response)
    {
        $this->app->getContainer()['environment'] = $this->app->getContainer()->factory(function () {
            return new Environment($_SERVER);
        });

        $this->app->getContainer()['request'] =  $this->app->getContainer()->factory(function ($container) {
            return Request::createFromEnvironment($container['environment']);
        });

        $this->app->getContainer()['response'] = $this->app->getContainer()->factory(function ($container) {
            $headers = new Headers(['Content-Type' => 'text/html']);
            $response = new Response(200, $headers);
            return $response->withProtocolVersion($container->get('settings')['httpVersion']);
        });

        /**
         * @var ResponseInterface $appResponse
         */
        $appResponse = $this->app->run(true);


        // set http header
        foreach ($appResponse->getHeaders() as $key => $value) {
            $filter_header = function ($header) {
                $filtered = str_replace('-', ' ', $header);
                $filtered = ucwords($filtered);
                return str_replace(' ', '-', $filtered);
            };
            $name = $filter_header($key);
            foreach ($value as $v) {
                $response->header($name, $v);
            }
        }

        // set http status
        $response->status($appResponse->getStatusCode());

        // send response to browser
        if (!$this->isEmptyResponse($appResponse)) {
            $body = $appResponse->getBody();
            if ($body->isSeekable()) {
                $body->rewind();
            }
            $settings       = $this->app->getContainer()->get('settings');
            $chunkSize      = $settings['responseChunkSize'];
            $contentLength  = $appResponse->getHeaderLine('Content-Length');
            if (!$contentLength) {
                $contentLength = $body->getSize();
            }
            $totalChunks    = ceil($contentLength / $chunkSize);
            $lastChunkSize  = $contentLength % $chunkSize;
            $currentChunk   = 0;
            while (!$body->eof() && $currentChunk < $totalChunks) {
                if (++$currentChunk == $totalChunks && $lastChunkSize > 0) {
                    $chunkSize = $lastChunkSize;
                }
                $response->write($body->read($chunkSize));
                if (connection_status() != CONNECTION_NORMAL) {
                    break;
                }
            }
            $response->end();
        }
    }

    /**
     * @param ResponseInterface $response
     * @return bool
     */
    private function isEmptyResponse(ResponseInterface $response)
    {
        return in_array($response->getStatusCode(), [204, 205, 304]);
    }
}
