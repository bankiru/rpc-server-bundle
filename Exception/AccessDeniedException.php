<?php

namespace Bankiru\Api\Rpc\Exception;

class AccessDeniedException extends \RuntimeException implements RpcException
{
    /**
     * @param string[] $roles
     *
     * @return static
     */
    public static function rolesNeeded(array $roles)
    {
        return new static('Insufficient privileges: required one of ' . implode(', ', $roles));
    }
}
