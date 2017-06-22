<?php

namespace Bankiru\Api\Rpc\Routing\Annotation;

/**
 * Class Method
 *
 * Annotation class for @Method().
 *
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
class Method
{
    public $name;
    public $method;
    public $context        = [];
    public $defaultContext = true;
    public $inherit        = true;
    public $options        = [];

    public function __construct(array $values)
    {
        if (array_key_exists('value', $values)) {
            $values['method'] = $values['value'];
            unset($values['value']);
        }

        if (!array_key_exists('method', $values)) {
            throw new \RuntimeException('Specify "method" parameter for annotation');
        }

        if (!array_key_exists('name', $values)) {
            $values['name'] = $values['method'];
        }

        foreach ($values as $k => $v) {
            if (!method_exists($this, $name = 'set' . $k)) {
                throw new \RuntimeException(sprintf('Unknown key "%s" for annotation "@%s".', $k, get_class($this)));
            }

            $this->$name($v);
        }
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param mixed $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * @return array
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param array $context
     */
    public function setContext($context)
    {
        $this->context = $context;
    }

    /**
     * @return boolean
     */
    public function isDefaultContext()
    {
        return $this->defaultContext;
    }

    /**
     * @param boolean $defaultContext
     */
    public function setDefaultContext($defaultContext)
    {
        $this->defaultContext = $defaultContext;
    }

    /**
     * @return boolean
     */
    public function isInherit()
    {
        return $this->inherit;
    }

    /**
     * @param boolean $inherit
     */
    public function setInherit($inherit)
    {
        $this->inherit = (bool)$inherit;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options = null)
    {
        $this->options = $options;
    }
}
