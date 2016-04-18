<?php
/**
 * Created by PhpStorm.
 * User: batanov.pavel
 * Date: 11.02.2016
 * Time: 18:32
 */

namespace Bankiru\Api\Rpc;

final class RpcEvents
{
    const REQUEST        = 'rpc.request';
    const CONTROLLER     = 'rpc.controller';
    const VIEW           = 'rpc.view';
    const RESPONSE       = 'rpc.response';
    const FINISH_REQUEST = 'rpc.finish-request';
    const EXCEPTION      = 'rpc.exception';
}
