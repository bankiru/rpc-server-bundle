<?php
/**
 * Created by PhpStorm.
 * User: batanov.pavel
 * Date: 10.03.2016
 * Time: 12:27
 */

namespace Bankiru\Api\Rpc\Routing;

class RouterCollection
{
    /** @var  Router[] */
    private $routers = [];

    /**
     * @param $endpoint
     *
     * @return Router
     * @throws \OutOfBoundsException
     */
    public function getRouter($endpoint)
    {
        if (!$this->hasRouter($endpoint)) {
            throw new \OutOfBoundsException('Router for endpoint ' . $endpoint . ' not present');
        }

        return $this->routers[$endpoint];
    }

    /**
     * @param string $endpoint
     *
     * @return bool
     */
    public function hasRouter($endpoint)
    {
        return array_key_exists($endpoint, $this->routers);
    }

    /**
     * @param string $endpoint
     * @param Router $router
     *
     * @throws \LogicException
     */
    public function addRouter($endpoint, Router $router)
    {
        if ($this->hasRouter($endpoint)) {
            throw new \LogicException('Router for endpoint ' . $endpoint . ' already present');
        }

        $this->routers[$endpoint] = $router;
    }

    /**
     * @return Router[]
     */
    public function all()
    {
        return $this->routers;
    }
}
