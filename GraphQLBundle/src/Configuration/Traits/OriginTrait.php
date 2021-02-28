<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Configuration\Traits;

trait OriginTrait
{
    protected array $origin = [];

    public function getOrigin(): array
    {
        return $this->origin;
    }

    public function setOrigin(array $origin): self
    {
        $this->origin = $origin;

        return $this;
    }
}
