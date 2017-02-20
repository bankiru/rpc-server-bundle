<?php

namespace Bankiru\Api\Rpc\Routing\Exception;

use Bankiru\Api\Rpc\Exception\RpcException;

class MethodNotFoundException extends \Exception implements RpcException
{
    private $method;

    /**
     * MethodNotFoundException constructor.
     *
     * @param $method
     */
    public function __construct($method)
    {
        parent::__construct(sprintf('No route found to process method "%s"', $method));
        $this->method = $method;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }
}
