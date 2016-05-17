<?php
/**
 * Created by PhpStorm.
 * User: batanov.pavel
 * Date: 16.05.2016
 * Time: 13:44
 */

namespace Bankiru\Api\Rpc\Tests;

use Bankiru\Api\Rpc\Exception\InvalidMethodParametersException;
use Bankiru\Api\Rpc\Routing\Exception\MethodNotFoundException;
use Bankiru\Api\Rpc\Tests\Fixtures\Kernel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RpcTest extends WebTestCase
{
    public function getArgumentVariants()
    {
        return [
            'missing all'                         => [[], false],
            'missing array'                       => [['noDefault' => 1], false],
            'not an array'                        => [['noDefault' => 1, 'array' => 2], false],
            'correct'                             => [['noDefault' => 1, 'array' => ['test1' => 2, 'abc']], true],
            'correct 2'                           => [['noDefault' => 1, 'array' => ['test', 'test2']], true],
            'correct w explicit default override' => [
                ['default' => 'new_value', 'noDefault' => 1, 'array' => []],
                true,
            ],
        ];
    }

    /**
     * @dataProvider getArgumentVariants
     *
     * @param array $args
     * @param bool  $valid
     *
     * @throws InvalidMethodParametersException
     * @throws \Error
     */
    public function testController(array $args, $valid)
    {
        $client = self::createClient();
        try {
            $client->request(
                'POST',
                '/test/',
                array_replace(['method' => 'test/method',], $args)
            );
        } catch (InvalidMethodParametersException $e) {
            if ($valid) {
                throw $e;
            }
        }
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
        $this->setExpectedExceptionRegExp($exception);
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
