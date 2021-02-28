<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Tests\Configuration;

use Overblog\GraphQLBundle\Configuration\Configuration;

class ConfigurationTest extends BaseConfigurationTest
{
    /**
     * Test configuration merging
     */
    public function testGetTypeReturnLatestDefinedType()
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
}
