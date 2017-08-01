<?php

namespace Bankiru\Api\Rpc\Listener;

use Bankiru\Api\Rpc\Event\GetExceptionResponseEvent;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class ExceptionListener
{
    /** @var LoggerInterface */
    private $logger;
    /**
     * @var bool
     */
    private $debug;

    /**
     * ExceptionHandlerListener constructor.
     *
     * @param bool            $debug
     * @param LoggerInterface $logger
     */
    public function __construct($debug = false, LoggerInterface $logger = null)
    {
        $this->logger = $logger ?: new NullLogger();
        $this->debug  = $debug;
    }

    public function onException(GetExceptionResponseEvent $event)
    {
        $request   = $event->getRequest();
        $exception = $event->getException();

        $context = [
            'endpoint' => $request->getAttributes()->get('_endpoint'),
            'method'   => $request->getMethod(),
        ];

        if ($this->debug) {
            $context['parameters'] = json_decode(json_encode($request->getParameters()), true);
        }

        $this->logger->critical($exception->getMessage(), $context);
    }
}
