<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Configuration;

use Overblog\GraphQLBundle\Configuration\Traits\TypeTrait;
use Symfony\Component\Validator\Constraints as Assert;

class FieldConfiguration extends TypeConfiguration
{
    use TypeTrait;

    /** @var ObjectConfiguration|InterfaceConfiguration */
    protected TypeConfiguration $parent;

    /**
     * @var ArgumentConfiguration[]
     * @Assert\Valid
     */
    protected array $arguments = [];

    protected ?string $resolver;

    public function __construct(string $name, string $type, string $resolver = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->resolver = $resolver;
    }

    public static function get(string $name, string $type, string $resolver = null): FieldConfiguration
    {
        return new static($name, $type, $resolver);
    }

    public function getGraphQLType(): string
    {
        return self::TYPE_FIELD;
    }

    /** @return ObjectConfiguration|InterfaceConfiguration */
    public function getParent(): TypeConfiguration
    {
        return $this->parent;
    }

    /** @param ObjectConfiguration|InterfaceConfiguration $parent */
    public function setParent(TypeConfiguration $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function getResolver(): ?string
    {
        return $this->resolver;
    }

    public function setResolver(string $resolver): self
    {
        $this->resolver = $resolver;

        return $this;
    }

    /** @return ArgumentConfiguration[] */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Find the latest argument defined with given name
     */
    public function getArgument(string $name): ?ArgumentConfiguration
    {
        foreach (array_reverse($this->arguments) as $argument) {
            if ($argument->getName() === $name) {
                return $argument;
            }
        }

        return null;
    }

    public function addArgument(ArgumentConfiguration $argumentConfiguration): self
    {
        if (in_array($argumentConfiguration, $this->arguments, true)) {
            return $this;
        }
        $argumentConfiguration->setParent($this);
        $this->arguments[] = $argumentConfiguration;

        return $this;
    }

    /**
     * @return TypeConfiguration[]
     */
    public function getChildren()
    {
        return $this->getArguments();
    }

    public function toArray(): array
    {
        $array = array_filter([
            'name' => $this->name,
            'type' => $this->type,
            'description' => $this->description,
            'deprecation' => $this->deprecation,
            'resolver' => $this->resolver,
            'arguments' => array_map(fn (ArgumentConfiguration $argument) => $argument->toArray(), $this->arguments),
            'extensions' => $this->getExtensionsArray(),
        ]);

        return $array;
    }
}
