<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Configuration;

use Overblog\GraphQLBundle\Configuration\Traits\CommonTrait;

abstract class TypeConfiguration
{
    use CommonTrait;

    const TYPE_OBJECT = 'object';
    const TYPE_FIELD = 'field';
    const TYPE_ARGUMENT = 'argument';
    const TYPE_INTERFACE = 'interface';
    const TYPE_INPUT = 'input';
    const TYPE_INPUT_FIELD = 'input_field';
    const TYPE_ENUM = 'enum';
    const TYPE_UNION = 'union';
    const TYPE_SCALAR = 'scalar';
    const TYPE_ENUM_VALUE = 'enum_value';

    const TYPES = [
        self::TYPE_OBJECT,
        self::TYPE_FIELD,
        self::TYPE_ARGUMENT,
        self::TYPE_INTERFACE,
        self::TYPE_INPUT,
        self::TYPE_INPUT_FIELD,
        self::TYPE_ENUM,
        self::TYPE_UNION,
        self::TYPE_SCALAR,
        self::TYPE_ENUM_VALUE,
    ];

    /**
     * @see https://facebook.github.io/graphql/draft/#sec-Input-and-Output-Types
     */
    const VALID_INPUT_TYPES = [
        self::TYPE_SCALAR,
        self::TYPE_ENUM,
        self::TYPE_INPUT,
    ];

    const VALID_OUTPUT_TYPES = [
        self::TYPE_SCALAR,
        self::TYPE_OBJECT,
        self::TYPE_INTERFACE,
        self::TYPE_UNION,
        self::TYPE_ENUM,
    ];

    abstract public function getGraphQLType(): string;

    abstract public function toArray(): array;

    /**
     * @return TypeConfiguration[]
     */
    public function getChildren()
    {
        return [];
    }

    public function getChild(string $name): ?TypeConfiguration
    {
        foreach ($this->getChildren() as $child) {
            if ($child->getName() === $name) {
                return $child;
            }
        }

        return null;
    }

    public function getParent(): ?TypeConfiguration
    {
        return null;
    }

    public function getPath(): string
    {
        $parent = $this;
        $path = [];
        while (null !== $parent) {
            $path[] = $parent->getName();
            $parent = $parent->getParent();
        }

        return join('.', $path);
    }
}
