<?php
/**
 * Created by PhpStorm.
 * User: batanov.pavel
 * Date: 10.03.2016
 * Time: 13:03
 */

namespace Bankiru\Api\Rpc\ApiDoc\Extractor\Provider;

use Bankiru\Api\Rpc\Routing\ControllerResolver\ControllerResolverInterface;
use Bankiru\Api\Rpc\Routing\RouterCollection;
use Doctrine\Common\Annotations\Reader;
use Symfony\Component\Routing\Router;

final class MethodAnnotationsProvider extends AbstractRpcMethodProvider
{
    /** @var RouterCollection */
    private $routerCollection;

    /**
     * RpcHandler constructor.
     *
     * @param Reader $reader
     * @param ControllerResolverInterface $resolver
     * @param RouterCollection $routerCollection
     * @param Router $router
     */
    public function __construct(
        Reader $reader,
        ControllerResolverInterface $resolver,
        RouterCollection $routerCollection,
        Router $router
    ) {
        $this->routerCollection = $routerCollection;
        parent::__construct($resolver, $reader, $router->getRouteCollection());
    }

    /**
     * Returns an array ApiDoc annotations.
     *
     * @return \Nelmio\ApiDocBundle\Annotation\ApiDoc[]
     */
    public function getAnnotations()
    {
        $docs = [];
        foreach ($this->routerCollection->all() as $endpoint => $router) {
            foreach ($router->getCollection()->all() as $method) {
                $docs[] = $this->processMethod($method, $endpoint);
            }
        }

        return $docs;
    }
}
