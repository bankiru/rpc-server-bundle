<?php

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
        $event->getRequest()->getAttributes()->replace($this->router->match($method));
    }
}
