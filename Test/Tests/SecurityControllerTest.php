<?php

namespace Bankiru\Api\Rpc\Test\Tests;

use Bankiru\Api\Rpc\Test\Kernel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SecurityControllerTest extends WebTestCase
{
    public function testPublicAction()
    {
        $client = self::createClient();

        $client->request(
            'POST',
            '/test/',
            ['method' => 'security/public']
        );

        self::assertTrue($client->getResponse()->isSuccessful());
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\InsufficientAuthenticationException
     * @expectedExceptionMessage Full authentication is required to access this resource.
     */
    public function testPrivateAction()
    {
        $client = self::createClient();

        $client->request(
            'POST',
            '/test/',
            ['method' => 'security/private']
        );

        self::assertTrue($client->getResponse()->isSuccessful());
    }

    protected static function getKernelClass()
    {
        return Kernel::class;
    }
}
