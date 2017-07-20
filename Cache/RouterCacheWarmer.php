<?php

namespace Bankiru\Api\Rpc\Cache;

use Bankiru\Api\Rpc\Routing\MethodMatcher;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;

final class RouterCacheWarmer implements CacheWarmerInterface
{
    const CACHE_DIR = 'rpc';

    /**
     * @var MethodMatcher
     */
    private $router;

    /**
     * Constructor.
     *
     * @param MethodMatcher $router A Router instance
     */
    public function __construct(MethodMatcher $router)
    {
        $this->router = $router;
    }

    /**
     * Warms up the cache.
     *
     * @param string $cacheDir The cache directory
     */
    public function warmUp($cacheDir)
    {
        if ($this->router instanceof WarmableInterface) {
            $this->router->warmUp($cacheDir);
        }
    }

    /**
     * Checks whether this warmer is optional or not.
     *
     * @return bool always true
     */
    public function isOptional()
    {
        return true;
    }
}
