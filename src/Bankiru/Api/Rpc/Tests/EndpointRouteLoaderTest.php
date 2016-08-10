<?php

namespace Bankiru\Api\Rpc\Tests;

use Bankiru\Api\Rpc\Http\Routing\EndpointRouteLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Routing\RouteCollection;

class EndpointRouteLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testLoader()
    {
        $public = [
            'path'     => '/',
            'defaults' => [
                '_format' => 'json',
                '_locale' => 'ru',
            ],
        ];

        $admin = [
            'path'     => '/admin/',
            'defaults' => [
                '_format' => 'json',
                '_locale' => 'ru',
            ],
        ];

        $test = [
            'path'     => '/test/',
            'defaults' => [
                '_format' => 'xml',
                '_locale' => 'ru',
            ],
        ];

        $loader   = new EndpointRouteLoader();
        $resolver = new LoaderResolver([$loader]);
        $loader->setResolver($resolver);

        $loader->addEndpoint('public', $public);
        $loader->addEndpoint('admin', $admin);
        $loader->addEndpoint('test', $test);

        $realLoader = $resolver->resolve('.', 'endpoint');

        /** @var RouteCollection $collection */
        $collection = $realLoader->load('.', 'endpoint');

        $publicRoute = $collection->get('public');
        self::assertNotNull($publicRoute);
        $adminRoute = $collection->get('admin');
        self::assertNotNull($adminRoute);
        $testRoute = $collection->get('test');
        self::assertNotNull($testRoute);

        self::assertEquals('json', $publicRoute->getDefault('_format'));
        self::assertEquals('json', $adminRoute->getDefault('_format'));
        self::assertEquals('xml', $testRoute->getDefault('_format'));

        self::assertEquals('ru', $publicRoute->getDefault('_locale'));
        self::assertEquals('ru', $adminRoute->getDefault('_locale'));
        self::assertEquals('ru', $testRoute->getDefault('_locale'));

        try {
            $realLoader->load('.', 'endpoint');
        } catch (\LogicException $e) {
            self::assertSame('Endpoint loader is already loaded', $e->getMessage());
        }

        try {
            $loader->addEndpoint('public', $public);
        } catch (\LogicException $e) {
            self::assertSame('Endpoint loader is already configured', $e->getMessage());
        }
    }
}
