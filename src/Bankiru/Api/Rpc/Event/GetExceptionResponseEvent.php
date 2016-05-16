<?php
/**
 * Created by PhpStorm.
 * User: batanov.pavel
 * Date: 11.02.2016
 * Time: 18:57
 */

namespace Bankiru\Api\Rpc\Event;

use Bankiru\Api\Rpc\Http\RequestInterface;
use Bankiru\Api\Rpc\RpcEvent;
use ScayTrase\Api\Rpc\RpcResponseInterface;
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
}
