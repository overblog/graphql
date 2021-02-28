<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Extension;

use function sprintf;

/**
 * GraphQL Extension registry
 */
class ExtensionRegistry
{
    /** @var array<string,Extension> */
    protected iterable $extensions = [];

    public function __construct(iterable $extensions = [])
    {
        foreach ($extensions as $extension) {
            $this->addExtension($extension);
        }
    }

    public function getExtension(string $alias): Extension
    {
        if (!isset($this->extensions[$alias])) {
            $message = sprintf('Unknow extension with alias "%s". Available extensions: %s.',
                $alias,
                join(array_keys($this->extensions))
            );
            throw new ExtensionException($message);
        }

        return $this->extensions[$alias];
    }

    public function addExtension(Extension $extension): self
    {
        $existing = $this->extensions[$extension->getAlias()] ?? null;
        if (null !== $existing) {
            throw new ExtensionException(sprintf('GraphQL Extensions "%s" and "%s" both use the same alias "%s".', get_class($existing), get_class($extension), $extension->getAlias()));
        }

        $this->extensions[$extension->getAlias()] = $extension;

        return $this;
    }
}
