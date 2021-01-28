<?php

declare(strict_types=1);

namespace Overblog\GraphQL\Bundle\ProfilerBundle;

use Overblog\GraphQL\Bundle\ProfilerBundle\DependencyInjection\GraphQLProfilerExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class GraphQLProfilerBundle extends Bundle
{
    public function getContainerExtension()
    {
        if (!$this->extension instanceof ExtensionInterface) {
            $this->extension = new GraphQLProfilerExtension();
        }

        return $this->extension;
    }
}
