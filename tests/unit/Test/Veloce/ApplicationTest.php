<?php

namespace Test\Veloce;

use Mockery as m;
use Slim\App;
use Slim\Http\Headers;
use Slim\Http\Response;
use Veloce\RequestHandler;
use Slim\Container;

/**
 * Class ApplicationTest
 * @package Test\Veloce
 */
class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {

    }

    protected function tearDown()
    {
        m::close();
    }

    // tests
    public function testGetRequest()
    {
        $request = m::mock('\swoole_http_request');
        $response = m::mock('\swoole_http_response');
        $container = new Container();
        $app = m::mock(App::class);

        $handler = new RequestHandler($app);

        $mockResponse = new Response(200, new Headers(
            [
                'Content-Type' => 'text/html',
                'Content-Length' => 5
            ]
        ));
        $mockResponse->write('hello');

        $app->shouldReceive('getContainer')->andReturn($container);
        $app->shouldReceive('run')->once()->andReturn($mockResponse);
        $response->shouldReceive('header')->twice();
        $response->shouldReceive('status')->once()->withArgs([200]);
        $response->shouldReceive('write')->once()->withArgs(['hello']);
        $response->shouldReceive('end')->once();

        $handler($request, $response);
    }
}
