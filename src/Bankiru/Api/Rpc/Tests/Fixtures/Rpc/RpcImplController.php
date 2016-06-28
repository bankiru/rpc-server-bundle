<?php
/**
 * Created by PhpStorm.
 * User: batanov.pavel
 * Date: 16.05.2016
 * Time: 11:46
 */

namespace Bankiru\Api\Rpc\Tests\Fixtures\Rpc;

use Bankiru\Api\Rpc\Impl\Response;
use Bankiru\Api\Rpc\Routing\Annotation\Method;
use ScayTrase\Api\Rpc\RpcRequestInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class RpcImplController extends Controller
{
    /**
     * @param string              $noDefault
     * @param string              $default
     * @param array               $array
     * @param RpcRequestInterface $request
     *
     * @Method("annotation", inherit=false, context={"annotation-non-inherit"}, defaultContext=false)
     *
     * @return JsonResponse
     */
    public function testAction($noDefault, $default = 'test', array $array, RpcRequestInterface $request)
    {
        return new Response('success');
    }
}
