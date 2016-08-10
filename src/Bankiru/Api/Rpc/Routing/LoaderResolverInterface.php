<?php

namespace Bankiru\Api\Rpc\Routing;

interface LoaderResolverInterface
{
    /**
     * Returns a loader able to load the resource.
     *
     * @param mixed       $resource A resource
     * @param string|null $type     The resource type or null if unknown
     *
     * @return LoaderInterface|false The loader or false if none is able to load the resource
     */
    public function resolve($resource, $type = null);
}
