<?php
/**
 * Created by PhpStorm.
 * User: batanov.pavel
 * Date: 17.05.2016
 * Time: 7:42
 */

namespace Bankiru\Api\Rpc\DependencyInjection\Compiler;

use Bankiru\Api\Rpc\Listener\ExceptionListener;
use Bankiru\Api\Rpc\RpcEvents;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class RouterLoaderPass implements CompilerPassInterface
{
    /** {@inheritdoc} */
    public function process(ContainerBuilder $container)
    {
        if ($container->has('logger')) {
            $container
                ->register('rpc.exception_listener', ExceptionListener::class)
                ->setArguments([new Reference('logger')])
                ->addTag(
                    'kernel.event_listener',
                    [
                        'event'  => RpcEvents::EXCEPTION,
                        'method' => 'onException',
                    ]
                );
        }

        $loader = $container->getDefinition('rpc.router.resolver');

        $taggedServices = $container->findTaggedServiceIds('rpc.route_loader');

        foreach ($taggedServices as $id => $tags) {
            $loader->addMethodCall('addLoader', [new Reference($id)]);
        }
    }
}
