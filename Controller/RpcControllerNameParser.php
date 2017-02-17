<?php

namespace Bankiru\Api\Rpc\Controller;

use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * ControllerNameParser converts controller from the short notation a:b:c
 * (BlogBundle:Post:index) to a fully-qualified class::method string
 * (Bundle\BlogBundle\Controller\PostController::indexAction).
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Pavel Batanov <pavel@batanov.me>
 */
class RpcControllerNameParser implements ControllerNameParser
{
    protected $kernel;

    /**
     * Constructor.
     *
     * @param KernelInterface $kernel A KernelInterface instance
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /** {@inheritdoc} */
    public function parse($controller)
    {
        $originalController = $controller;
        if (3 !== count($parts = explode(':', $controller))) {
            throw new \InvalidArgumentException(sprintf('The "%s" controller is not a valid "a:b:c" controller string.', $controller));
        }

        list($bundle, $controller, $action) = $parts;
        $controller = str_replace('/', '\\', $controller);
        $bundles = array();

        try {
            // this throws an exception if there is no such bundle
            $allBundles = $this->kernel->getBundle($bundle, false);
        } catch (\InvalidArgumentException $e) {
            $message = sprintf(
                'The "%s" (from the _controller value "%s") does not exist or is not enabled in your kernel!',
                $bundle,
                $originalController
            );

            if ($alternative = $this->findAlternative($bundle)) {
                $message .= sprintf(' Did you mean "%s:%s:%s"?', $alternative, $controller, $action);
            }

            throw new \InvalidArgumentException($message, 0, $e);
        }

        foreach ($allBundles as $b) {
            $try = $this->guessControllerClassName($b, $controller);
            if (class_exists($try)) {
                return $this->guessActionString($try, $action);
            }

            $bundles[] = $b->getName();
            $msg = sprintf('The _controller value "%s:%s:%s" maps to a "%s" class, but this class was not found. Create this class or check the spelling of the class and its namespace.', $bundle, $controller, $action, $try);
        }

        if (count($bundles) > 1) {
            $msg = sprintf('Unable to find controller "%s:%s" in bundles %s.', $bundle, $controller, implode(', ', $bundles));
        }

        throw new \InvalidArgumentException($msg);
    }

    /** {@inheritdoc} */
    public function build($controller)
    {
        if (0 === preg_match('#^(.*?\\\\Controller\\\\(.+)Controller)::(.+)Action$#', $controller, $match)) {
            throw new \InvalidArgumentException(sprintf('The "%s" controller is not a valid "class::method" string.', $controller));
        }

        $className = $match[1];
        $controllerName = $match[2];
        $actionName = $match[3];
        foreach ($this->kernel->getBundles() as $name => $bundle) {
            if (0 !== strpos($className, $bundle->getNamespace())) {
                continue;
            }

            return sprintf('%s:%s:%s', $name, $controllerName, $actionName);
        }

        throw new \InvalidArgumentException(sprintf('Unable to find a bundle that defines controller "%s".', $controller));
    }

    /**
     * Attempts to find a bundle that is *similar* to the given bundle name.
     *
     * @param string $nonExistentBundleName
     *
     * @return string
     */
    private function findAlternative($nonExistentBundleName)
    {
        $bundleNames = array_map(function (BundleInterface $b) {
            return $b->getName();
        }, $this->kernel->getBundles());

        $alternative = null;
        $shortest = null;
        foreach ($bundleNames as $bundleName) {
            // if there's a partial match, return it immediately
            if (false !== strpos($bundleName, $nonExistentBundleName)) {
                return $bundleName;
            }

            $lev = levenshtein($nonExistentBundleName, $bundleName);
            if ($lev <= strlen($nonExistentBundleName) / 3 && ($alternative === null || $lev < $shortest)) {
                $alternative = $bundleName;
            }
        }

        return $alternative;
    }

    /**
     * @param BundleInterface $bundle
     * @param string $controller
     * @return string Guessed controller FQCN
     */
    protected function guessControllerClassName(BundleInterface $bundle, $controller)
    {
        return $bundle->getNamespace().'\\Controller\\'.$controller.'Controller';
    }

    /**
     * @param string $controller
     * @param string $action
     * @return string guessed FQCN::function string
     */
    protected function guessActionString($controller, $action)
    {
        return $controller.'::'.$action.'Action';
    }
}
