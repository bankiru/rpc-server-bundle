<?php

namespace Bankiru\Api\Rpc\Exception;

class InvalidMethodParametersException extends \InvalidArgumentException implements RpcException
{
    /**
     * @param string   $method
     * @param string[] $missing
     *
     * @return static
     */
    public static function missing($method, array $missing)
    {
        return new static(
            sprintf(
                'Some parameters for method "%s" are missing and do not have the default value: %s',
                $method,
                implode(', ', $missing)
            )
        );
    }

    /**
     * @param string $method
     * @param string $name
     * @param string $expected
     * @param string $actual
     *
     * @return static
     */
    public static function typeMismatch($method, $name, $expected, $actual)
    {
        return new static(
            sprintf('Parameter %s for method "%s" has invalid type: %s given, %s expected', $name, $method, $actual, $expected)
        );
    }
}
