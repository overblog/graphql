<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Tests\Extension\Builder;

use Overblog\GraphQLBundle\Configuration\Configuration;
use Overblog\GraphQLBundle\Configuration\FieldConfiguration;
use Overblog\GraphQLBundle\Configuration\ObjectConfiguration;
use Overblog\GraphQLBundle\Configuration\TypeConfiguration;
use Overblog\GraphQLBundle\Extension\Builder\BuilderExtension;
use Overblog\GraphQLBundle\Extension\Builder\BuilderInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class MyBuilder implements BuilderInterface
{
    public function supports(TypeConfiguration $typeConfiguration): bool
    {
        return TypeConfiguration::TYPE_OBJECT === $typeConfiguration->getGraphQLType();
    }

    public function getConfiguration(TypeConfiguration $typeConfiguration = null): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('MyBuilder');
        $treeBuilder
            ->getRootNode()
            ->children()
                ->scalarNode('config1')->isRequired()->end()
                ->scalarNode('config2')->isRequired()->end()
                ->scalarNode('config3')->defaultValue('baz')->end()
            ->end();

        return $treeBuilder;
    }

    public function updateConfiguration(TypeConfiguration $typeConfiguration, $builderConfiguration): void
    {
    }
}

class MyBuilder2 implements BuilderInterface
{
    public function supports(TypeConfiguration $typeConfiguration): bool
    {
        return true;
    }

    public function getConfiguration(TypeConfiguration $typeConfiguration = null): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('MyBuilder2', 'scalar');
        $treeBuilder
            ->getRootNode()
            ->isRequired();

        return $treeBuilder;
    }

    public function updateConfiguration(TypeConfiguration $typeConfiguration, $builderConfiguration): void
    {
    }
}

class BuilderExtensionTest extends TestCase
{
    public function setUp(): void
    {
        $this->c = new Configuration();
    }

    protected function getExtension(): BuilderExtension
    {
        $mock1 = $this->getMockClass(MyBuilder::class, ['updateConfiguration']);
        $mock2 = $this->getMockClass(MyBuilder2::class, ['updateConfiguration']);

        $builders = [
            'MyBuilder' => new $mock1(),
            'MyBuilder2' => new $mock2(),
        ];

        return new BuilderExtension($builders);
    }

    public function testMissingBuilder()
    {
        $field = FieldConfiguration::get('MyField', 'String');
        $this->expectExceptionMessage('Builder "UnknowBuilder" not found. Available builders: MyBuilder');
        $this->getExtension()->handleConfiguration($this->c, $field, ['name' => 'UnknowBuilder']);
    }

    public function testBuilderIncompatibleType()
    {
        $field = FieldConfiguration::get('MyField', 'String');
        $this->expectExceptionMessage('The builder "MyBuilder" doesn\'t support GraphQL type "field"');
        $this->getExtension()->handleConfiguration($this->c, $field, ['name' => 'MyBuilder']);
    }

    public function testBuilderInvalidConfiguration()
    {
        $object = ObjectConfiguration::get('MyType');
        $this->expectExceptionMessageMatches('/Unrecognized option "foo" under "MyBuilder"/');
        $this->getExtension()->handleConfiguration($this->c, $object, ['name' => 'MyBuilder', 'configuration' => ['foo' => 'bar']]);
    }

    public function testBuilderConfiguration()
    {
        $object = ObjectConfiguration::get('MyType');
        $this->getExtension()->getBuilder('MyBuilder')
            ->expects($this->once())
            ->method('updateConfiguration')->with($object, ['config1' => 'foo', 'config2' => 'bar', 'config3' => 'baz']);
        $this->getExtension()->handleConfiguration($this->c, $object, ['name' => 'MyBuilder', 'configuration' => ['config1' => 'foo', 'config2' => 'bar']]);
        $this->assertTrue(true);
    }

    public function testBuilderConfigurationString()
    {
        $object = ObjectConfiguration::get('MyType');

        $this->getExtension()->getBuilder('MyBuilder2')
            ->expects($this->once())
            ->method('updateConfiguration')->with($object, 'goozii');
        $this->getExtension()->handleConfiguration($this->c, $object, ['name' => 'MyBuilder2', 'configuration' => 'goozii']);
        $this->assertTrue(true);
    }
}
