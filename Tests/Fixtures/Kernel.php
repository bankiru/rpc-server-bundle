<?php

namespace Bankiru\Api\Rpc\Tests\Fixtures;

use Bankiru\Api\Rpc\BankiruRpcServerBundle;
use Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

final class Kernel extends BaseKernel
{
    public function __construct($environment, $debug)
    {
        @unlink($this->getCacheDir());
        @unlink($this->getLogDir());
        parent::__construct($environment, $debug);
    }

    public function getCacheDir()
    {
        return __DIR__ . '/../../build/' . $this->getName() . '/cache';
    }

    public function getLogDir()
    {
        return __DIR__ . '/../../build/' . $this->getName() . '/log';
    }

    /** {@inheritdoc} */
    public function registerBundles()
    {
        return [
            new SensioFrameworkExtraBundle(),
            new FrameworkBundle(),
            new BankiruRpcServerBundle(),
            new SecurityBundle(),
        ];
    }

    public function getName()
    {
        return 'rpc_test';
    }

    /** {@inheritdoc} */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config/test.yml');
    }
}
