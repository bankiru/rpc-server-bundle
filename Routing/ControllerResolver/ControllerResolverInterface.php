<?php

namespace Bankiru\Api\Rpc\Routing\ControllerResolver;

use Bankiru\Api\Rpc\RpcRequestInterface;

interface ControllerResolverInterface
{
    /**
     * @param RpcRequestInterface $request
     *
     * @return callable|false
     * @throws \InvalidArgumentException
     */
    public function getController(RpcRequestInterface $request);

    /**
     * @param RpcRequestInterface $request
     * @param                     $controller
     *
     * @return array
     * @throws \RuntimeException
     */
    public function getArguments(RpcRequestInterface $request, $controller);
}
