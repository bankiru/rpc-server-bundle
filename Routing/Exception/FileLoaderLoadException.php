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

namespace Bankiru\Api\Rpc\Routing\Exception;

class FileLoaderLoadException extends \RuntimeException implements FileLoaderException
{
    /**
     * @param string     $resource       The resource that could not be imported
     * @param string     $sourceResource The original resource importing the new resource
     * @param int        $code           The error code
     * @param \Exception $previous       A previous exception
     */
    public function __construct($resource, $sourceResource = null, $code = null, $previous = null)
    {
        $message = '';
        if ($previous) {
            // Include the previous exception, to help the user see what might be the underlying cause

            // Trim the trailing period of the previous message. We only want 1 period remove so no rtrim...
            if ('.' === substr($previous->getMessage(), -1)) {
                $trimmedMessage = substr($previous->getMessage(), 0, -1);
                $message .= sprintf('%s', $trimmedMessage) . ' in ';
            } else {
                $message .= sprintf('%s', $previous->getMessage()) . ' in ';
            }
            $message .= $resource . ' ';

            // show tweaked trace to complete the human readable sentence
            if (null === $sourceResource) {
                $message .= sprintf('(which is loaded in resource "%s")', $this->varToString($resource));
            } else {
                $message .= sprintf('(which is being imported from "%s")', $this->varToString($sourceResource));
            }
            $message .= '.';

            // if there's no previous message, present it the default way
        } elseif (null === $sourceResource) {
            $message .= sprintf('Cannot load resource "%s".', $this->varToString($resource));
        } else {
            $message .= sprintf('Cannot import resource "%s" from "%s".',
                                $this->varToString($resource),
                                $this->varToString($sourceResource));
        }

        // Is the resource located inside a bundle?
        if ('@' === $resource[0]) {
            $parts  = explode(DIRECTORY_SEPARATOR, $resource);
            $bundle = substr($parts[0], 1);
            $message .= sprintf(' Make sure the "%s" bundle is correctly registered and loaded in the application kernel class.',
                                $bundle);
            $message .= sprintf(' If the bundle is registered, make sure the bundle path "%s" is not empty.',
                                $resource);
        }

        parent::__construct($message, $code, $previous);
    }

    protected function varToString($var)
    {
        if (is_object($var)) {
            return sprintf('Object(%s)', get_class($var));
        }

        if (is_array($var)) {
            $a = [];
            foreach ($var as $k => $v) {
                $a[] = sprintf('%s => %s', $k, $this->varToString($v));
            }

            return sprintf('Array(%s)', implode(', ', $a));
        }

        if (is_resource($var)) {
            return sprintf('Resource(%s)', get_resource_type($var));
        }

        if (null === $var) {
            return 'null';
        }

        if (false === $var) {
            return 'false';
        }

        if (true === $var) {
            return 'true';
        }

        return (string)$var;
    }
}
