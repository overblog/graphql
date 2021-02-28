<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Configuration;

use Overblog\GraphQLBundle\Configuration\Traits\DefaultValueTrait;
use Overblog\GraphQLBundle\Configuration\Traits\TypeTrait;

class InputFieldConfiguration extends TypeConfiguration
{
    use TypeTrait;
    use DefaultValueTrait;

    protected InputConfiguration $parent;

    public function __construct(string $name, string $type, $defaultValue = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->defaultValue = $defaultValue;
    }

    public static function get(string $name, string $type, $defaultValue = null): InputFieldConfiguration
    {
        return new static($name, $type, $defaultValue);
    }

    public function getGraphQLType(): string
    {
        return self::TYPE_INPUT_FIELD;
    }

    public function getParent(): InputConfiguration
    {
        return $this->parent;
    }

    public function setParent(InputConfiguration $parent): self
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
