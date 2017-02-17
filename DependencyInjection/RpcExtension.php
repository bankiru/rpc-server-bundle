<?php

namespace Bankiru\Api\Rpc\DependencyInjection;

use Bankiru\Api\Rpc\Listener\RouterListener;
use Bankiru\Api\Rpc\Routing\MethodCollection;
use Bankiru\Api\Rpc\Routing\Router;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class RpcExtension extends Extension
{

    /** {@inheritdoc} */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        if (array_key_exists('NelmioApiDocBundle', $container->getParameter('kernel.bundles'))) {
            $loader->load('nelmio.yml');
        }

        if ($container->has('security.authorization_checker')) {
            $loader->load('security.yml');
        }

        $configuration = new Configuration();
        $config        = $this->processConfiguration($configuration, $configs);


        $loader->load('rpc.yml');

        $this->configureRouter($config['router'], $container);
    }

    /**
     * @param array            $router
     * @param ContainerBuilder $container
     */
    private function configureRouter(array $router, ContainerBuilder $container)
    {
        $endpoints = $router['endpoints'];

        $endpointLoader = $container->getDefinition('rpc.router.loader');

        $routerCollection = $container->getDefinition('rpc.router.collection');

        foreach ($endpoints as $name => $config) {

            $collection     = new Definition(MethodCollection::class);
            $endpointRouter = new Definition(
                Router::class,
                [
                    $container->getDefinition('rpc.router.resolver'),
                    $config['resources'],
                    $collection,
                    $config['context']
                ]
            );

            $endpointRouter->addTag('rpc_router');
            $endpointLoader->addMethodCall('addEndpoint', [$name, $config]);
            $this->configureRouteListener($container, $name, $endpointRouter);

            //@todo review (@scaytrase)
            $container->setDefinition(sprintf('rpc.endpoint_router.%s', $name), $endpointRouter);

            $routerCollection->addMethodCall('addRouter', [$name, $endpointRouter]);
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param string           $name
     * @param Definition       $endpointRouter
     */
    private function configureRouteListener(ContainerBuilder $container, $name, Definition $endpointRouter)
    {
        $listener = new Definition(RouterListener::class, [$name, $endpointRouter]);
        $listener->addTag('kernel.event_listener', ['event' => 'rpc.request', 'method' => 'onRequest']);
        $container->setDefinition('rpc.router_listener.' . $name, $listener);
    }

    public function getAlias()
    {
        return 'rpc';
    }
}
