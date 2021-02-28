<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Tests\Extension\Builder;

use Overblog\GraphQLBundle\Configuration\ArgumentConfiguration;
use Overblog\GraphQLBundle\Configuration\Configuration;
use Overblog\GraphQLBundle\Configuration\ExtensionConfiguration;
use Overblog\GraphQLBundle\Configuration\FieldConfiguration;
use Overblog\GraphQLBundle\Configuration\ObjectConfiguration;
use Overblog\GraphQLBundle\Definition\Builder\MappingInterface;
use Overblog\GraphQLBundle\Extension\Builder\BuilderExtension;
use Overblog\GraphQLBundle\Extension\Builder\LegacyBuilder;
use PHPUnit\Framework\TestCase;

class LegacyFieldsBuilder implements MappingInterface
{
    public function toMappingDefinition(array $config): array
    {
        return [
            'field1' => ['type' => 'String!'],
            'field2' => ['type' => 'Int'],
            'field3' => 'String',
        ];
    }
}

class LegacyFieldBuilder implements MappingInterface
{
    public function toMappingDefinition(array $config): array
    {
        return [
            'type' => 'MyFieldType',
            'description' => 'My field description',
            'args' => [
                'arg1' => ['type' => 'Int', 'defaultValue' => 3],
                'arg2' => 'String',
            ],
        ];
    }
}

class LegacyArgsBuilder implements MappingInterface
{
    public function toMappingDefinition(array $config): array
    {
        return [
            'arg1' => [
                'type' => 'Int',
                'defaultValue' => 3,
                'description' => 'Arg description',
                'deprecatedReason' => 'deprecated',
            ],
            'arg2' => 'String',
        ];
    }
}

class BuilderExtensionLegacyTest extends TestCase
{
    protected function getExtension(): BuilderExtension
    {
        $builders = [
            'LegacyFields' => new LegacyBuilder(LegacyBuilder::TYPE_FIELDS, new LegacyFieldsBuilder()),
            'LegacyField' => new LegacyBuilder(LegacyBuilder::TYPE_FIELD, new LegacyFieldBuilder()),
            'LegacyArgs' => new LegacyBuilder(LegacyBuilder::TYPE_ARGS, new LegacyArgsBuilder()),
        ];

        return new BuilderExtension($builders);
    }

    public function testLegacyFieldsBuilder(): void
    {
        $extension = $this->getExtension();

        $extensionConfiguration = ExtensionConfiguration::get(BuilderExtension::ALIAS, ['name' => 'LegacyFields', 'configuration' => []]);
        $object = ObjectConfiguration::get('MyType')
                ->addField(FieldConfiguration::get('field0', 'String!'))
                ->addExtension($extensionConfiguration);
        $configuration = new Configuration();
        $configuration->addType($object);

        $extension->handleConfiguration($configuration, $object, ['name' => 'LegacyFields', 'configuration' => []]);

        $type = $configuration->getType('MyType');
        $this->assertInstanceOf(ObjectConfiguration::class, $type);
        /** @var ObjectConfiguration $type */
        $this->assertCount(4, $type->getFields());
        $this->assertEquals('String!', $type->getField('field0')->getType());
        $this->assertEquals('String!', $type->getField('field1')->getType());
        $this->assertEquals('Int', $type->getField('field2')->getType());
        $this->assertEquals('String', $type->getField('field3')->getType());
    }

    public function testLegacyFieldBuilder(): void
    {
        $extension = $this->getExtension();

        $extensionConfiguration = ExtensionConfiguration::get(BuilderExtension::ALIAS, ['name' => 'LegacyField']);
        $field = FieldConfiguration::get('field0', 'String!')
                    ->addExtension($extensionConfiguration);

        $object = ObjectConfiguration::get('MyType')
                    ->addField($field);

        $configuration = new Configuration();
        $configuration->addType($object);

        $extension->handleConfiguration($configuration, $field, ['name' => 'LegacyField']);

        $type = $configuration->getType('MyType');
        $this->assertInstanceOf(ObjectConfiguration::class, $type);

        /** @var ObjectConfiguration $type */
        $field = $type->getField('field0');
        $this->assertInstanceOf(FieldConfiguration::class, $field);

        /** @var FieldConfiguration $field */
        $this->assertEquals('MyFieldType', $field->getType());
        $this->assertEquals('My field description', $field->getDescription());
        $this->assertCount(2, $field->getArguments());
        $this->assertEquals([
            'name' => 'arg1',
            'type' => 'Int',
            'defaultValue' => 3,
        ], $field->getArgument('arg1')->toArray());
        $this->assertEquals([
            'name' => 'arg2',
            'type' => 'String',
        ], $field->getArgument('arg2')->toArray());
    }

    public function testLegacyArgsBuilder(): void
    {
        $extension = $this->getExtension();
        $extensionConfiguration = ExtensionConfiguration::get(BuilderExtension::ALIAS, ['name' => 'LegacyArgs']);
        $field = FieldConfiguration::get('field0', 'String!')
                    ->addArgument(ArgumentConfiguration::get('arg0', 'String'))
                    ->addExtension($extensionConfiguration);

        $object = ObjectConfiguration::get('MyType')
                    ->addField($field);

        $configuration = new Configuration();
        $configuration->addType($object);

        $extension->handleConfiguration($configuration, $field, ['name' => 'LegacyArgs']);

        $type = $configuration->getType('MyType');
        $this->assertInstanceOf(ObjectConfiguration::class, $type);

        /** @var ObjectConfiguration $type */
        $field = $type->getField('field0');
        $this->assertInstanceOf(FieldConfiguration::class, $field);

        /** @var FieldConfiguration $field */
        $this->assertEquals('String!', $field->getType());
        $this->assertCount(3, $field->getArguments());
        $this->assertEquals([
            'name' => 'arg0',
            'type' => 'String',
        ], $field->getArgument('arg0')->toArray());
        $this->assertEquals([
            'name' => 'arg1',
            'type' => 'Int',
            'defaultValue' => 3,
            'description' => 'Arg description',
            'deprecation' => 'deprecated',
        ], $field->getArgument('arg1')->toArray());
        $this->assertEquals([
            'name' => 'arg2',
            'type' => 'String',
        ], $field->getArgument('arg2')->toArray());
    }
}
