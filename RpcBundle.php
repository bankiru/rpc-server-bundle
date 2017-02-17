<?php

namespace Bankiru\Api\Rpc;

use Bankiru\Api\Rpc\DependencyInjection\Compiler\RouterLoaderPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class RpcBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new RouterLoaderPass());
    }
}
