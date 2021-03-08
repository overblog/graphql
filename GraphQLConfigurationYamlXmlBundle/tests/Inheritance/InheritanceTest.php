<?php

declare(strict_types=1);

namespace Overblog\GraphQL\Bundle\ConfigurationYamlXmlBundle\Tests\Inheritance;

use Overblog\GraphQL\Bundle\ConfigurationYamlXmlBundle\ConfigurationYamlParser;
use Overblog\GraphQLBundle\Configuration\Configuration;
use Overblog\GraphQL\Bundle\ConfigurationYamlXmlBundle\Processor\InheritanceProcessor;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class InheritanceTest extends WebTestCase
{
    protected function getInheritanceConfiguration()
    {
        $parser = new ConfigurationYamlParser([__DIR__.DIRECTORY_SEPARATOR.'../fixtures/inheritance']);

        return $parser->getConfigurationArray();
    }

    public function testObjectInheritance(): void
    {
        $config = $this->getInheritanceConfiguration();
        
        $this->assertArrayHasKey('Query', $config);
        // TODO(mcg-web): understand why travis fields order diffed from local test
        $this->assertEquals(
            [
                'type' => 'object',
                InheritanceProcessor::INHERITS_KEY => ['QueryFoo', 'QueryBar', 'QueryHelloWord'],
                'class_name' => 'QueryType',
                'decorator' => false,
                'config' => [
                    'fields' => [
                        'sayHello' => [
                            'type' => 'String',
                        ],
                        'period' => [
                            'type' => 'Period',
                        ],
                        'bar' => [
                            'type' => 'String',
                        ],
                    ],
                    'name' => 'Query',
                    'interfaces' => ['QueryHelloWord'],
                    'builders' => [],
                ],
            ],
            $config['Query']
        );
    }

    public function testEnumInheritance(): void
    {
        $config = $this->getInheritanceConfiguration();
        $this->assertArrayHasKey('Period', $config);
        $this->assertSame(
            [
                'type' => 'enum',
                InheritanceProcessor::INHERITS_KEY => ['Day', 'Month', 'Year'],
                'class_name' => 'PeriodType',
                'decorator' => false,
                'config' => [
                    'values' => [
                        'DAY' => ['value' => 1],
                        'MONTH' => ['value' => 2],
                        'YEAR' => ['value' => 3],
                    ],
                    'name' => 'Period',
                ],
            ],
            $config['Period']
        );
    }

    public function testRelayInheritance(): void
    {
        $config = $this->getInheritanceConfiguration();
        $this->assertArrayHasKey('ChangeEventInput', $config);
        $this->assertSame(
            [
                'type' => 'input-object',
                InheritanceProcessor::INHERITS_KEY => ['AddEventInput'],
                'class_name' => 'ChangeEventInputType',
                'decorator' => false,
                'config' => [
                    'name' => 'ChangeEventInput',
                    'fields' => [
                        'title' => ['type' => 'String!'],
                        'clientMutationId' => ['type' => 'String'],
                        'id' => ['type' => 'ID!'],
                    ],
                ],
            ],
            $config['ChangeEventInput']
        );
    }

    public function testDecoratorTypeShouldRemovedFromFinalConfig(): void
    {
        $config = $this->getInheritanceConfiguration();
        $this->assertArrayNotHasKey('QueryBarDecorator', $config);
        $this->assertArrayNotHasKey('QueryFooDecorator', $config);
    }

    public function testDecoratorInterfacesShouldMerge(): void
    {
        $config = $this->getInheritanceConfiguration();
        $this->assertArrayHasKey('ABCDE', $config);
        $this->assertSame(
            [
                'type' => 'object',
                InheritanceProcessor::INHERITS_KEY => ['DecoratorA', 'DecoratorB', 'DecoratorD'],
                'class_name' => 'ABCDEType',
                'decorator' => false,
                'config' => [
                    'interfaces' => ['A', 'AA', 'B', 'C', 'D', 'E'],
                    'fields' => [
                        'a' => [
                            'type' => 'String',
                        ],
                        'aa' => [
                            'type' => 'String',
                        ],
                        'b' => [
                            'type' => 'String',
                        ],
                        'c' => [
                            'type' => 'String',
                        ],
                        'd' => [
                            'type' => 'String',
                        ],
                        'e' => [
                            'type' => 'String',
                        ],
                    ],
                    'name' => 'ABCDE',
                    'builders' => [],
                ],
            ],
            $config['ABCDE']
        );
    }
}
