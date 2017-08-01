<?php

namespace Bankiru\Api\Rpc\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class RouterLoaderPass implements CompilerPassInterface
{
    /** {@inheritdoc} */
    public function process(ContainerBuilder $container)
    {
        $loader = $container->getDefinition('rpc_server.router.resolver');

        $taggedServices = $container->findTaggedServiceIds('rpc.route_loader');

        foreach ($taggedServices as $id => $tags) {
            $loader->addMethodCall('addLoader', [new Reference($id)]);
        }
    }
}
