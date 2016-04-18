<?php
/**
 * Created by PhpStorm.
 * User: batanov.pavel
 * Date: 16.02.2016
 * Time: 9:20
 */

namespace Bankiru\Api\Rpc\Routing\Loader;


use Bankiru\Api\Rpc\Routing\LoaderInterface;
use Bankiru\Api\Rpc\Routing\LoaderResolverInterface;

abstract class Loader implements LoaderInterface
{
    /** @var  LoaderResolverInterface */
    private $resolver;

    /**
     * Imports a resource.
     *
     * @param mixed       $resource A resource
     * @param string|null $type     The resource type or null if unknown
     *
     * @return mixed
     */
    public function import($resource, $type = null)
    {
        return $this->resolve($resource, $type)->load($resource, $type);
    }

    /**
     * Finds a loader able to load an imported resource.
     *
     * @param mixed       $resource A resource
     * @param string|null $type     The resource type or null if unknown
     *
     * @return LoaderInterface A LoaderInterface instance
     */
    public function resolve($resource, $type = null)
    {
        if ($this->supports($resource, $type)) {
            return $this;
        }

        $loader = null === $this->resolver ? false : $this->resolver->resolve($resource, $type);

        if (false === $loader) {
            throw new \OutOfBoundsException($resource);
        }

        return $loader;
    }

    /** {@inheritdoc} */
    public function getResolver()
    {
        return $this->resolver;
    }

    /** {@inheritdoc} */
    public function setResolver($resolver)
    {
        $this->resolver = $resolver;
    }
}
