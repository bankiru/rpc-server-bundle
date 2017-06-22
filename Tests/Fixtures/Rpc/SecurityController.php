<?php

namespace Bankiru\Api\Rpc\Tests\Fixtures\Rpc;

use Bankiru\Api\Rpc\Routing\Annotation\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Method(options={"_roles":{"IS_AUTHENTICATED_ANONYMOUSLY"}})
 */
final class SecurityController extends Controller
{
    public function publicAction()
    {
    }
}
