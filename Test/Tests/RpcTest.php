<?php

namespace Bankiru\Api\Rpc\Test\Tests;

use Bankiru\Api\Rpc\Exception\InvalidMethodParametersException;
use Bankiru\Api\Rpc\Routing\Exception\MethodNotFoundException;
use Bankiru\Api\Rpc\Test\Kernel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RpcTest extends WebTestCase
{
    public function getValidArgumentVariants()
    {
        return [
            'correct'                             => [['noDefault' => 1, 'array' => ['test1' => 2, 'abc']]],
            'correct 2'                           => [['noDefault' => 1, 'array' => ['test', 'test2']]],
            'correct w explicit default override' => [
                ['default' => 'new_value', 'noDefault' => 1, 'array' => []],
            ],
        ];
    }

    public function getInvalidArgumentVariants()
    {
        return [
            'missing all'   => [[]],
            'missing array' => [['noDefault' => 1]],
            'not an array'  => [['noDefault' => 1, 'array' => 2]],
        ];
    }

    /**
     * @dataProvider getValidArgumentVariants
     *
     * @param array $args
     *
     * @throws InvalidMethodParametersException
     */
    public function testValidController(array $args)
    {
        $client = self::createClient();
        $client->request('POST', '/test/', array_replace(['method' => 'test/method'], $args));

        self::assertTrue($client->getResponse()->isSuccessful());
    }

    /**
     * @dataProvider getInvalidArgumentVariants
     *
     * @param array $args
     *
     * @expectedException \Bankiru\Api\Rpc\Exception\InvalidMethodParametersException
     */
    public function testInvalidController(array $args)
    {
        $client = self::createClient();
        $client->request('POST', '/test/', array_replace(['method' => 'test/method'], $args));
    }

    public function getExceptionTestData()
    {
        return [
            'unknown method'  => [
                '/not_found_endpoint/',
                ['method' => 'test/method', 'arg' => 1],
                NotFoundHttpException::class,
            ],
            'uknown endpoint' => [
                '/test/',
                ['method' => 'unknown/method', 'arg' => 1],
                MethodNotFoundException::class,
            ],
        ];
    }

    /**
     * @dataProvider getExceptionTestData
     *
     * @param string $endpoint
     * @param array  $args
     * @param string $exception FQCN
     */
    public function testException($endpoint, $args, $exception)
    {
        if (method_exists($this, 'expectException')) {
            $this->expectException($exception);
        } elseif (method_exists($this, 'setExpectedException')) {
            $this->setExpectedException($exception);
        } else {
            throw new \BadMethodCallException('Unsupported PHPUnit version');
        }

        $client = self::createClient();

        $client->request(
            'POST',
            $endpoint,
            $args
        );
    }

    protected static function getKernelClass()
    {
        return Kernel::class;
    }
}
