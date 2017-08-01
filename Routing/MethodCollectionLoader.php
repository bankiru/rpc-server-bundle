<?php

namespace Bankiru\Api\Rpc\Routing;

interface MethodCollectionLoader
{
    /**
     * Loads method collection
     *
     * @return MethodCollection
     */
    public function loadCollection();
}
