<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Configuration\Traits;

trait TypeResolverTrait
{
    protected ?string $typeResolver = null;

    public function getTypeResolver(): ?string
    {
        return $this->typeResolver;
    }

    public function setTypeResolver(string $typeResolver): self
    {
        $this->typeResolver = $typeResolver;

        return $this;
    }
}
