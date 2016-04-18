<?php
/**
 * Created by PhpStorm.
 * User: batanov.pavel
 * Date: 12.02.2016
 * Time: 9:31
 */

namespace Bankiru\Api\Rpc;

use Bankiru\Api\Rpc\Http\RequestInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class RpcEvent extends Event
{
    /** @var  HttpKernelInterface */
    private $kernel;
    /** @var  RequestInterface */
    private $request;

    /**
     * RpcEvent constructor.
     *
     * @param HttpKernelInterface  $kernel
     * @param RequestInterface $request
     */
    public function __construct(HttpKernelInterface $kernel, RequestInterface $request)
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
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }
}
