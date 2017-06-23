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

namespace Bankiru\Api\Rpc\Routing\ControllerResolver;

use Bankiru\Api\Rpc\Controller\ControllerNameParser;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class ControllerResolver extends BaseResolver
{
    /** @var ContainerInterface */
    protected $container;
    /** @var ControllerNameParser */
    protected $parser;

    /**
     * Constructor.
     *
     * @param ContainerInterface   $container
     * @param ControllerNameParser $parser
     * @param LoggerInterface      $logger
     */
    public function __construct(
        ContainerInterface $container,
        ControllerNameParser $parser,
        LoggerInterface $logger = null
    )
    {
        $this->container = $container;
        $this->parser    = $parser;

        parent::__construct($logger);
    }

    /**
     * Returns a callable for the given controller.
     *
     * @param string $controller A Controller string
     *
     * @return mixed A PHP callable
     *
     * @throws \LogicException           When the name could not be parsed
     * @throws \InvalidArgumentException When the controller class does not exist
     */
    protected function createController($controller)
    {
        if (false === strpos($controller, '::')) {
            $count = substr_count($controller, ':');
            if (2 === $count) {
                // controller in the a:b:c notation then
                $controller = $this->parser->parse($controller);
            } elseif (1 === $count) {
                // controller in the service:method notation
                list($service, $method) = explode(':', $controller, 2);

                return [$this->container->get($service), $method];
            } elseif ($this->container->has($controller) && method_exists(
                    $service = $this->container->get($controller),
                    '__invoke'
                )
            ) {
                return $service;
            } else {
                throw new \LogicException(sprintf('Unable to parse the controller name "%s".', $controller));
            }
        }

        return parent::createController($controller);
    }

    /**
     * {@inheritdoc}
     */
    protected function instantiateController($class)
    {
        $controller = parent::instantiateController($class);

        if ($controller instanceof ContainerAwareInterface) {
            $controller->setContainer($this->container);
        }

        return $controller;
    }
}
