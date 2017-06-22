<?php

namespace Bankiru\Api\Rpc\Tests\Fixtures\Rpc;

use Bankiru\Api\Rpc\Controller\RpcController;
use Bankiru\Api\Rpc\Routing\ControllerResolver\ControllerResolverInterface;
use Bankiru\Api\Rpc\Tests\Fixtures\Impl\Request as RpcRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class TestController extends RpcController
{
    public function rpcAction(Request $request)
    {
        $args   = $request->request;
        $method = $args->get('method');
        $args->remove('method');
        $rpcRequest = new RpcRequest($method, $args->all());

        return new JsonResponse(
            ['body' => $this->getResponse($rpcRequest, $request->attributes->get('_route'))->getBody()]
        );
    }

    /**
     * @return ControllerResolverInterface
     */
    protected function getResolver()
    {
        return $this->get('rpc.controller_resolver');
    }
}
