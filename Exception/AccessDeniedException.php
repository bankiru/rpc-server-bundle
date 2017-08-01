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
        return new static(
            sprintf(
                'Insufficient privileges: required one of %s role to access this method',
                implode(', ', $roles)
            )
        );
    }
}
