<?php

namespace Bankiru\Api\Rpc\Routing\Exception;

class CircularReferenceLoaderException extends \LogicException implements FileLoaderException
{
    public static function fromPaths(array $paths)
    {
        return new static(
            sprintf('Circular reference detected in [%s]', implode(',', array_keys($paths)))
        );
    }
}
