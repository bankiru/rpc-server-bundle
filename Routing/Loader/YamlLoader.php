<?php

namespace Bankiru\Api\Rpc\Routing\Loader;

use Bankiru\Api\Rpc\Routing\MethodCollection;
use Bankiru\Api\Rpc\Routing\Route;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser;

class YamlLoader extends FileLoader
{
    private static $availableKeys = [
        'resource',
        'type',
        'prefix',
        'method',
        'controller',
        'context',
        'default_context',
        'inherit',
    ];

    /** @var  Parser */
    private $parser;

    /**
     * Loads a resource.
     *
     * @param mixed       $resource The resource
     * @param string|null $type     The resource type or null if unknown
     *
     * @return MethodCollection
     * @throws \Exception If something went wrong
     */
    public function load($resource, $type = null)
    {
        $path = $this->getLocator()->locate($resource);

        if (!stream_is_local($path)) {
            throw new \InvalidArgumentException(sprintf('This is not a local file "%s".', $path));
        }

        if (!file_exists($path)) {
            throw new \InvalidArgumentException(sprintf('File "%s" not found.', $path));
        }

        if (null === $this->parser) {
            $this->parser = new Parser();
        }

        try {
            $parsedConfig = $this->parser->parse(file_get_contents($path));
        } catch (ParseException $e) {
            throw new \InvalidArgumentException(sprintf('The file "%s" does not contain valid YAML.', $path), 0, $e);
        }

        $collection = new MethodCollection();
        $collection->addResource(new FileResource($path));

        // empty file
        if (null === $parsedConfig) {
            return $collection;
        }

        // not an array
        if (!is_array($parsedConfig)) {
            throw new \InvalidArgumentException(sprintf('The file "%s" must contain a YAML array.', $path));
        }

        foreach ($parsedConfig as $name => $config) {
            $this->validate($config, $name, $path);

            if (isset($config['resource'])) {
                $this->parseImport($collection, $config, $path, $resource);
            } else {
                $this->parseRoute($collection, $name, $config, $path);
            }
        }

        return $collection;
    }

    /**
     * Returns whether this class supports the given resource.
     *
     * @param mixed       $resource A resource
     * @param string|null $type     The resource type or null if unknown
     *
     * @return bool True if this class supports the given resource, false otherwise
     */
    public function supports($resource, $type = null)
    {
        return is_string($resource) &&
               in_array(pathinfo($resource, PATHINFO_EXTENSION), ['yml', 'yaml'], true) &&
               (!$type || 'yaml' === $type);
    }

    /**
     * Validates the route configuration.
     *
     * @param array  $config A resource config
     * @param string $name   The config key
     * @param string $path   The loaded file path
     *
     * @return array
     * @throws \InvalidArgumentException If one of the provided config keys is not supported,
     *                                   something is missing or the combination is nonsense
     */
    protected function validate($config, $name, $path)
    {
        if (!is_array($config)) {
            throw new \InvalidArgumentException(
                sprintf('The definition of "%s" in "%s" must be a YAML array.', $name, $path)
            );
        }
        if ($extraKeys = array_diff(array_keys($config), self::$availableKeys)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The routing file "%s" contains unsupported keys for "%s": "%s". Expected one of: "%s".',
                    $path,
                    $name,
                    implode('", "', $extraKeys),
                    implode('", "', self::$availableKeys)
                )
            );
        }
        if (isset($config['resource']) && isset($config['method'])) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The routing file "%s" must not specify both the "resource" key and the "path" key for "%s". ' .
                    'Choose between an import and a route definition.',
                    $path,
                    $name
                )
            );
        }
        if (!isset($config['resource']) && isset($config['type'])) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The "type" key for the route definition "%s" in "%s" is unsupported. ' .
                    'It is only available for imports in combination with the "resource" key.',
                    $name,
                    $path
                )
            );
        }
    }

    /**
     * Parses an import and adds the routes in the resource to the RouteCollection.
     *
     * @param MethodCollection $collection A RouteCollection instance
     * @param array            $config     Route definition
     * @param string           $path       Full path of the YAML file being processed
     * @param string           $file       Loaded file name
     */
    protected function parseImport(MethodCollection $collection, array $config, $path, $file)
    {
        $type   = isset($config['type']) ? $config['type'] : null;
        $prefix = isset($config['prefix']) ? $config['prefix'] : '';
        $this->setCurrentDir(dirname($path));

        $subCollection = $this->import($config['resource'], $type, false, $file);
        /* @var $subCollection MethodCollection */
        $subCollection->addPrefix($prefix);

        if (array_key_exists('context', $config)) {
            foreach ((array)$config['context'] as $context) {
                $subCollection->addContext($context);
            }
        }

        $collection->addCollection($subCollection);
    }

    /**
     * Parses a route and adds it to the RouteCollection.
     *
     * @param MethodCollection $collection A RouteCollection instance
     * @param string           $name       Route name
     * @param array            $config     Route definition
     * @param string           $path       Full path of the YAML file being processed
     */
    protected function parseRoute(MethodCollection $collection, $name, array $config, $path)
    {
        $context = array_key_exists('context', $config) ? (array)$config['context'] : [];
        $method  = array_key_exists('method', $config) ? $config['method'] : $name;
        $inherit = array_key_exists('inherit', $config) ? $config['inherit'] : true;
        $route   = new Route(
            $method,
            $config['controller'],
            $context,
            array_key_exists('default_context', $config) ? $config['default_context'] : true,
            $inherit
        );

        $collection->add($name, $route);
    }
}
