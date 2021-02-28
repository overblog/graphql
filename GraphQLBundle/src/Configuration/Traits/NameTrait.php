<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Configuration\Traits;

trait NameTrait
{
    protected string $name;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
