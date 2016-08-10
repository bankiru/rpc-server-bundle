<?php

namespace Bankiru\Api\Rpc\Http;

use ScayTrase\Api\Rpc\RpcRequestInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

interface RequestInterface extends RpcRequestInterface
{
    /**
     * @return ParameterBag
     */
    public function getAttributes();
}
