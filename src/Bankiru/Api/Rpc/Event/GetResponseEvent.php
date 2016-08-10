<?php

namespace Bankiru\Api\Rpc\Event;

use Bankiru\Api\Rpc\RpcEvent;
use ScayTrase\Api\Rpc\RpcResponseInterface;

class GetResponseEvent extends RpcResponseEvent
{
    public function getEndpoint()
    {
        return $this->getRequest()->getAttributes()->get('_endpoint');
    }
}
