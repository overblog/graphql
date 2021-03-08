<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Configuration\Traits;

trait DeprecationTrait
{
    protected ?string $deprecationReason = null;

    public function getDeprecationReason(): ?string
    {
        return $this->deprecationReason;
    }

    public function setDeprecationReason(?string $deprecationReason): self
    {
        $this->deprecationReason = $deprecationReason;

        return $this;
    }

    public function hasDeprecationReason(): bool
    {
        return $this->deprecationReason !== null;
    }
}
