<?php

namespace Bankiru\Api\Rpc\Test\Impl;

use ScayTrase\Api\Rpc\RpcErrorInterface;
use ScayTrase\Api\Rpc\RpcResponseInterface;

final class Response implements RpcResponseInterface
{
    private $body;

    /**
     * Response constructor.
     *
     * @param $body
     */
    public function __construct($body)
    {
        $this->body = $body;
    }

    /** @return bool */
    public function isSuccessful()
    {
        return true;
    }

    /** @return RpcErrorInterface|null */
    public function getError()
    {
        return null;
    }

    /** @return \stdClass|array|mixed|null */
    public function getBody()
    {
        return $this->body;
    }
}
