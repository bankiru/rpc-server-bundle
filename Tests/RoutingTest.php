<?php

namespace Bankiru\Api\Rpc\Tests;

use Bankiru\Api\Rpc\Routing\MethodCollection;
use Bankiru\Api\Rpc\Routing\Router;
use Bankiru\Api\Rpc\Tests\Fixtures\Kernel;
use Bankiru\Api\Rpc\Tests\Fixtures\Rpc\TestController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\RequestContext;

class RoutingTest extends WebTestCase
{
    public function testRouting()
    {
        $client  = self::createClient();
        $router  = $client->getContainer()->get('router');
        $context = new RequestContext();
        $context->setMethod('POST');
        $router->setContext($context);
        $match = $router->match('/test/');
        self::assertArrayHasKey('_controller', $match);
        self::assertSame(TestController::class . '::rpcAction', $match['_controller']);
        self::assertSame('test', $match['_route']);
    }

    public function testRpcRouterCollection()
    {
        $client = self::createClient();
        /** @var Router $router */
        $router = $client->getContainer()->get('rpc.endpoint_router.test');
        self::assertNotNull($router);
        /** @var MethodCollection $collection */
        $collection = $router->getCollection();
        self::assertNotNull($router);
        self::assertInstanceOf(MethodCollection::class, $collection);

        self::assertTrue($collection->has('test_method'));
        $route = $collection->get('test_method');
        self::assertSame('test/method', $route->getMethod());

        self::assertContains('Default', $route->getContext());
        self::assertContains('test', $route->getContext());
        self::assertTrue($route->includeDefaultContext());

        $route = $collection->get('test_inheritance');
        self::assertSame('test/non-inherited', $route->getMethod());

        self::assertCount(2, $route->getContext());
        self::assertContains('Default', $route->getContext());
        self::assertNotContains('test', $route->getContext());
        self::assertContains('own', $route->getContext());
        self::assertTrue($route->includeDefaultContext());

        $route = $collection->get('annotation');
        self::assertSame('annotation', $route->getMethod());

        self::assertNotContains('Default', $route->getContext());
        self::assertFalse($route->includeDefaultContext());
        self::assertContains('annotation-non-inherit', $route->getContext());
        self::assertCount(1, $route->getContext());
    }

    protected static function getKernelClass()
    {
        return Kernel::class;
    }
}
