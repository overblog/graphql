<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Configuration;

class ConfigurationExceptionType
{
    public function __construct(TypeConfiguration $type, string $error)
    {
        $this->type = $type;
        $this->error = $error;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getError()
    {
        return $this->error;
    }
}
