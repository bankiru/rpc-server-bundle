<?php
/**
 * Created by PhpStorm.
 * User: batanov.pavel
 * Date: 12.02.2016
 * Time: 13:15
 */

namespace Bankiru\Api\Rpc\Routing;

use Symfony\Component\Config\Resource\ResourceInterface;

class MethodCollection implements \IteratorAggregate, \Countable
{
    /** @var  Route[] */
    private $routes = [];
    /**
     * @var ResourceInterface[]
     */
    private $resources = [];

    /**
     * @param $method
     *
     * @return Route
     * @throws \OutOfBoundsException
     */
    public function get($method)
    {
        if (!$this->has($method)) {
            throw new \OutOfBoundsException();
        }

        return $this->routes[$method];
    }

    public function has($method)
    {
        return array_key_exists($method, $this->routes);
    }

    public function add($method, Route $route)
    {
        if ($this->has($method)) {
            throw new \LogicException(sprintf('Trying to replace method %s', $method));
        }

        $this->replace($method, $route);
    }

    public function replace($method, Route $route)
    {
        unset($this->routes[$method]);

        $this->routes[$method] = $route;
    }

    public function addPrefix($prefix)
    {
        if ('' === $prefix) {
            return;
        }

        foreach ($this->routes as $name => $route) {
            unset($this->routes[$name]);
            $method = $prefix . $route->getMethod();
            $route->setMethod($method);
            $this->routes[$method] = $route;
        }
    }

    public function addContext($context)
    {
        foreach ($this->routes as $route) {
            if ($route->inheritContext()) {
                $route->addContext($context);
            }
        }
    }

    public function addCollection(MethodCollection $collection)
    {
        // we need to remove all routes with the same names first because just replacing them
        // would not place the new route at the end of the merged array
        foreach ($collection->all() as $name => $route) {
            unset($this->routes[$name]);
            $this->routes[$name] = $route;
        }

        $this->resources = array_merge($this->resources, $collection->getResources());
    }

    public function all()
    {
        return $this->routes;
    }

    /**
     * Returns an array of resources loaded to build this collection.
     *
     * @return ResourceInterface[] An array of resources
     */
    public function getResources()
    {
        return array_unique($this->resources);
    }

    /**
     * Gets the current RouteCollection as an Iterator that includes all routes.
     *
     * It implements \IteratorAggregate.
     *
     * @see all()
     *
     * @return \ArrayIterator An \ArrayIterator object for iterating over routes
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->routes);
    }

    /**
     * Gets the number of Routes in this collection.
     *
     * @return int The number of routes
     */
    public function count()
    {
        return count($this->routes);
    }

    /**
     * @param ResourceInterface $resource
     */
    public function addResource(ResourceInterface $resource) { $this->resources[] = $resource; }

    /**
     * @param string $method
     *
     * @return Route|null
     */
    public function match($method)
    {
        foreach ($this->routes as $route) {
            if ($route->getMethod() === $method) {
                return $route;
            }
        }

        return null;
    }
}
