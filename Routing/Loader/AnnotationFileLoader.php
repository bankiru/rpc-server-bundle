<?php

namespace Bankiru\Api\Rpc\Routing\Loader;

use Bankiru\Api\Rpc\Routing\MethodCollection;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Config\Resource\FileResource;

class AnnotationFileLoader extends FileLoader
{
    /** @var AnnotationClassLoader */
    private $loader;

    /**
     * Constructor.
     *
     * @param FileLocatorInterface  $locator A FileLocator instance
     * @param AnnotationClassLoader $loader  An AnnotationClassLoader instance
     *
     * @throws \RuntimeException
     */
    public function __construct(FileLocatorInterface $locator, AnnotationClassLoader $loader)
    {
        if (!function_exists('token_get_all')) {
            throw new \RuntimeException('The Tokenizer extension is required for the routing annotation loaders.');
        }

        parent::__construct($locator);

        $this->loader = $loader;
    }

    /**
     * @return AnnotationClassLoader
     */
    public function getLoader()
    {
        return $this->loader;
    }

    /**
     * Loads from annotations from a file.
     *
     * @param string      $file A PHP file path
     * @param string|null $type The resource type
     *
     * @return MethodCollection A RouteCollection instance
     *
     * @throws \InvalidArgumentException When the file does not exist or its routes cannot be parsed
     */
    public function load($file, $type = null)
    {
        $path = $this->getLocator()->locate($file);

        $collection = new MethodCollection();
        if ($class = $this->findClass($path)) {
            $collection->addResource(new FileResource($path));
            $collection->addCollection($this->loader->load($class, $type));
        }
        if (PHP_VERSION_ID >= 70000) {
            // PHP 7 memory manager will not release after token_get_all(), see https://bugs.php.net/70098
            gc_mem_caches();
        }

        return $collection;
    }

    /**
     * Returns the full class name for the first class in the file.
     *
     * @param string $file A PHP file path
     *
     * @return string|false Full class name if found, false otherwise
     */
    protected function findClass($file)
    {
        $class     = false;
        $namespace = false;
        $tokens    = token_get_all(file_get_contents($file));
        for ($i = 0; isset($tokens[$i]); ++$i) {
            $token = $tokens[$i];

            if (!isset($token[1])) {
                continue;
            }

            if (true === $class && T_STRING === $token[0]) {
                return $namespace . '\\' . $token[1];
            }

            if (true === $namespace && T_STRING === $token[0]) {
                $namespace = $token[1];
                while (isset($tokens[++$i][1]) && in_array($tokens[$i][0], [T_NS_SEPARATOR, T_STRING])) {
                    $namespace .= $tokens[$i][1];
                }
                $token = $tokens[$i];
            }

            if (T_CLASS === $token[0]) {
                $class = true;
            }

            if (T_NAMESPACE === $token[0]) {
                $namespace = true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return is_string($resource) &&
               'php' === pathinfo($resource, PATHINFO_EXTENSION) &&
               (!$type || 'annotation' === $type);
    }
}
