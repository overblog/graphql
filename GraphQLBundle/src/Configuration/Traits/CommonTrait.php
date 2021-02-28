<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Configuration\Traits;

trait CommonTrait
{
    use NameTrait;
    use DescriptionTrait;
    use DeprecationTrait;
    use ExtensionTrait;
    use OriginTrait;
}
