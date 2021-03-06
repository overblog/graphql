<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Configuration;

use Overblog\GraphQLBundle\Configuration\Traits\FieldsTrait;
use Overblog\GraphQLBundle\Configuration\Traits\ResolveTypeTrait;

class InterfaceConfiguration extends RootTypeConfiguration
{
    use ResolveTypeTrait;
    use FieldsTrait;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public static function get(string $name): InterfaceConfiguration
    {
        return new static($name);
    }

    public function getGraphQLType(): string
    {
        return self::TYPE_INTERFACE;
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'description' => $this->description,
            'resolveType' => $this->resolveType,
            'fields' => array_map(fn (FieldConfiguration $field) => $field->toArray(), $this->fields),
            'extensions' => $this->getExtensionsArray(),
        ]);
    }
}
