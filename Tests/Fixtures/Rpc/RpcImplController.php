<?php

namespace Bankiru\Api\Rpc\Tests\Fixtures\Rpc;

use Bankiru\Api\Rpc\Routing\Annotation\Method;
use Bankiru\Api\Rpc\Tests\Fixtures\Impl\Response;
use ScayTrase\Api\Rpc\RpcRequestInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

final class RpcImplController extends Controller
{
    /**
     * @param string              $noDefault
     * @param string              $default
     * @param array               $array
     * @param RpcRequestInterface $request
     *
     * @Method("annotation", inherit=false, context={"annotation-non-inherit"}, defaultContext=false)
     *
     * @return Response
     */
    public function testAction($noDefault, $default = 'test', array $array, RpcRequestInterface $request)
    {
        return new Response('success');
    }
}
