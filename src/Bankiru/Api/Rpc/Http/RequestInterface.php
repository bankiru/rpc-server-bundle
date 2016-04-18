<?php
/**
 * Created by PhpStorm.
 * User: batanov.pavel
 * Date: 16.03.2016
 * Time: 8:21
 */

namespace Bankiru\Api\Rpc\Http;

use ScayTrase\Api\Rpc\RpcRequestInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

interface RequestInterface extends RpcRequestInterface
{
    /**
     * @return ParameterBag
     */
    public function getAttributes();
}
