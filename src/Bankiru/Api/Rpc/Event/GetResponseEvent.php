<?php
/**
 * Created by PhpStorm.
 * User: batanov.pavel
 * Date: 11.02.2016
 * Time: 18:30
 */

namespace Bankiru\Api\Rpc\Event;


use Bankiru\Api\Rpc\RpcEvent;
use ScayTrase\Api\Rpc\RpcResponseInterface;

class GetResponseEvent extends RpcEvent
{
    /** @var  RpcResponseInterface|null */
    private $response;

    public function getEndpoint()
    {
        return $this->getRequest()->getAttributes()->get('_endpoint');
    }

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
