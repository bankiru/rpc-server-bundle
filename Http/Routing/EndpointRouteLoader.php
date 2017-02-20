<?php

namespace Bankiru\Api\Rpc\Http\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

final class EndpointRouteLoader extends Loader
{
    /** @var bool */
    private $loaded = false;
    /** @var array */
    private $endpoints = [];

    /** {@inheritdoc} */
    public function load($resource, $type = null)
    {
        if ($this->loaded) {
            throw new \LogicException('Endpoint loader is already loaded');
        }

        $collection = new RouteCollection();
        foreach ($this->endpoints as $name => $endpoint) {
            // prepare a new route
            $path     = $endpoint['path'];
            $defaults = $endpoint['defaults'];
            $route    = new Route($path, $defaults);
            $route->setMethods('POST');

            $collection->add($name, $route);
        }

        $this->loaded = true;

        return $collection;
    }

    /** {@inheritdoc} */
    public function supports($resource, $type = null)
    {
        return 'endpoint' === $type;
    }

    public function addEndpoint($name, array $endpoint)
    {
        if ($this->loaded) {
            throw new \LogicException('Endpoint loader is already configured');
        }

        $this->endpoints[$name] = $endpoint;
    }
}
