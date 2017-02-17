<?php

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
