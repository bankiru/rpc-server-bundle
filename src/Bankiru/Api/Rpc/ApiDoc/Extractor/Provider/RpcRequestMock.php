<?php
/**
 * Created by PhpStorm.
 * User: batanov.pavel
 * Date: 16.03.2016
 * Time: 12:40
 */

namespace Bankiru\Api\Rpc\ApiDoc\Extractor\Provider;

use Bankiru\Api\Rpc\Http\RequestInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Class RpcRequestMock
 *
 * @package Bankiru\Api\Rpc\ApiDoc\Extractor\Provider
 * @internal
 */
// private
final class RpcRequestMock implements RequestInterface
{
    /** @var  string */
    private $method;
    /** @var \stdClass|\stdClass[]|null */
    private $parameters;
    /** @var  ParameterBag */
    private $attributes;

    /**
     * RpcRequestMock constructor.
     *
     * @param string                     $method
     * @param null|\stdClass|\stdClass[] $parameters
     * @param ParameterBag               $attributes
     */
    public function __construct($method, $parameters, ParameterBag $attributes)
    {
        $this->method     = $method;
        $this->parameters = $parameters;
        $this->attributes = $attributes;
    }

    /**
     * @return ParameterBag
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /** @return string */
    public function getMethod()
    {
        return $this->parameters;
    }

    /** @return \stdClass|\stdClass[]|null */
    public function getParameters()
    {
        return $this->method;
    }
}
