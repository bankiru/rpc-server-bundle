<?php
/**
 * Created by PhpStorm.
 * User: batanov.pavel
 * Date: 16.02.2016
 * Time: 14:53
 */

namespace Bankiru\Api\Rpc\Routing\Loader;

use Bankiru\Api\Rpc\Routing\MethodCollection;
use Symfony\Component\Config\Resource\DirectoryResource;

class DirectoryLoader extends FileLoader
{
    /**
     * {@inheritdoc}
     */
    public function load($file, $type = null)
    {
        $path = $this->getLocator()->locate($file);

        $collection = new MethodCollection();
        $collection->addResource(new DirectoryResource($path));

        foreach (scandir($path) as $dir) {
            if ('.' !== $dir[0]) {
                $this->setCurrentDir($path);
                $subPath = $path.'/'.$dir;
                $subType = null;

                if (is_dir($subPath)) {
                    $subPath .= '/';
                    $subType = 'directory';
                }

                $subCollection = $this->import($subPath, $subType, false, $path);
                $collection->addCollection($subCollection);
            }
        }

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        // only when type is forced to directory, not to conflict with AnnotationClassLoader

        return 'directory' === $type;
    }
}
