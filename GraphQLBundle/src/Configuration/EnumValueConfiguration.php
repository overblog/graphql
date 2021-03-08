<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Configuration;

use Overblog\GraphQLBundle\Configuration\Traits\DeprecationTrait;

class EnumValueConfiguration extends TypeConfiguration
{
    use DeprecationTrait;
    protected $value;

    public function __construct(string $name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    public static function get(string $name, $value): EnumValueConfiguration
    {
        return new static($name, $value);
    }

    /** @return mixed */
    public function getValue()
    {
        return $this->value;
    }

    /** @param mixed $value */
    public function setValue($value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getGraphQLType(): string
    {
        return self::TYPE_ENUM_VALUE;
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'description' => $this->description,
            'deprecationReason' => $this->deprecationReason,
            'value' => $this->value,
            'extensions' => $this->getExtensionsArray(),
        ]);
    }
}
