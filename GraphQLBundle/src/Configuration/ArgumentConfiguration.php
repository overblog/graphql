<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Configuration;

use Overblog\GraphQLBundle\Configuration\Traits\DefaultValueTrait;
use Overblog\GraphQLBundle\Configuration\Traits\TypeTrait;

class ArgumentConfiguration extends TypeConfiguration
{
    use TypeTrait;
    use DefaultValueTrait;

    protected FieldConfiguration $parent;

    public function __construct(string $name, string $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    public static function get(string $name, string $type): ArgumentConfiguration
    {
        return new static($name, $type);
    }

    public function getGraphQLType(): string
    {
        return self::TYPE_ARGUMENT;
    }

    public function getParent(): FieldConfiguration
    {
        return $this->parent;
    }

    public function setParent(FieldConfiguration $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function toArray(): array
    {
        $array = array_filter([
            'name' => $this->name,
            'type' => $this->type,
            'description' => $this->description,
            'deprecation' => $this->deprecation,
            'extensions' => $this->getExtensionsArray(),
        ]);

        if ($this->hasDefaultValue()) {
            $array['defaultValue'] = $this->defaultValue;
        }

        return $array;
    }
}
