<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Tests\Configuration;

use Overblog\GraphQLBundle\Configuration\ArgumentConfiguration;
use Overblog\GraphQLBundle\Configuration\Configuration;
use Overblog\GraphQLBundle\Configuration\EnumConfiguration;
use Overblog\GraphQLBundle\Configuration\EnumValueConfiguration;
use Overblog\GraphQLBundle\Configuration\FieldConfiguration;
use Overblog\GraphQLBundle\Configuration\ObjectConfiguration;

class ConfigurationTest extends BaseConfigurationTest
{
    public function testMerging()
    {
        $configuration1 = (new Configuration())
            ->addType(self::object('Type1', ['f' => 'String']))
            ->addType(self::object('Type2', ['f' => 'String']));

        $configuration2 = (new Configuration())
            ->addType(self::object('Type3', ['f' => 'String']))
            ->addType(self::object('Type4', ['f' => 'String']));

        $configuration1->merge($configuration2);

        $this->assertCount(4, $configuration1->getTypes());
    }

    public function testRetrieveTypeByPath()
    {
        $configuration = new Configuration();
        $configuration
            ->addType(ObjectConfiguration::get('type1')
                ->addField(FieldConfiguration::get('field1', 'String')
                        ->addArgument(ArgumentConfiguration::get('arg1', 'Int'))
                    )
                );
        $configuration
            ->addType(EnumConfiguration::get('enum1')->addValue(EnumValueConfiguration::get('value1', 2)));

        $this->assertEquals($configuration->get('type1.field1.arg1')->getType(), 'Int');
        $this->assertEquals($configuration->get('enum1.value1')->getValue(), 2);
    }
}
