<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Extension\Builder;

use Overblog\GraphQLBundle\Configuration\TypeConfiguration;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

interface BuilderInterface
{
    /** Types configurations supported by this builder */
    public function supports(TypeConfiguration $typeConfiguration): bool;

    /** Update the configuration according to this builder */
    public function updateConfiguration(TypeConfiguration $typeConfiguration, $builderConfiguration): void;

    /** Validate the given configuration array */
    public function getConfiguration(TypeConfiguration $type = null): TreeBuilder;
}
