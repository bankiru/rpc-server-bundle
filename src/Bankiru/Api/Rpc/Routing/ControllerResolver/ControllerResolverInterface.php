<?php
/**
 * User: scaytrase
 * Created: 2016-03-13 15:40
 */
namespace Bankiru\Api\Rpc\Routing\ControllerResolver;

use Bankiru\Api\Rpc\Http\RequestInterface;

interface ControllerResolverInterface
{
    /**
     * @param RequestInterface $request
     *
     * @return callable|false
     * @throws \InvalidArgumentException
     */
    public function getController(RequestInterface $request);

    /**
     * @param RequestInterface $request
     * @param                  $controller
     *
     * @return array
     * @throws \RuntimeException
     */
    public function getArguments(RequestInterface $request, $controller);
}
