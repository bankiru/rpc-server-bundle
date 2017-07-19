<?php

namespace Bankiru\Api\Rpc\Event;

class GetResponseEvent extends RpcResponseEvent
{
    public function getEndpoint()
    {
        return $this->getRequest()->getAttributes()->get('_endpoint');
    }
}
