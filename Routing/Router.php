<?php

namespace Bankiru\Api\Rpc\Routing;

use Symfony\Component\Config\ConfigCacheFactory;
use Symfony\Component\Config\ConfigCacheFactoryInterface;
use Symfony\Component\Config\ConfigCacheInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;

final class Router implements MethodMatcher, WarmableInterface
{
    /** @var MethodCollectionLoader */
    private $loader;
    /** @var MethodCollection */
    private $collection;
    /** @var MethodMatcher */
    private $matcher;
    /**
     * @var string
     */
    private $name;
    /** @var ConfigCacheFactoryInterface */
    private $configCacheFactory;
    private $options = [];

    /**
     * Router constructor.
     *
     * @param MethodCollectionLoader $loader
     * @param string                 $name
     * @param array                  $options
     */
    public function __construct(MethodCollectionLoader $loader, $name, array $options = [])
    {
        $this->loader = $loader;
        $this->name   = $name;
        $this->setOptions($options);
    }

    /**
     * @return MethodCollection
     */
    public function getMethodCollection()
    {
        if (null === $this->collection) {
            $this->collection = $this->loader->loadCollection();
        }

        return $this->collection;
    }

    /** {@inheritdoc} */
    public function match($method)
    {
        return $this->getMatcher()->match($method);
    }

    /**
     * Warms up the cache.
     *
     * @param string $cacheDir The cache directory
     */
    public function warmUp($cacheDir)
    {
        $currentDir = $this->getOption('cache_dir');

        // force cache generation
        $this->setOption('cache_dir', $cacheDir);

        $this->getMatcher();
        $this->setOption('cache_dir', $currentDir);
    }

    /**
     * Sets an option.
     *
     * @param string $key   The key
     * @param mixed  $value The value
     *
     * @throws \InvalidArgumentException
     */
    public function setOption($key, $value)
    {
        if (!array_key_exists($key, $this->options)) {
            throw new \InvalidArgumentException(sprintf('The Router does not support the "%s" option.', $key));
        }

        $this->options[$key] = $value;
    }

    /**
     * Gets an option value.
     *
     * @param string $key The key
     *
     * @return mixed The value
     *
     * @throws \InvalidArgumentException
     */
    public function getOption($key)
    {
        if (!array_key_exists($key, $this->options)) {
            throw new \InvalidArgumentException(sprintf('The Router does not support the "%s" option.', $key));
        }

        return $this->options[$key];
    }

    /**
     * Sets options.
     *
     * Available options:
     *
     *   * cache_dir:              The cache directory (or null to disable caching)
     *   * debug:                  Whether to enable debugging or not (false by default)
     *
     * @param array $options An array of options
     *
     * @throws \InvalidArgumentException When unsupported option is provided
     */
    public function setOptions(array $options)
    {
        $this->options = [
            'cache_dir'           => null,
            'debug'               => false,
            'matcher_cache_class' => ucfirst($this->name) . 'MethodMatcher',
        ];

        // check option names and live merge, if errors are encountered Exception will be thrown
        $invalid = [];
        foreach ($options as $key => $value) {
            if (array_key_exists($key, $this->options)) {
                $this->options[$key] = $value;
            } else {
                $invalid[] = $key;
            }
        }

        if ($invalid) {
            throw new \InvalidArgumentException(
                sprintf('The Router does not support the following options: "%s".', implode('", "', $invalid))
            );
        }
    }

    /**
     * @return MethodMatcher
     */
    private function getMatcher()
    {
        if (null !== $this->matcher) {
            return $this->matcher;
        }

        if (null === $this->options['cache_dir'] || null === $this->options['matcher_cache_class']) {
            $this->matcher = new CollectionMatcher($this->getMethodCollection());

            return $this->matcher;
        }

        $cache = $this->getConfigCacheFactory()->cache(
            $this->options['cache_dir'] . '/rpc/' . $this->options['matcher_cache_class'] . '.php',
            function (ConfigCacheInterface $cache) {
                $dumper = new MatcherDumper();

                $options = [
                    'class' => $this->options['matcher_cache_class'],
                ];

                $resources   = $this->getMethodCollection()->getResources();
                $refl        = new \ReflectionClass(MatcherDumper::class);
                $resources[] = new FileResource($refl->getFileName());

                $cache->write($dumper->dump($this->getMethodCollection(), $options), $resources);
            }
        );

        require_once $cache->getPath();

        $this->matcher = new $this->options['matcher_cache_class']($cache);

        return $this->matcher;
    }

    /**
     * Provides the ConfigCache factory implementation, falling back to a
     * default implementation if necessary.
     *
     * @return ConfigCacheFactoryInterface $configCacheFactory
     */
    private function getConfigCacheFactory()
    {
        if (null === $this->configCacheFactory) {
            $this->configCacheFactory = new ConfigCacheFactory(true);
        }

        return $this->configCacheFactory;
    }
}
