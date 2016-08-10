<?php

namespace Bankiru\Api\Rpc\Tests\Fixtures;

use Bankiru\Api\Rpc\RpcBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    /** {@inheritdoc} */
    public function registerBundles()
    {
        return [
            new FrameworkBundle(),
            new RpcBundle(),
        ];
    }

    /** {@inheritdoc} */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config/test.yml');
    }

    public function getCacheDir()
    {
        return __DIR__ . '/../../../../../../build/cache/';
    }

    public function getLogDir()
    {
        return __DIR__ . '/../../../../../../build/log/';
    }
}
