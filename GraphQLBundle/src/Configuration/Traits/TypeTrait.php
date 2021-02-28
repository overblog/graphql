<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Configuration\Traits;

use Symfony\Component\Validator\Constraints as Assert;

trait TypeTrait
{
    /**
     * @Assert\NotBlank
     */
    protected string $type;

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }
}
