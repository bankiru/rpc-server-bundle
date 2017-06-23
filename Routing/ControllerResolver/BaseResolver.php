<?php

/*
 * Copyright (c) 2010-2017 Fabien Potencier
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to t * *he following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 */

namespace Bankiru\Api\Rpc\Routing\ControllerResolver;

use Bankiru\Api\Rpc\Exception\InvalidMethodParametersException;
use Bankiru\Api\Rpc\Http\RequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class BaseResolver implements ControllerResolverInterface
{
    /** @var  LoggerInterface */
    private $logger;

    /**
     * Resolver constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger ?: new NullLogger();
    }

    /** {@inheritdoc} */
    public function getController(RequestInterface $request)
    {
        if (!$controller = $request->getAttributes()->get('_controller')) {
            $this->logger->warning('Unable to look for the controller as the "_controller" parameter is missing.');

            return false;
        }

        if (is_array($controller)) {
            return $controller;
        }

        if (is_object($controller)) {
            if (method_exists($controller, '__invoke')) {
                return $controller;
            }

            throw new \InvalidArgumentException(
                sprintf(
                    'Controller "%s" for method "%s" is not callable.',
                    get_class($controller),
                    $request->getMethod()
                )
            );
        }

        if (false === strpos($controller, ':')) {
            if (method_exists($controller, '__invoke')) {
                return $this->instantiateController($controller);
            } elseif (function_exists($controller)) {
                return $controller;
            }
        }

        $callable = $this->createController($controller);

        if (!is_callable($callable)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The controller for method "%s" is not callable. %s',
                    $request->getMethod(),
                    $this->getControllerError($callable)
                )
            );
        }

        return $callable;
    }

    /**
     * Returns an instantiated controller.
     *
     * @param string $class A class name
     *
     * @return object
     */
    protected function instantiateController($class)
    {
        return new $class();
    }

    /**
     * Returns a callable for the given controller.
     *
     * @param string $controller A Controller string
     *
     * @return callable A PHP callable
     *
     * @throws \InvalidArgumentException
     */
    protected function createController($controller)
    {
        if (false === strpos($controller, '::')) {
            throw new \InvalidArgumentException(sprintf('Unable to find controller "%s".', $controller));
        }

        list($class, $method) = explode('::', $controller, 2);

        if (!class_exists($class)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
        }

        return [$this->instantiateController($class), $method];
    }

    private function getControllerError($callable)
    {
        if (is_string($callable)) {
            if (false !== strpos($callable, '::')) {
                $callable = explode('::', $callable);
            }

            if (class_exists($callable) && !method_exists($callable, '__invoke')) {
                return sprintf('Class "%s" does not have a method "__invoke".', $callable);
            }

            if (!function_exists($callable)) {
                return sprintf('Function "%s" does not exist.', $callable);
            }
        }

        if (!is_array($callable)) {
            return sprintf(
                'Invalid type for controller given, expected string or array, got "%s".',
                gettype($callable)
            );
        }

        if (2 !== count($callable)) {
            return sprintf('Invalid format for controller, expected array(controller, method) or controller::method.');
        }

        list($controller, $method) = $callable;

        if (is_string($controller) && !class_exists($controller)) {
            return sprintf('Class "%s" does not exist.', $controller);
        }

        $className = is_object($controller) ? get_class($controller) : $controller;

        if (method_exists($controller, $method)) {
            return sprintf('Method "%s" on class "%s" should be public and non-abstract.', $method, $className);
        }

        $collection = get_class_methods($controller);

        $alternatives = [];

        foreach ($collection as $item) {
            $lev = levenshtein($method, $item);

            if ($lev <= strlen($method) / 3 || false !== strpos($item, $method)) {
                $alternatives[] = $item;
            }
        }

        asort($alternatives);

        $message = sprintf('Expected method "%s" on class "%s"', $method, $className);

        if (count($alternatives) > 0) {
            $message .= sprintf(', did you mean "%s"?', implode('", "', $alternatives));
        } else {
            $message .= sprintf('. Available methods: "%s".', implode('", "', $collection));
        }

        return $message;
    }

    /** {@inheritdoc} */
    public function getArguments(RequestInterface $request, $controller)
    {
        if (is_array($controller)) {
            $r = new \ReflectionMethod($controller[0], $controller[1]);
        } elseif (is_object($controller) && !$controller instanceof \Closure) {
            $r = new \ReflectionObject($controller);
            $r = $r->getMethod('__invoke');
        } else {
            $r = new \ReflectionFunction($controller);
        }

        return $this->doGetArguments($request, $r->getParameters());
    }

    /**
     * @param RequestInterface       $request
     * @param \ReflectionParameter[] $parameters
     *
     * @return array
     * @throws \RuntimeException
     */
    protected function doGetArguments(RequestInterface $request, array $parameters)
    {
        $attributes = $request->getAttributes()->all();
        $arguments  = [];
        $missing    = [];
        foreach ($parameters as $param) {
            if (is_array($request->getParameters()) && array_key_exists($param->name, $request->getParameters())) {
                $arguments[] = $this->checkType($request->getParameters()[$param->name], $param, $request);
            } elseif (array_key_exists($param->name, $attributes)) {
                $arguments[] = $this->checkType($attributes[$param->name], $param->name, $request);
            } elseif ($param->getClass() && $param->getClass()->isInstance($request)) {
                $arguments[] = $request;
            } elseif ($param->isDefaultValueAvailable()) {
                $arguments[] = $param->getDefaultValue();
            } else {
                $missing[] = $param->name;
            }
        }

        if (count($missing) > 0) {
            throw InvalidMethodParametersException::missing($request->getMethod(), $missing);
        }

        return $arguments;
    }

    /**
     * Checks that argument matches parameter type
     *
     * @param mixed                $argument
     * @param \ReflectionParameter $param
     * @param RequestInterface     $request
     *
     * @return mixed
     */
    private function checkType($argument, \ReflectionParameter $param, RequestInterface $request)
    {
        $actual = is_object($argument) ? get_class($argument) : gettype($argument);
        if (null !== $param->getClass()) {
            $className = $param->getClass();
            if (!($argument instanceof $className)) {
                throw InvalidMethodParametersException::typeMismatch(
                    $request->getMethod(),
                    $param->name,
                    $className,
                    $actual
                );
            }
        } elseif ($param->isArray() && !is_array($argument)) {
            throw InvalidMethodParametersException::typeMismatch(
                $request->getMethod(),
                $param->name,
                'array',
                $actual
            );
        }

        return $argument;
    }
}
