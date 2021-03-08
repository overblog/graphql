<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Configuration\Traits;

trait ResolveTypeTrait
{
    protected ?string $resolveType = null;

    public function getResolveType(): ?string
    {
        return $this->resolveType;
    }

    public function setResolveType(string $resolveType): self
    {
        $this->resolveType = $resolveType;

        return $this;
    }
}
