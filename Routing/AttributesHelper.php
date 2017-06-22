<?php

namespace Bankiru\Api\Rpc\Routing;

final class AttributesHelper
{
    /**
     * Return default attributes for route
     *
     * @param Route  $method
     * @param string $name
     *
     * @return array
     */
    public static function getAttributes(Route $method, $name)
    {
        return [
            '_route'                => $name,
            '_controller'           => $method->getController(),
            '_with_default_context' => $method->includeDefaultContext(),
            '_context'              => $method->getContext(),
        ];
    }
}
