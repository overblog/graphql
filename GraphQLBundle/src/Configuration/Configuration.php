<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Configuration;

use Exception;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class Configuration
{
    const NAME_REGEXP = '/^[_A-Za-z][_0-9A-Za-z]*$/';
    const BUILTIN_SCALARS = ['Int', 'Float', 'String', 'Boolean', 'ID'];

    const DUPLICATE_STRATEGY_FORBIDDEN = 'forbidden';
    const DUPLICATE_STRATEGY_OVERRIDE_SAME_TYPE = 'override_same_type';
    const DUPLICATE_STRATEGY_OVERRIDE_ALL = 'override';

    protected string $duplicateStrategy = self::DUPLICATE_STRATEGY_FORBIDDEN;

    /**
     * @Assert\Valid
     *
     * @var TypeConfiguration[]
     */
    protected array $types = [];

    public function __construct(string $duplicateStrategy = self::DUPLICATE_STRATEGY_FORBIDDEN)
    {
        $this->duplicateStrategy = $duplicateStrategy;
    }

    /**
     * @param string[] $gqlTypes
     *
     * @return TypeConfiguration[]
     */
    public function getTypes(string ...$gqlTypes): array
    {
        if (0 === count($gqlTypes)) {
            return $this->types;
        }

        return array_filter($this->types, fn (TypeConfiguration $type) => in_array($type->getGraphQLType(), $gqlTypes));
    }

    /**
     * Retrieve latest type defined with given name
     */
    public function getType(string $name): ?TypeConfiguration
    {
        foreach (array_reverse($this->types) as $type) {
            if ($type->getName() === $name) {
                return $type;
            }
        }

        return null;
    }

    public function addType(TypeConfiguration $type): self
    {
        // Type has already been added
        if (in_array($type, $this->types, true)) {
            return $this;
        }

        switch (true) {
            case $type instanceof ObjectConfiguration:
            case $type instanceof EnumConfiguration:
            case $type instanceof InterfaceConfiguration:
            case $type instanceof UnionConfiguration:
            case $type instanceof InputConfiguration:
            case $type instanceof ScalarConfiguration:
                $this->types[] = $type;
                break;
            default:
                throw new Exception(sprintf('Type configuration object %s can\'t be added directly into the root configuration '.get_class($type)));
        }

        return $this;
    }

    public function addTypes(iterable $types): self
    {
        foreach ($types as $type) {
            $this->addType($type);
        }

        return $this;
    }

    public function removeType(TypeConfiguration $type)
    {
        $key = array_search($type, $this->types, true);
        if (false !== $key) {
            unset($this->types[$key]);
        }
    }

    /**
     * Merge given configuration into current configuration
     */
    public function merge(Configuration $configuration)
    {
        foreach ($configuration->getTypes() as $type) {
            $this->addType($type);
        }
    }

    /**
     * Apply a function to every configuration element
     * including fields, arguments, ...
     */
    public function apply(callable $callback, array $types = null)
    {
        $types = null !== $types ? $types : $this->types;
        foreach ($types as $type) {
            $callback($type);
            $this->apply($callback, $type->getChildren());
        }
    }

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context)
    {
        // Validate and remove duplicates
        $this->validateNames($context);

        // Validate types
        $this->validateFieldsTypes($context);
    }

    /**
     * Recursively check that names are valids and handle duplication on root types, fields and arguments
     * When overriding is allowed, the last defined items erase the other one
     * (note: For root types, the latest is a the beginning of the array).
     *
     * @return void
     */
    protected function validateNames(ExecutionContextInterface $context, array $configurations = null, TypeConfiguration $parent = null)
    {
        if (null === $configurations) {
            $configurations = $this->types;
        }

        // Group all types by name and validate name format
        $itemsByName = array_reduce($configurations, function ($indexed, TypeConfiguration $type) use ($context) {
            $name = $type->getName();
            if (!preg_match(self::NAME_REGEXP, $name)) {
                $message = \sprintf('The name "%s" is not valid. Allowed characters are letters, numbers and _.', $name);
                $this->createViolation($context, $message, $type);

                return $indexed;
            }

            if (!isset($indexed[$name])) {
                $indexed[$name] = [];
            }

            $indexed[$name][] = $type;

            return $indexed;
        }, []);

        foreach ($itemsByName as $name => $items) {
            if (count($items) > 1 && self::DUPLICATE_STRATEGY_FORBIDDEN === $this->duplicateStrategy) {
                $this->createDuplicationViolation($context, $name, $items, $parent);
                continue;
            }
            $gqlType = current($items)->getGraphQLType();
            foreach ($items as $index => $item) {
                if (
                    self::DUPLICATE_STRATEGY_OVERRIDE_SAME_TYPE === $this->duplicateStrategy
                    && null === $parent
                    && $item->getGraphQLType() !== $gqlType
                ) {
                    $this->createDuplicationViolation($context, $name, $items, $parent);
                } else {
                    // Keep the last item defined with this name
                    $keepedIndex = count($items) - 1;
                    if ($keepedIndex === $index) {
                        $this->validateNames($context, $item->getChildren(), $item);
                    } else {
                        $this->removeType($item);
                    }
                }
            }
        }
    }

    /**
     * @param ObjectConfiguration|InterfaceConfiguration|InputConfiguration|FieldConfiguration|InputFieldConfiguration|ArgumentConfiguration|null $type
     *
     * @return void
     */
    protected function createViolation(ExecutionContextInterface $context, string $message, TypeConfiguration $type = null)
    {
        $path = [];
        $typesWithParent = [TypeConfiguration::TYPE_FIELD, TypeConfiguration::TYPE_INPUT_FIELD, TypeConfiguration::TYPE_ARGUMENT];
        $parent = $type;
        while ($parent) {
            $path[] = $parent->getName();
            if (in_array($parent->getGraphQLType(), $typesWithParent)) {
                $parent = $parent->getParent();
            } else {
                $parent = null;
            }
        }

        $path = join('.', array_reverse($path));

        $context
            ->buildViolation($message)
            ->setInvalidValue($type)
            ->atPath($path)
            ->addViolation();
    }

    protected function createDuplicationViolation(ExecutionContextInterface $context, string $name, array $items, TypeConfiguration $type = null)
    {
        switch (true) {
            case null === $type:
                $typeLabel = 'types';
                break;
            case $type instanceof ObjectConfiguration:
            case $type instanceof InterfaceConfiguration:
            case $type instanceof InputConfiguration:
                $typeLabel = 'fields';
                break;
            case $type instanceof FieldConfiguration:
            case $type instanceof InputFieldConfiguration:
                $typeLabel = 'arguments';
                break;
        }

        $message = sprintf(
            'Naming collision on name "%s", found %s %s using it.',
            $name,
            count($items),
            $typeLabel
        );
        if (null === $type) {
            $message .= "\n".join("\n", array_map(
                fn (TypeConfiguration $type) => sprintf('  - GraphQL %s %s', $type->getGraphQLType(), json_encode($type->getOrigin())),
                $items
            ));
        }

        $this->createViolation($context, $message, $type);
    }

    /**
     * Ensure that types with fields have at least one field
     * Ensure that used types are known
     * Ensure that fields type on input are scalar, enum or other input
     * Ensure that fields type on object or interface are not input
     *
     * @return void
     */
    protected function validateFieldsTypes(ExecutionContextInterface $context)
    {
        $configurations = $this->getTypes(
            TypeConfiguration::TYPE_INPUT,
            TypeConfiguration::TYPE_OBJECT,
            TypeConfiguration::TYPE_INTERFACE
        );

        foreach ($configurations as $configuration) {
            $fields = $configuration->getFields();
            if (0 === count($fields)) {
                $message = sprintf('The %s "%s" has no field', $configuration->getGraphQLType(), $configuration->getName());
                $this->createViolation($context, $message, $configuration);
                continue;
            }
            switch (true) {
                case $configuration instanceof InputConfiguration:
                    $acceptableTypes = TypeConfiguration::VALID_INPUT_TYPES;
                    break;
                default:
                    $acceptableTypes = TypeConfiguration::VALID_OUTPUT_TYPES;
                    break;
            }
            foreach ($fields as $field) {
                $fieldType = $this->cleanType($field->getType());
                $type = $this->getType($fieldType);
                if (null === $type) {
                    if (!in_array($fieldType, self::BUILTIN_SCALARS)) {
                        $this->createViolation($context, sprintf('Unknow type "%s". Check the spelling', $field->getType()), $field);
                        continue;
                    }
                } elseif (!in_array($type->getGraphQLType(), $acceptableTypes)) {
                    $message = \sprintf(
                        'Incompatible type "%s" (%s). Accepted types for %s fields are : %s',
                        $field->getType(),
                        $type->getGraphQLType(),
                        $configuration->getGraphQLType(),
                        join(', ', $acceptableTypes)
                    );
                    $this->createViolation($context, $message, $field);
                    continue;
                }

                if ($field instanceof FieldConfiguration) {
                    foreach ($field->getArguments() as $argument) {
                        $argType = $this->cleanType($argument->getType());
                        $type = $this->getType($argType);
                        if (null === $type) {
                            if (!in_array($argType, self::BUILTIN_SCALARS)) {
                                $this->createViolation($context, sprintf('Unknow type "%s". Check the spelling', $argument->getType()), $argument);
                                continue;
                            }
                        } elseif (!in_array($type->getGraphQLType(), TypeConfiguration::VALID_INPUT_TYPES)) {
                            $message = \sprintf(
                                'Incompatible type "%s". Accepted types for arguments are : %s',
                                $argument->getType(),
                                join(', ', TypeConfiguration::VALID_INPUT_TYPES)
                            );
                            $this->createViolation($context, $message, $argument);
                        }
                    }
                }
            }
        }
    }

    protected function cleanType(string $type): string
    {
        return str_replace(['[', ']', '!'], '', $type);
    }
}
