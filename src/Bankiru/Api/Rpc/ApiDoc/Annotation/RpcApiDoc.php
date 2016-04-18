<?php
/**
 * Created by PhpStorm.
 * User: batanov.pavel
 * Date: 10.03.2016
 * Time: 13:14
 */

namespace Bankiru\Api\Rpc\ApiDoc\Annotation;

use Bankiru\Api\Rpc\Routing\Route;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Class RpcApiDoc
 *
 * @package Bankiru\Api\NelmioRpc\Annotation
 * @Annotation
 * @Target("METHOD")
 */
class RpcApiDoc extends ApiDoc
{
    /** @var  Route */
    private $rpcMethod;
    private $endpoint;
    private $classname;

    public function __construct(array $data)
    {
        parent::__construct($data);
        if (isset($data['rpc-method'])) {
            $this->setRpcMethod($data['rpc-method']);
        }
        if (isset($data['endpoint'])) {
            $this->setEndpoint($data['endpoint']);
        }
        if (isset($data['classname'])) {
            $this->setClassname($data['classname']);
        }
    }

    /**
     * @return mixed
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * @param mixed $endpoint
     */
    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;
    }

    public function getResource()
    {
        return $this->getClassname();
    }

    /**
     * @return mixed
     */
    public function getClassname()
    {
        return $this->classname;
    }

    /**
     * @param mixed $classname
     */
    public function setClassname($classname)
    {
        $this->classname = $classname;
    }

    /**
     * @return array
     */
    public function getStatusCodes()
    {
        $data = $this->toArray();

        return array_key_exists('statusCodes', $data) ? $data['statusCodes'] : [];
    }

    public function toArray()
    {
        $array           = parent::toArray();
        $array['uri']    = $this->getRpcMethod() ? $this->getRpcMethod()->getMethod() : '';
//        $array['method'] = 'RPC';

        return $array;
    }

    public function getRpcMethod()
    {
        return $this->rpcMethod;
    }

    public function setRpcMethod(Route $method)
    {
        $this->rpcMethod = $method;
    }
}
