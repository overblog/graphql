<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\ConfigurationProvider\Type;

use Overblog\GraphQLBundle\Configuration\Configuration;
use Overblog\GraphQLBundle\Configuration\FieldConfiguration;
use Overblog\GraphQLBundle\Configuration\ObjectConfiguration;
use Overblog\GraphQLBundle\ConfigurationProvider\ConfigurationProviderInterface;

class PageInfoConfiguration implements ConfigurationProviderInterface
{
    public function getConfiguration(): Configuration
    {
        $configuration = new Configuration();
        $pageInfoConfiguration = ObjectConfiguration::get('PageInfo')
            ->setDescription('Information about pagination in a connection.')
            ->addFields([
                FieldConfiguration::get('hasNextPage', 'Boolean!')
                    ->setDescription('When paginating forwards, are there more items?'),
                FieldConfiguration::get('hasPreviousPage', 'Boolean!')
                    ->setDescription('When paginating backwards, are there more items?'),
                FieldConfiguration::get('startCursor', 'String')
                    ->setDescription('When paginating backwards, the cursor to continue.'),
                FieldConfiguration::get('endCursor', 'String')
                    ->setDescription('When paginating forwards, the cursor to continue.'),
            ]);

        $configuration->addType($pageInfoConfiguration);

        return $configuration;
    }
}
