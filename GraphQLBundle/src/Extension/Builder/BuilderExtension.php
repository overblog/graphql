<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Extension\Builder;

use Exception;
use Overblog\GraphQLBundle\Configuration\Configuration;
use Overblog\GraphQLBundle\Configuration\TypeConfiguration;
use Overblog\GraphQLBundle\Extension\Extension;
use Overblog\GraphQLBundle\Util\ConfigProcessor;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Traversable;

/**
 * Extension to handle configuration builders
 */
class BuilderExtension extends Extension
{
    const ALIAS = 'builder';

    protected array $builders = [];

    public function __construct(iterable $builders = [])
    {
        $this->builders = $builders instanceof Traversable ? iterator_to_array($builders) : $builders;
    }

    public function getConfiguration(TypeConfiguration $type = null): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('extension');
        $treeBuilder
            ->getRootNode()
            ->children()
                ->scalarNode('name')->isRequired()->end()
                ->variableNode('configuration')->end()
            ->end();

        return $treeBuilder;
    }

    public function getBuilder(string $name): ?BuilderInterface
    {
        return $this->builders[$name] ?? null;
    }

    public function handleConfiguration(Configuration $configuration, TypeConfiguration $typeConfiguration, $extensionConfiguration): void
    {
        $builderName = $extensionConfiguration['name'];
        $builderConfiguration = $extensionConfiguration['configuration'] ?? null;

        $builder = $this->getBuilder($builderName);
        if (null === $builder) {
            throw new Exception(sprintf('Builder "%s" not found. Available builders: %s', $builderName, join(', ', array_keys($this->builders))));
        }

        if (!$builder->supports($typeConfiguration)) {
            throw new Exception(sprintf("The builder \"%s\" doesn't support GraphQL type \"%s\"", $builderName, $typeConfiguration->getGraphQLType()));
        }

        $builderConfiguration = (new ConfigProcessor())->process($builder->getConfiguration()->buildTree(), $builderConfiguration);

        $builder->updateConfiguration($typeConfiguration, $builderConfiguration);
    }
}
