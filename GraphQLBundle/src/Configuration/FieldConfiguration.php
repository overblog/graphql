<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Configuration;

use Overblog\GraphQLBundle\Configuration\Traits\DeprecationTrait;
use Overblog\GraphQLBundle\Configuration\Traits\TypeTrait;
use Symfony\Component\Validator\Constraints as Assert;

class FieldConfiguration extends TypeConfiguration
{
    use TypeTrait;
    use DeprecationTrait;

    /** @var ObjectConfiguration|InterfaceConfiguration */
    protected TypeConfiguration $parent;

    /**
     * @var ArgumentConfiguration[]
     * @Assert\Valid
     */
    protected array $arguments = [];
    protected $resolve = null;
    protected ?string $complexity = null;
    protected array $middlewares = [];

    public function __construct(string $name, string $type, $resolve = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->resolve = $resolve;
    }

    public static function get(string $name, string $type, string $resolve = null): FieldConfiguration
    {
        return new static($name, $type, $resolve);
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

    public function getResolve()
    {
        return $this->resolve;
    }

    public function setResolve($resolve): self
    {
        $this->resolve = $resolve;

        return $this;
    }

    public function getComplexity(): ?string
    {
        return $this->complexity;
    }

    public function setComplexity(string $complexity): self
    {
        $this->complexity = $complexity;

        return $this;
    }

    /** @return ArgumentConfiguration[] */
    public function getArguments(bool $indexedByName = false): array
    {
        if (!$indexedByName) {
            return $this->arguments;
        }
        $args = [];
        foreach ($this->arguments as $argument) {
            $args[$argument->getName()] = $argument;
        }

        return $args;
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

    public function getChild(string $name): ?ArgumentConfiguration
    {
        return $this->getArgument($name);
    }

    public function toArray(): array
    {
        $array = array_filter([
            'name' => $this->name,
            'type' => $this->type,
            'description' => $this->description,
            'deprecationReason' => $this->deprecationReason,
            'complexity' => $this->complexity,
            'resolve' => $this->resolve,
            'arguments' => array_map(fn (ArgumentConfiguration $argument) => $argument->toArray(), $this->arguments),
            'extensions' => $this->getExtensionsArray(),
        ]);

        return $array;
    }
}
