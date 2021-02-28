<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Configuration\Traits;

trait DeprecationTrait
{
    protected ?string $deprecation = null;

    public function getDeprecation(): ?string
    {
        return $this->deprecation;
    }

    public function setDeprecation(?string $deprecation): self
    {
        $this->deprecation = $deprecation;

        return $this;
    }
}
