<?php

namespace Bankiru\Api\Rpc\Controller;

use Bankiru\Api\Rpc\Event\FilterControllerEvent;
use Bankiru\Api\Rpc\Event\FilterResponseEvent;
use Bankiru\Api\Rpc\Event\FinishRequestEvent;
use Bankiru\Api\Rpc\Event\GetExceptionResponseEvent;
use Bankiru\Api\Rpc\Event\GetResponseEvent;
use Bankiru\Api\Rpc\Event\ViewEvent;
use Bankiru\Api\Rpc\Routing\ControllerResolver\ControllerResolverInterface;
use Bankiru\Api\Rpc\Routing\Exception\MethodNotFoundException;
use Bankiru\Api\Rpc\RpcEvents;
use Bankiru\Api\Rpc\RpcRequestInterface;
use ScayTrase\Api\Rpc\RpcResponseInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ExceptionInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class RpcController implements ContainerAwareInterface
{
    /** @var  ContainerInterface */
    private $container;
    /** @var  EventDispatcherInterface */
    private $dispatcher;

    /**
     * Sets the container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container  = $container;
        $this->dispatcher = $this->get('event_dispatcher');
    }

    /**
     * @param RpcRequestInterface $request
     * @param string              $endpoint
     *
     * @return RpcResponseInterface
     * @throws \Exception
     */
    protected function getResponse(RpcRequestInterface $request, $endpoint)
    {
        $request->getAttributes()->set('_endpoint', $endpoint);

        try {
            $rpcResponse = $this->handleSingleRequest($request);
        } catch (\Exception $e) {
            $rpcResponse = $this->handleException($e, $request);
        }

        return $rpcResponse;
    }

    /**
     * @param RpcRequestInterface $request
     *
     * @return RpcResponseInterface
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws MethodNotFoundException
     */
    protected function handleSingleRequest(RpcRequestInterface $request)
    {
        // request
        $event = new GetResponseEvent($this->getKernel(), $request);
        $this->dispatcher->dispatch(RpcEvents::REQUEST, $event);
        if ($event->hasResponse()) {
            return $this->filterResponse($event->getResponse(), $request);
        }
        // load controller
        if (false === $controller = $this->getResolver()->getController($request)) {
            throw new MethodNotFoundException($request->getMethod());
        }
        $event = new FilterControllerEvent($this->getKernel(), $request, $controller);
        $this->dispatcher->dispatch(RpcEvents::CONTROLLER, $event);
        $controller = $event->getController();
        // controller arguments
        $arguments = $this->getResolver()->getArguments($request, $controller);
        // call controller
        $response = call_user_func_array($controller, $arguments);
        // view
        if (!$response instanceof RpcResponseInterface) {
            $event = new ViewEvent($this->getKernel(), $request, $response);
            $this->dispatcher->dispatch(RpcEvents::VIEW, $event);
            if ($event->hasResponse()) {
                $response = $event->getResponse();
            }
            /** @noinspection NotOptimalIfConditionsInspection */
            if (!$response instanceof RpcResponseInterface) {
                $msg = sprintf(
                    'The controller must return a RpcResponseInterface response (%s given).',
                    $this->varToString($response)
                );
                // the user may have forgotten to return something
                if (null === $response) {
                    $msg .= ' Did you forget to add a return statement somewhere in your controller?';
                }
                throw new \LogicException($msg);
            }
        }

        return $this->filterResponse($response, $request);
    }

    /**
     * @param $name
     *
     * @return object|null
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     */
    protected function get($name)
    {
        return $this->container->get($name);
    }

    /**
     * Filters a response object.
     *
     * @param RpcResponseInterface $response A Response instance
     * @param RpcRequestInterface  $request  An error message in case the response is not a Response object
     *
     * @return RpcResponseInterface The filtered Response instance
     * @throws \RuntimeException
     */
    protected function filterResponse(RpcResponseInterface $response, RpcRequestInterface $request)
    {
        $event = new FilterResponseEvent($this->getKernel(), $request, $response);
        $this->dispatcher->dispatch(RpcEvents::RESPONSE, $event);
        $this->finishRequest($request);

        return $event->getResponse();
    }

    /**
     * Publishes the finish request event, then pop the request from the stack.
     *
     * Note that the order of the operations is important here, otherwise
     * operations such as {@link RequestStack::getParentRequest()} can lead to
     * weird results.
     *
     * @param RpcRequestInterface $request
     *
     * @throws \RuntimeException
     */
    protected function finishRequest(RpcRequestInterface $request)
    {
        $this->dispatcher->dispatch(
            RpcEvents::FINISH_REQUEST,
            new FinishRequestEvent($this->getKernel(), $request)
        );
    }

    /**
     * @return ControllerResolverInterface
     */
    abstract protected function getResolver();

    /**
     * @param $var
     *
     * @return string
     */
    protected function varToString($var)
    {
        if (is_object($var)) {
            return sprintf('Object(%s)', get_class($var));
        }
        if (is_array($var)) {
            $a = [];
            foreach ($var as $k => $v) {
                $a[] = sprintf('%s => %s', $k, $this->varToString($v));
            }

            return sprintf('Array(%s)', implode(', ', $a));
        }
        if (is_resource($var)) {
            return sprintf('Resource(%s)', get_resource_type($var));
        }
        if (null === $var) {
            return 'null';
        }
        if (false === $var) {
            return 'false';
        }
        if (true === $var) {
            return 'true';
        }

        return (string)$var;
    }

    /**
     * Handles an exception by trying to convert it to a Response.
     *
     * @param \Exception          $e       An \Exception instance
     * @param RpcRequestInterface $request A Request instance
     *
     * @return RpcResponseInterface A Response instance
     *
     * @throws \Exception
     */
    protected function handleException(\Exception $e, RpcRequestInterface $request)
    {
        $event = new GetExceptionResponseEvent($this->getKernel(), $request, $e);
        $this->dispatcher->dispatch(RpcEvents::EXCEPTION, $event);
        // a listener might have replaced the exception
        $e = $event->getException();
        if (!$event->hasResponse()) {
            $this->finishRequest($request);
            throw $e;
        }
        $response = $event->getResponse();

        try {
            return $this->filterResponse($response, $request);
        } catch (\Exception $e) {
            return $response;
        }
    }

    /**
     * @return KernelInterface
     * @throws \RuntimeException
     */
    private function getKernel()
    {
        try {
            /** @var KernelInterface|null $kernel */
            $kernel = $this->get('kernel');
        } catch (ExceptionInterface $e) {
            throw new \RuntimeException('Cannot obtain Kernel from container: ' . $e->getMessage(), $e->getCode(), $e);
        }
        if (null === $kernel) {
            throw new \RuntimeException('Cannot obtain Kernel from container');
        }

        return $kernel;
    }
}
