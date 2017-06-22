<?php

namespace Bankiru\Api\Rpc\Routing\Loader;

use Bankiru\Api\Rpc\Routing\Annotation\Method;
use Bankiru\Api\Rpc\Routing\LoaderInterface;
use Bankiru\Api\Rpc\Routing\MethodCollection;
use Bankiru\Api\Rpc\Routing\Route;
use Doctrine\Common\Annotations\Reader;
use Symfony\Component\Config\Resource\FileResource;

class AnnotationClassLoader implements LoaderInterface
{
    /** @var  Reader */
    private $reader;

    /**
     * Constructor.
     *
     * @param Reader $reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /** {@inheritdoc} */
    public function load($class, $type = null)
    {
        if (!class_exists($class)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
        }

        $class = new \ReflectionClass($class);
        if ($class->isAbstract()) {
            throw new \InvalidArgumentException(
                sprintf('Annotations from class "%s" cannot be read as it is abstract.', $class->getName())
            );
        }

        $parents = $this->getParentAnnotations($class);

        $collection = new MethodCollection();
        $collection->addResource(new FileResource($class->getFileName()));

        foreach ($class->getMethods() as $method) {
            if (!$method->isPublic()) {
                continue;
            }

            foreach ($this->reader->getMethodAnnotations($method) as $annot) {
                if ($annot instanceof Method) {
                    $this->addRoute($collection, $annot, $parents, $class, $method);
                }
            }
        }

        $collection->addPrefix($parents['method']);

        return $collection;
    }

    /** {@inheritdoc} */
    public function supports($resource, $type = null)
    {
        return is_string($resource) &&
               preg_match('/^(?:\\\\?[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)+$/', $resource) &&
               (!$type || 'annotation' === $type);
    }

    /** {@inheritdoc} */
    public function getResolver()
    {
    }

    /** {@inheritdoc} */
    public function setResolver($resolver)
    {
    }

    protected function getParentAnnotations(\ReflectionClass $class)
    {
        $parents = [
            'method'          => '',
            'context'         => [],
            'default_context' => true,
            'options'         => [],
        ];

        /** @var Method $annot */
        if ($annot = $this->reader->getClassAnnotation($class, Method::class)) {
            if (null !== $annot->getMethod()) {
                $parents['method'] = $annot->getMethod();
            }

            if (null !== $annot->getContext()) {
                $parents['context'] = $annot->getContext();
            }

            if (null !== $annot->getOptions()) {
                $parents['options'] = $annot->getOptions();
            }
        }

        return $parents;
    }

    protected function addRoute(
        MethodCollection $collection,
        Method $annot,
        array $parents,
        \ReflectionClass $class,
        \ReflectionMethod $method
    ) {
        $collection->add(
            $annot->getMethod(),
            new Route(
                $annot->getMethod(),
                $class->getName() . '::' . $method->getName(),
                array_merge($parents['context'], $annot->getContext()),
                $parents['default_context'] && $annot->isDefaultContext(),
                $annot->isInherit(),
                array_merge_recursive($parents['options'], $annot->getOptions())
            )
        );
    }
}
