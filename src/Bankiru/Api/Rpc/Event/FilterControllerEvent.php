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

class FilterControllerEvent extends RpcEvent
{
    /** @var  callable|null */
    private $controller;

    /**
     * FilterControllerEvent constructor.
     * @param HttpKernelInterface $kernel
     * @param RequestInterface $request
     * @param string|callable $controller
     */
    public function __construct(HttpKernelInterface $kernel, RequestInterface $request, $controller)
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
