<?php

namespace Bankiru\Api\Rpc\Listener;

use Bankiru\Api\Rpc\Event\GetExceptionResponseEvent;
use Psr\Log\LoggerInterface;

final class ExceptionListener
{
    /** @var LoggerInterface */
    private $logger;

    /**
     * ExceptionHandlerListener constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onException(GetExceptionResponseEvent $event)
    {
        $request   = $event->getRequest();
        $exception = $event->getException();

        $this->logger->critical(
            $exception->getMessage(),
            [
                'endpoint'   => $request->getAttributes()->get('_endpoint'),
                'method'     => $request->getMethod(),
                'parameters' => json_decode(json_encode($request->getParameters()), true),
            ]
        );
    }
}
