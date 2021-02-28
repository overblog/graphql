<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Configuration;

use Overblog\GraphQLBundle\Configuration\Traits\TypeResolverTrait;

class UnionConfiguration extends TypeConfiguration
{
    use TypeResolverTrait;

    protected array $types = [];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public static function get(string $name): UnionConfiguration
    {
        return new static($name);
    }

    public function getGraphQLType(): string
    {
        return self::TYPE_UNION;
    }

    /** @return string[] */
    public function getTypes(): array
    {
        return $this->types;
    }

    public function setTypes(array $types = []): self
    {
        foreach ($types as $type) {
            $this->addType($type);
        }

        return $this;
    }

    public function addType(string $type): self
    {
        if (!in_array($type, $this->types)) {
            $this->types[] = $type;
        }

        return $this;
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'description' => $this->description,
            'deprecation' => $this->deprecation,
            'typeResolver' => $this->typeResolver,
            'types' => $this->types,
            'extensions' => $this->getExtensionsArray(),
        ]);
    }
}
