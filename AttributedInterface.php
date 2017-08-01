<?php

namespace Bankiru\Api\Rpc;

use Symfony\Component\HttpFoundation\ParameterBag;

interface AttributedInterface
{
    /**
     * @return ParameterBag
     */
    public function getAttributes();
}
