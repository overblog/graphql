<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Configuration;

class EnumValueConfiguration extends TypeConfiguration
{
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

    public function getGraphQLType(): string
    {
        return self::TYPE_ENUM_VALUE;
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'description' => $this->description,
            'deprecation' => $this->deprecation,
            'value' => $this->value,
            'extensions' => $this->getExtensionsArray(),
        ]);
    }
}
