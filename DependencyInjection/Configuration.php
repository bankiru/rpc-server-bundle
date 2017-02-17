<?php

namespace Bankiru\Api\Rpc\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\VariableNodeDefinition;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{

    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();
        $root    = $builder->root('rpc');
        $this->configureRouter($root->children()->arrayNode('router'));

        return $builder;
    }

    private function configureRouter(ArrayNodeDefinition $root)
    {
        $root->addDefaultsIfNotSet();
        $root->treatNullLike(['endpoints' => []]);
        /** @var ArrayNodeDefinition $proto */
        $endpoints = $root->children()->arrayNode('endpoints');

        $proto = $endpoints->prototype('array');

        $proto->append(
            (new ScalarNodeDefinition('path'))
                ->isRequired()
                ->example('/')
                ->cannotBeEmpty()
                ->info('Endpoint URI')
        );
        $proto->append(
            (new ArrayNodeDefinition('resources'))
                ->beforeNormalization()
                ->ifNull()
                ->then(
                    function () {
                        return [];
                    }
                )
                ->ifString()
                ->then(
                    function ($v) {
                        return [$v];
                    }
                )
                ->end()
                ->prototype('scalar')->end()
                ->example('rpc.yml')
                ->info('Route definitions')
        );
        $proto->append(
            (new VariableNodeDefinition('defaults'))
        );
        $proto->append(
            (new VariableNodeDefinition('context'))
                ->beforeNormalization()
                ->ifString()
                ->then(
                    function ($v) {
                        return [$v];
                    }
                )
                ->end()
                ->defaultValue(['Default'])
                ->info('Endpoint-wide context')
                ->example(['Default'])
        );

        $endpoints->useAttributeAsKey('name');
        $proto->addDefaultsIfNotSet();
    }
}
