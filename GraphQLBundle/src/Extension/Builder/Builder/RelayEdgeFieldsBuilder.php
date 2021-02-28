<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Extension\Builder\Builder;

use InvalidArgumentException;
use Overblog\GraphQLBundle\Configuration\FieldConfiguration;
use Overblog\GraphQLBundle\Configuration\InterfaceConfiguration;
use Overblog\GraphQLBundle\Configuration\ObjectConfiguration;
use Overblog\GraphQLBundle\Configuration\TypeConfiguration;
use Overblog\GraphQLBundle\Extension\Builder\BuilderInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class RelayEdgeFieldsBuilder implements BuilderInterface
{
    public function supports(TypeConfiguration $typeConfiguration): bool
    {
        return in_array($typeConfiguration->getGraphQLType(), [TypeConfiguration::TYPE_OBJECT, TypeConfiguration::TYPE_INTERFACE]);
    }

    public function getConfiguration(TypeConfiguration $typeConfiguration = null): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('configuration');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('nodeType')->isRequired()->end()
                ->scalarNode('nodeDescription')->default('Node of the Edge')->end()
            ->end();

        return $treeBuilder;
    }

    /**
     * @param ObjectConfiguration|InterfaceConfiguration $typeConfiguration
     * @param mixed                                      $builderConfiguration
     *
     * @throws InvalidArgumentException
     */
    public function updateConfiguration(TypeConfiguration $typeConfiguration, $builderConfiguration): void
    {
        $node = FieldConfiguration::get('node', $builderConfiguration['nodeType'])
                ->setDescription($builderConfiguration['nodeDescription']);

        $cursor = FieldConfiguration::get('cursor', 'String!')
                ->setDescription('The edge cursor');

        $typeConfiguration->addFields([$node, $cursor]);
    }
}
