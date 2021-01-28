<?php

declare(strict_types=1);

namespace Overblog\GraphQL\Bundle\ProfilerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public const NAME = 'overblog_graphql_profiler';

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder(self::NAME);

        // @phpstan-ignore-next-line
        $treeBuilder->getRootNode()
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('query_match')->defaultNull()->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
