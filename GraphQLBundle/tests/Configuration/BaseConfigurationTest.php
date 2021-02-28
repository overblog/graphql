<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Tests\Configuration;

use Overblog\GraphQLBundle\Configuration\FieldConfiguration;
use Overblog\GraphQLBundle\Configuration\InputConfiguration;
use Overblog\GraphQLBundle\Configuration\InputFieldConfiguration;
use Overblog\GraphQLBundle\Configuration\ObjectConfiguration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class BaseConfigurationTest extends TestCase
{
    protected function getValidator(): ValidatorInterface
    {
        return Validation::createValidatorBuilder()
            ->enableAnnotationMapping(true)
            ->addDefaultDoctrineAnnotationReader()
            ->getValidator();
    }

    protected static function object(string $name, array $fields = [])
    {
        $object = new ObjectConfiguration($name);
        foreach ($fields as $idx => $field) {
            list($name, $type) = is_string($idx) ? [$idx, $field] : $field;
            $object->addField(new FieldConfiguration($name, $type));
        }

        return $object;
    }

    protected static function input(string $name, array $fields = [])
    {
        $input = new InputConfiguration($name);
        foreach ($fields as $idx => $field) {
            list($name, $type) = is_string($idx) ? [$idx, $field] : $field;
            $input->addField(new InputFieldConfiguration($name, $type));
        }

        return $input;
    }
}
