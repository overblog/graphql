<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Extension\IsPublic;

use Overblog\GraphQLBundle\Extension\Extension;

/**
 * Extension to handle public visibility on fields
 */
class IsPublicExtension extends Extension
{
    const ALIAS = 'public';
}
