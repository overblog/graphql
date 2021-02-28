<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Configuration\Traits;

trait DefaultValueTrait
{
    protected $defaultValue = null;

    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    public function hasDefaultValue()
    {
        return null !== $this->defaultValue;
    }

    public function setDefaultValue($defaultValue): self
    {
        $this->defaultValue = $defaultValue;

        return $this;
    }
}
