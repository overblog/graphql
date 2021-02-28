<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Extension;

use Overblog\GraphQLBundle\Configuration\Configuration;
use Overblog\GraphQLBundle\Configuration\TypeConfiguration;
use Overblog\GraphQLBundle\Util\ConfigProcessor;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * Base GraphQL Extension class
 */
abstract class Extension
{
    /**
     * Handle Configuration for a given type and configuration
     */
    public function handleConfiguration(Configuration $configuration, TypeConfiguration $typeConfiguration, $extensionConfiguration): void
    {
        return;
    }

    /**
     * Return an unique alias to identify the extension
     */
    final public function getAlias(): string
    {
        if (!defined(static::class.'::ALIAS')) {
            throw new ExtensionException(sprintf('Extension "%s" must define a constant ALIAS with a unique alias', static::class));
        }

        return static::ALIAS;
    }

    /**
     * Definie on what TypeConfiguration the extension can be used
     * Ex: [TypeConfiguration::TYPE_OBJECT, TypeConfiguration::TYPE_INTERFACE]
     *
     * @return string[]
     */
    public function supports(): array
    {
        return TypeConfiguration::TYPES;
    }

    /**
     * Provide a TreeBuilder to process configuration based on type.
     */
    public function getConfiguration(TypeConfiguration $type = null): TreeBuilder
    {
        return new TreeBuilder('extension', 'scalar');
    }

    /**
     * Process the configuration for given type and return the processed configuration
     *
     * @param mixed $configuration
     *
     * @return mixed
     */
    public function processConfiguration(TypeConfiguration $type, $configuration)
    {
        if (!in_array($type->getGraphQLType(), $this->supports())) {
            throw new ExtensionException('Extension %s does not support type "%s"', $type->getGraphQLType());
        }

        return (new ConfigProcessor())->process($this->getConfiguration($type)->buildTree(), $configuration);
    }
}
