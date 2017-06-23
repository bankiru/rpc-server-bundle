<?php

namespace Bankiru\Api\Rpc\DependencyInjection;

use Bankiru\Api\Rpc\Cache\RouterCacheWarmer;
use Bankiru\Api\Rpc\Listener\RouterListener;
use Bankiru\Api\Rpc\Routing\ResourceMethodCollectionLoader;
use Bankiru\Api\Rpc\Routing\Router;
use Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

final class BankiruRpcServerExtension extends Extension
{

    /** {@inheritdoc} */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $bundles = $container->getParameter('kernel.bundles');

        $configuration = new Configuration();
        $config        = $this->processConfiguration($configuration, $configs);

        $loader->load('rpc.yml');

        if (in_array(SensioFrameworkExtraBundle::class, $bundles, true)) {
            $loader->load('sensio.yml');
        }

        $this->configureRouter($config['router'], $container);
    }

    public function getAlias()
    {
        return 'rpc';
    }

    /**
     * @param array            $router
     * @param ContainerBuilder $container
     */
    private function configureRouter(array $router, ContainerBuilder $container)
    {
        $endpoints        = $router['endpoints'];
        $endpointLoader   = $container->getDefinition('rpc.router.loader');
        $routerCollection = $container->getDefinition('rpc.router.collection');

        foreach ($endpoints as $name => $config) {
            $routerId = sprintf('rpc.endpoint_router.%s', $name);

            $container->register($routerId, Router::class)
                      ->setArguments(
                          [
                              new Definition(
                                  ResourceMethodCollectionLoader::class,
                                  [
                                      new Reference('rpc.router.resolver'),
                                      $config['resources'],
                                      $config['context'],
                                  ]
                              ),
                              $name,
                              [
                                  'cache_dir' => '%kernel.cache_dir%',
                              ],
                          ]
                      )
                      ->setPublic(false)
                      ->addTag('rpc_router');

            $container->register(sprintf('rpc.router_warmer.%s', $name), RouterCacheWarmer::class)
                      ->setPublic(false)
                      ->setArguments(
                          [
                              new Reference($routerId),
                          ]
                      )
                      ->addTag('kernel.cache_warmer');

            $container->register('rpc.router_listener.' . $name, RouterListener::class)
                      ->setArguments([$name, new Reference($routerId)])
                      ->addTag('kernel.event_listener', ['event' => 'rpc.request', 'method' => 'onRequest']);

            $endpointLoader->addMethodCall('addEndpoint', [$name, $config]);
            $routerCollection->addMethodCall('addRouter', [$name, new Reference($routerId)]);
        }
    }
}
