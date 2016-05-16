<?php
/**
 * Created by PhpStorm.
 * User: batanov.pavel
 * Date: 17.05.2016
 * Time: 8:44
 */

namespace Bankiru\Api\Rpc\Event;

use Bankiru\Api\Rpc\RpcEvent;
use ScayTrase\Api\Rpc\RpcResponseInterface;

class RpcResponseEvent extends RpcEvent
{
    /** @var  RpcResponseInterface|null */
    protected $response;

    /** @return bool */
    public function hasResponse()
    {
        return null !== $this->response;
    }

    /** @return RpcResponseInterface */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param RpcResponseInterface $response
     */
    public function setResponse(RpcResponseInterface $response)
    {
        $this->response = $response;
    }
}
