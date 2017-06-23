<?php

namespace Bankiru\Api\Rpc\Event;

use Bankiru\Api\Rpc\Http\RequestInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class GetExceptionResponseEvent extends RpcResponseEvent
{
    /** @var \Exception */
    private $exception;

    public function __construct(HttpKernelInterface $kernel, RequestInterface $request, \Exception $exception)
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
