<?php

namespace Bankiru\Api\Rpc\Event;

use Bankiru\Api\Rpc\RpcRequestInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class GetExceptionResponseEvent extends RpcResponseEvent
{
    /** @var \Exception */
    private $exception;

    public function __construct(HttpKernelInterface $kernel, RpcRequestInterface $request, \Exception $exception)
    {
        parent::__construct($kernel, $request);
        $this->exception = $exception;
    }

    /** @return \Exception */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * @param \Exception $exception
     */
    public function setException($exception)
    {
        $this->exception = $exception;
    }
}
