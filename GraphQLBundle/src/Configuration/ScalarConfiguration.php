<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Configuration;

class ScalarConfiguration extends TypeConfiguration
{
    protected ?string $scalarType = null;

    protected ?string $serialize = null;
    protected ?string $parseValue = null;
    protected ?string $parseLiteral = null;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public static function get(string $name): ScalarConfiguration
    {
        return new static($name);
    }

    public function getGraphQLType(): string
    {
        return self::TYPE_SCALAR;
    }

    public function getScalarType(): ?string
    {
        return $this->scalarType;
    }

    public function setScalarType(string $scalarType): self
    {
        $this->scalarType = $scalarType;

        return $this;
    }

    public function getSerialize(): ?string
    {
        return $this->serialize;
    }

    public function setSerialize(string $serialize): self
    {
        $this->serialize = $serialize;

        return $this;
    }

    public function getParseValue(): ?string
    {
        return $this->parseValue;
    }

    public function setParseValue(string $parseValue): self
    {
        $this->parseValue = $parseValue;

        return $this;
    }

    public function getParseLiteral(): ?string
    {
        return $this->parseLiteral;
    }

    public function setParseLiteral(string $parseLiteral): self
    {
        $this->parseLiteral = $parseLiteral;

        return $this;
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'description' => $this->description,
            'deprecation' => $this->deprecation,
            'scalarType' => $this->scalarType,
            'serialize' => $this->serialize,
            'parseValue' => $this->parseValue,
            'parseLiteral' => $this->parseLiteral,
            'extensions' => $this->getExtensionsArray(),
        ]);
    }
}
