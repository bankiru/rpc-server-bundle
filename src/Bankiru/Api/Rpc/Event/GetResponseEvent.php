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

class GetResponseEvent extends RpcResponseEvent
{
    public function getEndpoint()
    {
        return $this->getRequest()->getAttributes()->get('_endpoint');
    }
}
