<?php

namespace Bankiru\Api\Rpc\Event;

use Bankiru\Api\Rpc\RpcEvent;
use Bankiru\Api\Rpc\RpcRequestInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ViewEvent extends RpcEvent
{
    /** @var  mixed */
    private $response;

    /**
     * ViewEvent constructor.
     *
     * @param HttpKernelInterface $kernel
     * @param RpcRequestInterface $request
     * @param mixed               $response
     */
    public function __construct(HttpKernelInterface $kernel, RpcRequestInterface $request, $response)
    {
        parent::__construct($kernel, $request);
        $this->response = $response;
    }

    /** @return mixed */
    public function getResponse()
    {
        return $this->response;
    }

    /** @param mixed $response */
    public function setResponse($response)
    {
        $this->response = $response;
    }

    /** @return bool */
    public function hasResponse()
    {
        return null !== $this->response;
    }
}
