<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Event;

use Overblog\GraphQLBundle\Configuration\Configuration;
use Symfony\Contracts\EventDispatcher\Event;

final class ConfigurationEvent extends Event
{
    private Configuration $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    public function setConfiguration(Configuration $configuration): void
    {
        $this->configuration = $configuration;
    }
}
