<?php
/**
 * Created by PhpStorm.
 * User: batanov.pavel
 * Date: 11.02.2016
 * Time: 18:27
 */

namespace Bankiru\Api\Rpc\Event;

use Bankiru\Api\Rpc\Http\RequestInterface;
use Bankiru\Api\Rpc\RpcEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ViewEvent extends RpcEvent
{
    /** @var  mixed */
    private $response;

    /**
     * ViewEvent constructor.
     *
     * @param HttpKernelInterface $kernel
     * @param RequestInterface    $request
     * @param mixed               $response
     */
    public function __construct(HttpKernelInterface $kernel, RequestInterface $request, $response)
    {
        parent::__construct($kernel, $request);
        $this->response = $response;
    }

    /** @return mixed */
    public function getResponse() { return $this->response; }

    /** @param mixed $response */
    public function setResponse($response) { $this->response = $response; }

    /** @return bool */
    public function hasResponse() { return null !== $this->response; }
}
