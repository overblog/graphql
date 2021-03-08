<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Configuration;

use Closure;

class ScalarConfiguration extends RootTypeConfiguration
{
    protected ?string $scalarType = null;

    protected ?Closure $serialize = null;
    protected ?Closure $parseValue = null;
    protected ?Closure $parseLiteral = null;

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

    public function getSerialize(): ?Closure
    {
        return $this->serialize;
    }

    public function setSerialize(callable $serialize): self
    {
        $this->serialize = Closure::fromCallable($serialize);

        return $this;
    }

    public function getParseValue(): ?Closure
    {
        return $this->parseValue;
    }

    public function setParseValue(callable $parseValue): self
    {
        $this->parseValue = Closure::fromCallable($parseValue);

        return $this;
    }

    public function getParseLiteral(): ?Closure
    {
        return $this->parseLiteral;
    }

    public function setParseLiteral(callable $parseLiteral): self
    {
        $this->parseLiteral = Closure::fromCallable($parseLiteral);

        return $this;
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'description' => $this->description,
            'scalarType' => $this->scalarType,
            'serialize' => $this->serialize,
            'parseValue' => $this->parseValue,
            'parseLiteral' => $this->parseLiteral,
            'extensions' => $this->getExtensionsArray(),
        ]);
    }
}
