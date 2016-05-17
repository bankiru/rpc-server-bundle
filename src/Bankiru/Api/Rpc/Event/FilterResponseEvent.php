<?php
/**
 * Created by PhpStorm.
 * User: batanov.pavel
 * Date: 11.02.2016
 * Time: 18:28
 */

namespace Bankiru\Api\Rpc\Event;


use Bankiru\Api\Rpc\Http\RequestInterface;
use Bankiru\Api\Rpc\RpcEvent;
use ScayTrase\Api\Rpc\RpcResponseInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class FilterResponseEvent extends RpcEvent
{
    /** @var RpcResponseInterface|null */
    private $response;

    public function __construct(
        HttpKernelInterface $kernel,
        RequestInterface $request,
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
