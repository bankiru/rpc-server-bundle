<?php

namespace Bankiru\Api\Rpc\Test\Rpc;

use Bankiru\Api\Rpc\Routing\Annotation\Method;
use Bankiru\Api\Rpc\Test\Impl\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Method("security/")
 */
final class SecurityController extends Controller
{
    /**
     * @Method("public")
     * @Security("is_granted('IS_AUTHENTICATED_ANONYMOUSLY')")
     */
    public function publicAction()
    {
        return new Response(['success' => true]);
    }

    /**
     * @Method("private")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function privateAction()
    {
        return new Response(['success' => true]);
    }
}
