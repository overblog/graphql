<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\ConfigurationProvider;

use Overblog\GraphQLBundle\Configuration\Configuration;

interface ConfigurationProviderInterface
{
    public function getConfiguration(): Configuration;
}
