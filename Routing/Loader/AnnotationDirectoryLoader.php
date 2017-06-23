<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bankiru\Api\Rpc\Routing\Loader;

use Bankiru\Api\Rpc\Routing\MethodCollection;
use Symfony\Component\Config\Resource\DirectoryResource;

class AnnotationDirectoryLoader extends AnnotationFileLoader
{
    /**
     * Loads from annotations from a directory.
     *
     * @param string      $path A directory path
     * @param string|null $type The resource type
     *
     * @return MethodCollection A RouteCollection instance
     *
     * @throws \InvalidArgumentException When the directory does not exist or its routes cannot be parsed
     */
    public function load($path, $type = null)
    {
        $dir = $this->getLocator()->locate($path);

        $collection = new MethodCollection();
        $collection->addResource(new DirectoryResource($dir, '/\.php$/'));
        /** @var \SplFileInfo[] $files */
        $files = iterator_to_array(
            new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir),
                \RecursiveIteratorIterator::LEAVES_ONLY
            )
        );
        usort($files,
            function (\SplFileInfo $a, \SplFileInfo $b) {
                return (string)$a > (string)$b ? 1 : -1;
            });

        foreach ($files as $file) {
            if (!$file->isFile() || '.php' !== substr($file->getFilename(), -4)) {
                continue;
            }

            if ($class = $this->findClass($file)) {
                $refl = new \ReflectionClass($class);
                if ($refl->isAbstract()) {
                    continue;
                }

                $collection->addCollection($this->getLoader()->load($class, $type));
            }
        }

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        if (!is_string($resource)) {
            return false;
        }

        try {
            $path = $this->getLocator()->locate($resource);
        } catch (\Exception $e) {
            return false;
        }

        return is_dir($path) && (!$type || 'annotation' === $type);
    }
}
