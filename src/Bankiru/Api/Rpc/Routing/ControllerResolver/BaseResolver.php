<?php
/**
 * Created by PhpStorm.
 * User: batanov.pavel
 * Date: 11.02.2016
 * Time: 18:41
 */

namespace Bankiru\Api\Rpc\Routing\ControllerResolver;

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
        $this->logger = $logger;

        if (null === $this->logger) {
            $this->logger = new NullLogger();
        }
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

        return $this->doGetArguments($request, $controller, $r->getParameters());
    }

    /**
     * @param RequestInterface       $request
     * @param                        $controller
     * @param \ReflectionParameter[] $parameters
     *
     * @return array
     * @throws \RuntimeException
     */
    protected function doGetArguments(RequestInterface $request, $controller, array $parameters)
    {
        $attributes = $request->getAttributes()->all();
        $arguments  = [];
        foreach ($parameters as $param) {
            if (is_array($request->getParameters()) && array_key_exists($param->name, $request->getParameters())) {
                $arguments[] = $request->getParameters()[$param->name];
            } elseif (array_key_exists($param->name, $attributes)) {
                $arguments[] = $attributes[$param->name];
            } elseif ($param->getClass() && $param->getClass()->isInstance($request)) {
                $arguments[] = $request;
            } elseif ($param->isDefaultValueAvailable()) {
                $arguments[] = $param->getDefaultValue();
            } else {
                if (is_array($controller)) {
                    $repr = sprintf('%s::%s()', get_class($controller[0]), $controller[1]);
                } elseif (is_object($controller)) {
                    $repr = get_class($controller);
                } else {
                    $repr = $controller;
                }

                throw new \RuntimeException(
                    sprintf(
                        'Controller "%s" requires that you provide a value for the "$%s" argument ' .
                        '(because there is no default value or because there is a non optional argument after this one).',
                        $repr,
                        $param->name
                    )
                );
            }
        }

        return $arguments;
    }
}
