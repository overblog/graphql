<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Configuration;

use Overblog\GraphQLBundle\Configuration\Traits\ClassNameTrait;

abstract class RootTypeConfiguration extends TypeConfiguration
{
    use ClassNameTrait;
}
