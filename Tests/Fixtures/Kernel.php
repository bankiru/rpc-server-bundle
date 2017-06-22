<?php

namespace Bankiru\Api\Rpc\Tests\Fixtures;

use Bankiru\Api\Rpc\BankiruRpcServerBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    public function __construct($environment, $debug)
    {
        @unlink($this->getCacheDir());
        @unlink($this->getLogDir());
        parent::__construct($environment, $debug);
    }

    public function getCacheDir()
    {
        return __DIR__ . '/../../build/cache/';
    }

    public function getLogDir()
    {
        return __DIR__ . '/../../build/log/';
    }

    /** {@inheritdoc} */
    public function registerBundles()
    {
        return [
            new FrameworkBundle(),
            new BankiruRpcServerBundle(),
            new SecurityBundle(),
        ];
    }

    /** {@inheritdoc} */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config/test.yml');
    }
}
