<?php

namespace Bankiru\Api\Rpc\Routing;

final class ResourceMethodCollectionLoader implements MethodCollectionLoader
{
    /**
     * @var array
     */
    private $resources;
    /**
     * @var LoaderResolverInterface
     */
    private $resolver;
    /**
     * @var array
     */
    private $context;

    /**
     * ResourceMethodCollectionLoader constructor.
     *
     * @param LoaderResolverInterface $resolver
     * @param array                   $resources
     * @param array                   $context
     */
    public function __construct(LoaderResolverInterface $resolver, array $resources, array $context = [])
    {
        $this->resources = $resources;
        $this->resolver  = $resolver;
        $this->context   = $context;
    }

    /** {@inheritdoc} */
    public function loadCollection()
    {
        $collection = new MethodCollection();

        foreach ($this->resources as $resource) {
            $loader = $this->resolver->resolve($resource);

            if (false === $loader) {
                throw new \RuntimeException(sprintf('Could not resolve loader for resource "%s"', $resource));
            }

            $collection->addCollection($loader->load($resource));
        }

        foreach ($this->context as $item) {
            $collection->addContext($item);
        }

        return $collection;
    }
}
