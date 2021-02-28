<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Configuration\Traits;

use Overblog\GraphQLBundle\Configuration\FieldConfiguration;
use Symfony\Component\Validator\Constraints as Assert;

trait FieldsTrait
{
    /**
     * @Assert\Valid
     *
     * @var FieldConfiguration[]
     */
    protected array $fields = [];

    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * Get the latest defined field with given name
     */
    public function getField(string $name): ?FieldConfiguration
    {
        foreach (array_reverse($this->fields) as $field) {
            if ($field->getName() === $name) {
                return $field;
            }
        }

        return null;
    }

    public function addField(FieldConfiguration $fieldConfiguration): self
    {
        if (in_array($fieldConfiguration, $this->fields, true)) {
            return $this;
        }
        $fieldConfiguration->setParent($this);
        $this->fields[] = $fieldConfiguration;

        return $this;
    }

    /**
     * @param FieldConfiguration[] $fields
     */
    public function addFields(array $fields): self
    {
        foreach ($fields as $field) {
            $this->addField($field);
        }

        return $this;
    }

    /**
     * @return TypeConfiguration[]
     */
    public function getChildren()
    {
        return $this->getFields();
    }
}
