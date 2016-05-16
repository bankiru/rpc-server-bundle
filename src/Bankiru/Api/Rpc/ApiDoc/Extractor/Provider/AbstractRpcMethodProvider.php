<?php
/**
 * User: scaytrase
 * Created: 2016-03-13 17:27
 */

namespace Bankiru\Api\Rpc\ApiDoc\Extractor\Provider;

use Bankiru\Api\Rpc\ApiDoc\Annotation\RpcApiDoc;
use Bankiru\Api\Rpc\Impl\Request;
use Bankiru\Api\Rpc\Routing\ControllerResolver\ControllerResolverInterface;
use Bankiru\Api\Rpc\Routing\Route as Method;
use Doctrine\Common\Annotations\Reader;
use Nelmio\ApiDocBundle\Extractor\AnnotationsProviderInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

abstract class AbstractRpcMethodProvider implements AnnotationsProviderInterface
{
    /** @var ControllerResolverInterface */
    protected $resolver;
    /** @var Reader */
    protected $reader;
    /** @var RouteCollection */
    protected $httpCollection;

    /**
     * AbstractRpcMethodProvider constructor.
     *
     * @param ControllerResolverInterface $resolver
     * @param Reader                      $reader
     * @param RouteCollection             $httpCollection
     */
    public function __construct(ControllerResolverInterface $resolver, Reader $reader, RouteCollection $httpCollection)
    {
        $this->resolver       = $resolver;
        $this->reader         = $reader;
        $this->httpCollection = $httpCollection;
    }


    /**
     * @param Method $method
     * @param string $endpoint
     *
     * @return RpcApiDoc
     */
    protected function processMethod(Method $method, $endpoint)
    {
        /** @var string[] $views */
        $views = $method->getContext();
        if ($method->includeDefaultContext()) {
            $views[] = 'Default';
        }

        $views[] = 'default';

        $request = new Request($method, [], new ParameterBag(['_controller' => $method->getController()]));

        /** @var array $controller */
        $controller = $this->resolver->getController($request);
        $refl       = new \ReflectionMethod($controller[0], $controller[1]);

        /** @var RpcApiDoc $methodDoc */
        $methodDoc = $this->reader->getMethodAnnotation($refl, RpcApiDoc::class);

        if (null === $methodDoc) {
            $methodDoc = new RpcApiDoc(['resource' => $endpoint]);
        }
        $methodDoc = clone $methodDoc;

        $methodDoc->setEndpoint($endpoint);
        $methodDoc->setRpcMethod($method);
        if (null === $methodDoc->getSection()) {
            $methodDoc->setSection($endpoint);
        }
        foreach ($views as $view) {
            $methodDoc->addView($view);
        }

        $route = new Route($endpoint);
        $route->setMethods([$endpoint]);
        $route->setDefault('_controller', (get_class($controller[0]) . '::' . $controller[1]));
        $methodDoc->setRoute($route);

        return $methodDoc;
    }
}
