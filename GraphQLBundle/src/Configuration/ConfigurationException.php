<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Configuration;

use RuntimeException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

final class ConfigurationException extends RuntimeException
{
    /** @var ConfigurationExceptionType[] */
    protected array $errors = [];

    protected function updateMessage(): void
    {
        $message = sprintf("Found %s error(s) in the GraphQL Configuration:\n", count($this->errors));
        foreach ($this->errors as $error) {
            $type = $error->getType();

            $message .= sprintf("[%s %s] %s\n", $type->getGraphQLType(), $type->getName(), $error->getError());
        }

        $this->message = $message;
    }

    public function addError(TypeConfiguration $type, string $error)
    {
        $this->errors[] = new ConfigurationExceptionType($type, $error);
        $this->updateMessage();
    }

    public function addViolation(ConstraintViolation $violation)
    {
        if ($violation->getInvalidValue() instanceof TypeConfiguration) {
            $this->addError($violation->getInvalidValue(), $violation->getMessage());
        }
    }

    public function addViolations(ConstraintViolationList $violations)
    {
        foreach ($violations as $violation) {
            $this->addViolation($violation);
        }
    }

    public function hasErrors()
    {
        return count($this->errors) > 0;
    }
}
