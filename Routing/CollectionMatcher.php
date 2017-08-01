<?php

namespace Bankiru\Api\Rpc\Routing;

use Bankiru\Api\Rpc\Routing\Exception\MethodNotFoundException;

final class CollectionMatcher implements MethodMatcher
{
    /**
     * @var MethodCollection
     */
    private $collection;

    /**
     * CollectionMatcher constructor.
     *
     * @param MethodCollection $collection
     */
    public function __construct(MethodCollection $collection)
    {
        $this->collection = $collection;
    }

    /** {@inheritdoc} */
    public function match($method)
    {
        if (!$this->collection->has($method)) {
            throw new MethodNotFoundException($method);
        }

        return AttributesHelper::getAttributes($this->collection->get($method), $method);
    }
}
