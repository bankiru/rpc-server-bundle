<?php
/**
 * Created by PhpStorm.
 * User: batanov.pavel
 * Date: 16.02.2016
 * Time: 9:29
 */

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
