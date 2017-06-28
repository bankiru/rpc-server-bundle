<?php

namespace Bankiru\Api\Rpc\Rpc;

use ScayTrase\Api\Rpc\RpcClientInterface;
use ScayTrase\Api\Rpc\RpcRequestInterface;

final class ProxyController
{
    /** @var RpcClientInterface */
    private $client;

    /**
     * ProxyController constructor.
     *
     * @param RpcClientInterface $client
     */
    public function __construct(RpcClientInterface $client)
    {
        $this->client = $client;
    }

    public function handle(RpcRequestInterface $request)
    {
        return $this->client->invoke($request);
    }
}
