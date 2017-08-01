<?php

namespace Bankiru\Api\Rpc;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class RpcEvent extends Event
{
    /** @var  HttpKernelInterface */
    private $kernel;
    /** @var  RpcRequestInterface */
    private $request;

    /**
     * RpcEvent constructor.
     *
     * @param HttpKernelInterface $kernel
     * @param RpcRequestInterface $request
     */
    public function __construct(HttpKernelInterface $kernel, RpcRequestInterface $request)
    {
        $this->kernel  = $kernel;
        $this->request = $request;
    }

    /**
     * @return HttpKernelInterface
     */
    public function getKernel()
    {
        return $this->kernel;
    }

    /**
     * @return RpcRequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }
}
