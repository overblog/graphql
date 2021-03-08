<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Configuration\Traits;

trait ClassNameTrait
{
    protected ?string $className = null;

    public function getClassName(): ?string
    {
        return $this->className ?: sprintf('%sType', $this->getName());
    }

    public function setClassName(?string $className): self
    {
        $this->className = $className;

        return $this;
    }
}
