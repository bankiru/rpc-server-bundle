<?php
/**
 * Created by PhpStorm.
 * User: batanov.pavel
 * Date: 12.02.2016
 * Time: 13:24
 */

namespace Bankiru\Api\Rpc\Listener;

use Bankiru\Api\Rpc\Event\GetResponseEvent;
use Bankiru\Api\Rpc\Routing\Router;

final class RouterListener
{
    /** @var  string */
    private $endpoint;

    /** @var  Router */
    private $router;

    /**
     * RouterListener constructor.
     *
     * @param string $endpoint
     * @param Router $router
     */
    public function __construct($endpoint, Router $router)
    {
        $this->endpoint = $endpoint;
        $this->router   = $router;
    }

    public function onRequest(GetResponseEvent $event)
    {
        if ($event->getEndpoint() !== $this->endpoint) {
            return;
        }

        $method = $event->getRequest()->getMethod();
        $route  = $this->router->getCollection()->match($method);
        if (null === $route) {
            return;
        }
        
        $event->getRequest()->getAttributes()->set('_route', $route);
        $event->getRequest()->getAttributes()->set('_controller', $route->getController());
        $event->getRequest()->getAttributes()->set('_with_default_context', $route->includeDefaultContext());
        $event->getRequest()->getAttributes()->set('_context', $route->getContext());
    }
}
