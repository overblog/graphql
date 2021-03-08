<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Configuration;

use Overblog\GraphQLBundle\Configuration\Traits\ClassNameTrait;

class EnumConfiguration extends RootTypeConfiguration
{
    /** @var EnumValueConfiguration[] */
    protected array $values;

    public function __construct(string $name, array $values = [])
    {
        $this->name = $name;
        foreach ($values as $value) {
            $this->addValue($value);
        }
    }

    public static function get(string $name, array $values = []): EnumConfiguration
    {
        return new static($name, $values);
    }

    public function getGraphQLType(): string
    {
        return self::TYPE_ENUM;
    }

    public function addValue(EnumValueConfiguration $value): self
    {
        $this->values[] = $value;

        return $this;
    }

    public function getValue(string $name): ?EnumValueConfiguration
    {
        foreach ($this->values as $value) {
            if ($value->getName() === $name) {
                return $value;
            }
        }

        return null;
    }

    /**
     * @return EnumValueConfiguration[]
     */
    public function getValues(bool $indexedByName = false): array
    {
        if (!$indexedByName) {
            return $this->values;
        }

        $values = [];
        foreach ($this->values as $value) {
            $values[$value->getName()] = $value;
        }

        return $values;
    }

    /**
     * @return TypeConfiguration[]
     */
    public function getChildren()
    {
        return $this->getValues();
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'description' => $this->description,
            'values' => array_map(fn (EnumValueConfiguration $value) => $value->toArray(), $this->values),
            'extensions' => $this->getExtensionsArray(),
        ]);
    }
}
