<?php

namespace Bankiru\Api\Rpc\Impl;

use Bankiru\Api\Rpc\Http\RequestInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

final class Request implements RequestInterface
{
    /** @var  ParameterBag */
    private $attributes;
    /** @var  string */
    private $method;
    /** @var  array|\stdClass */
    private $parameters;

    /**
     * Request constructor.
     *
     * @param ParameterBag    $attributes
     * @param string          $method
     * @param array|\stdClass $parameters
     */
    public function __construct($method, $parameters, ParameterBag $attributes = null)
    {
        $this->attributes = $attributes ?: new ParameterBag();
        $this->method     = $method;
        $this->parameters = $parameters;
    }

    /** {@inheritdoc} */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /** {@inheritdoc} */
    public function getMethod()
    {
        return $this->method;
    }

    /** {@inheritdoc} */
    public function getParameters()
    {
        return $this->parameters;
    }
}
