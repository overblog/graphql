<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Extension\Builder;

use InvalidArgumentException;
use Overblog\GraphQLBundle\Configuration\ArgumentConfiguration;
use Overblog\GraphQLBundle\Configuration\FieldConfiguration;
use Overblog\GraphQLBundle\Configuration\InterfaceConfiguration;
use Overblog\GraphQLBundle\Configuration\ObjectConfiguration;
use Overblog\GraphQLBundle\Configuration\TypeConfiguration;
use Overblog\GraphQLBundle\Definition\Builder\MappingInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class LegacyBuilder implements BuilderInterface
{
    const TYPE_FIELDS = 'fields';
    const TYPE_FIELD = 'field';
    const TYPE_ARGS = 'args';

    const TYPES = [
        self::TYPE_FIELDS,
        self::TYPE_FIELD,
        self::TYPE_ARGS,
    ];

    protected string $type;
    protected MappingInterface $legacyBuilder;

    public function __construct(string $type, MappingInterface $legacyBuilder)
    {
        $this->type = $type;
        $this->legacyBuilder = $legacyBuilder;
    }

    public function supports(TypeConfiguration $typeConfiguration): bool
    {
        switch ($this->type) {
            case self::TYPE_FIELDS:
                return in_array($typeConfiguration->getGraphQLType(), [TypeConfiguration::TYPE_OBJECT, TypeConfiguration::TYPE_INTERFACE]);
            case self::TYPE_FIELD:
            case self::TYPE_ARGS:
                return TypeConfiguration::TYPE_FIELD === $typeConfiguration->getGraphQLType();
        }
    }

    public function getConfiguration(TypeConfiguration $typeConfiguration = null): TreeBuilder
    {
        return new TreeBuilder('configuration', 'variable');
    }

    /**
     * @param ObjectConfiguration|InterfaceConfiguration $typeConfiguration
     * @param mixed                                      $builderConfiguration
     *
     * @throws InvalidArgumentException
     */
    public function updateConfiguration(TypeConfiguration $typeConfiguration, $builderConfiguration): void
    {
        trigger_deprecation('overblog/graphql', '0.14', 'Builders with MappingInterface have been deprecated. Use the Builder Extension with a proper builder instead.');
        $mapping = $this->legacyBuilder->toMappingDefinition($builderConfiguration ?? []);
        switch ($this->type) {
            // Handle fields builder
            case self::TYPE_FIELDS:
                /** @var ObjectConfiguration|InterfaceConfiguration $typeConfiguration */
                foreach ($mapping as $name => $field) {
                    $typeConfiguration->addField($this->mappingToField($name, $field));
                }
                break;
            // Handle field builder
            case self::TYPE_FIELD:
                /** @var FieldConfiguration $typeConfiguration */
                $this->mappingToField($typeConfiguration->getName(), $mapping, $typeConfiguration);
                break;
            // Handle arguments builder
            case self::TYPE_ARGS:
                /** @var FieldConfiguration $typeConfiguration */
                foreach ($mapping as $argName => $argMapping) {
                    $typeConfiguration->addArgument($this->mappingToArgument($argName, $argMapping));
                }
                break;
        }
    }

    protected function mappingToField(string $name, $mapping, FieldConfiguration $field = null): FieldConfiguration
    {
        if (is_string($mapping)) {
            $mapping = ['type' => $mapping];
        }
        $fieldConfiguration = $field ?: FieldConfiguration::get($name, $mapping['type']);

        if (isset($mapping['type'])) {
            $fieldConfiguration->setType($mapping['type']);
        }
        if (isset($mapping['resolver']) || isset($mapping['resolve'])) {
            $fieldConfiguration->setResolver($mapping['resolver'] ?? $mapping['resolve']);
        }
        if (isset($mapping['description'])) {
            $fieldConfiguration->setDescription($mapping['description']);
        }
        if (isset($mapping['deprecation']) || isset($mapping['deprecated'])) {
            $fieldConfiguration->setDeprecation($mapping['deprecation'] ?? $mapping['deprecated']);
        }
        if (isset($mapping['args'])) {
            foreach ($mapping['args'] as $argName => $argMapping) {
                $fieldConfiguration->addArgument($this->mappingToArgument($argName, $argMapping));
            }
        }

        return $fieldConfiguration;
    }

    protected function mappingToArgument(string $name, $mapping): ArgumentConfiguration
    {
        if (is_string($mapping)) {
            $mapping = ['type' => $mapping];
        }
        $argConfiguration = ArgumentConfiguration::get($name, $mapping['type']);
        if (isset($mapping['defaultValue'])) {
            $argConfiguration->setDefaultValue($mapping['defaultValue']);
        }
        if (isset($mapping['description'])) {
            $argConfiguration->setDescription($mapping['description']);
        }
        if (isset($mapping['deprecation']) || isset($mapping['deprecatedReason'])) {
            $argConfiguration->setDeprecation($mapping['deprecation'] ?? $mapping['deprecatedReason']);
        }

        return $argConfiguration;
    }
}
