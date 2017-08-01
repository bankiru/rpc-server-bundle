<?php

namespace Bankiru\Api\Rpc\Event;

use Bankiru\Api\Rpc\RpcEvent;
use Bankiru\Api\Rpc\RpcRequestInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class FilterControllerEvent extends RpcEvent
{
    /** @var  callable|null */
    private $controller;

    /**
     * FilterControllerEvent constructor.
     *
     * @param HttpKernelInterface $kernel
     * @param RpcRequestInterface $request
     * @param string|callable     $controller
     */
    public function __construct(HttpKernelInterface $kernel, RpcRequestInterface $request, $controller)
    {
        parent::__construct($kernel, $request);
        $this->controller = $controller;
    }

    /** @return callable */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @param $controller
     */
    public function setController($controller)
    {
        $this->controller = $controller;
    }
}
