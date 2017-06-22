<?php

namespace Bankiru\Api\Rpc\Routing;

use Bankiru\Api\Rpc\Routing\Exception\MethodNotFoundException;

interface MethodMatcher
{
    /**
     * @param string $method
     *
     * @return array
     *
     * @throws MethodNotFoundException
     */
    public function match($method);
}
