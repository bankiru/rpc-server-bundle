<?php

namespace Bankiru\Api\Rpc\Event;

use Bankiru\Api\Rpc\Http\RequestInterface;
use Bankiru\Api\Rpc\RpcEvent;
use Bankiru\Api\Rpc\RpcRequestInterface;
use ScayTrase\Api\Rpc\RpcResponseInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class FilterResponseEvent extends RpcEvent
{
    /** @var RpcResponseInterface|null */
    private $response;

    public function __construct(
        HttpKernelInterface $kernel,
        RpcRequestInterface $request,
        RpcResponseInterface $response
    )
    {
        parent::__construct($kernel, $request);
        $this->response = $response;
    }

    /** @return RpcResponseInterface|null */
    public function getResponse()
    {
        return $this->response;
    }

    public function setResponse(RpcResponseInterface $response = null)
    {
        $this->response = $response;
    }
}
