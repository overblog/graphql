<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Configuration;

use Overblog\GraphQLBundle\Configuration\Traits\FieldsTrait;

class ObjectConfiguration extends TypeConfiguration
{
    use FieldsTrait;

    /** @var string[] */
    protected array $interfaces = [];

    protected ?string $isTypeOf = null;
    protected ?string $fieldsResolver = null;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public static function get(string $name): ObjectConfiguration
    {
        return new static($name);
    }

    public function getGraphQLType(): string
    {
        return self::TYPE_OBJECT;
    }

    /** @return string[] */
    public function getInterfaces(): array
    {
        return $this->interfaces;
    }

    public function setInterfaces(array $interfaces = []): self
    {
        $this->interfaces = $interfaces;

        return $this;
    }

    public function addInterface(string $interface)
    {
        if (!in_array($interface, $this->interfaces)) {
            $this->interfaces[] = $interface;
        }
    }

    public function getIsTypeOf(): ?string
    {
        return $this->isTypeOf;
    }

    public function setIsTypeOf(string $isTypeOf): self
    {
        $this->isTypeOf = $isTypeOf;

        return $this;
    }

    public function getFieldsResolver(): ?string
    {
        return $this->fieldsResolver;
    }

    public function setFieldsResolver(string $fieldsResolver): self
    {
        $this->fieldsResolver = $fieldsResolver;

        return $this;
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'description' => $this->description,
            'deprecation' => $this->deprecation,
            'interfaces' => $this->interfaces,
            'isTypeOf' => $this->isTypeOf,
            'fieldsResolver' => $this->fieldsResolver,
            'fields' => array_map(fn (FieldConfiguration $field) => $field->toArray(), $this->fields),
            'extensions' => $this->getExtensionsArray(),
        ]);
    }
}
