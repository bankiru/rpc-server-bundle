<?php

/*
 * Copyright (c) 2010-2017 Fabien Potencier
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to t * *he following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 */

namespace Bankiru\Api\Rpc\Routing\Loader;

use Bankiru\Api\Rpc\Routing\Exception\CircularReferenceLoaderException;
use Bankiru\Api\Rpc\Routing\Exception\FileLoaderException;
use Bankiru\Api\Rpc\Routing\Exception\FileLoaderLoadException;
use Symfony\Component\Config\FileLocatorInterface;

abstract class FileLoader extends Loader
{
    /** @var array to track circular references */
    protected static $loading = [];

    /** @var  FileLocatorInterface */
    private $locator;
    /** @var  string */
    private $currentDir;

    public function __construct(FileLocatorInterface $locator)
    {
        $this->locator = $locator;
    }

    /**
     * @param string $currentDir
     */
    public function setCurrentDir($currentDir)
    {
        $this->currentDir = $currentDir;
    }

    /**
     * Imports a resource.
     *
     * @param mixed       $resource       A Resource
     * @param string|null $type           The resource type or null if unknown
     * @param bool        $ignoreErrors   Whether to ignore import errors or not
     * @param string|null $sourceResource The original resource importing the new resource
     *
     * @return mixed
     * @throws FileLoaderException
     * @throws CircularReferenceLoaderException
     */
    public function import($resource, $type = null, $ignoreErrors = false, $sourceResource = null)
    {
        try {
            $loader = $this->resolve($resource, $type);

            if ($loader instanceof self && null !== $this->currentDir) {
                $resource = $loader->getLocator()->locate($resource, $this->currentDir, false);
            }

            $resources = is_array($resource) ? $resource : [$resource];
            for ($i = 0; $i < $resourcesCount = count($resources); ++$i) {
                if (isset(self::$loading[$resources[$i]])) {
                    if ($i == $resourcesCount - 1) {
                        throw CircularReferenceLoaderException::fromPaths(array_keys(self::$loading));
                    }
                } else {
                    $resource = $resources[$i];
                    break;
                }
            }
            self::$loading[$resource] = true;

            try {
                $ret = $loader->load($resource, $type);
            } finally {
                unset(self::$loading[$resource]);
            }

            return $ret;
        } catch (CircularReferenceLoaderException $e) {
            throw $e;
        } catch (\Exception $e) {
            if (!$ignoreErrors) {
                // prevent embedded imports from nesting multiple exceptions
                if ($e instanceof FileLoaderException) {
                    throw $e;
                }

                throw new FileLoaderLoadException($resource, $sourceResource, null, $e);
            }
        }

        return null;
    }

    /**
     * @return FileLocatorInterface
     */
    public function getLocator()
    {
        return $this->locator;
    }
}
