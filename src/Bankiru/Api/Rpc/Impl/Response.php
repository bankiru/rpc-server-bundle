<?php
/**
 * Created by PhpStorm.
 * User: batanov.pavel
 * Date: 16.05.2016
 * Time: 13:53
 */

namespace Bankiru\Api\Rpc\Impl;

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
    public function __construct($body) { $this->body = $body; }


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
