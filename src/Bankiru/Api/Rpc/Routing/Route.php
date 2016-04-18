<?php
/**
 * Created by PhpStorm.
 * User: batanov.pavel
 * Date: 12.02.2016
 * Time: 13:51
 */

namespace Bankiru\Api\Rpc\Routing;

class Route
{
    /** @var  string */
    private $method;
    /** @var  string */
    private $controller;
    /** @var  array */
    private $context;
    /** @var bool */
    private $defaultContext;

    /**
     * Route constructor.
     *
     * @param string $method
     * @param string $controller
     * @param array  $context
     * @param bool   $defaultContext
     */
    public function __construct($method, $controller, array $context, $defaultContext = true)
    {
        $this->method         = $method;
        $this->controller     = $controller;
        $this->context        = $context;
        $this->defaultContext = (bool)$defaultContext;
    }

    /**
     * @param boolean $defaultContext
     */
    public function setDefaultContext($defaultContext)
    {
        $this->defaultContext = (bool)$defaultContext;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $method
     */
    public function setMethod($method)
    {
        $this->method = (string)$method;
    }

    /**
     * @return string[]
     */
    public function getContext()
    {
        return array_unique($this->context);
    }

    /**
     * @param array $context
     */
    public function setContext($context)
    {
        $this->context = $context;
    }

    /**
     * @return bool
     */
    public function includeDefaultContext()
    {
        return $this->defaultContext;
    }

    /**
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @param string $controller
     */
    public function setController($controller)
    {
        $this->controller = $controller;
    }

    public function addContext($context)
    {
        $this->context[] = $context;
    }

    public function inheritContext()
    {
        return true;
    }
}
