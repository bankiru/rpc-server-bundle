<?php
/**
 * Created by PhpStorm.
 * User: batanov.pavel
 * Date: 16.02.2016
 * Time: 9:14
 */

namespace Bankiru\Api\Rpc\Routing;


interface LoaderInterface
{
    /**
     * Loads a resource.
     *
     * @param mixed       $resource The resource
     * @param string|null $type     The resource type or null if unknown
     *
     * @throws \Exception If something went wrong
     */
    public function load($resource, $type = null);

    /**
     * Returns whether this class supports the given resource.
     *
     * @param mixed       $resource A resource
     * @param string|null $type     The resource type or null if unknown
     *
     * @return bool True if this class supports the given resource, false otherwise
     */
    public function supports($resource, $type = null);

    /**
     * @return LoaderResolverInterface
     */
    public function getResolver();

    /**
     * @param LoaderResolverInterface $resolver
     */
    public function setResolver($resolver);
}
