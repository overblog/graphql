<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Configuration;

use Exception;

class ExtensionConfiguration
{
    protected string $alias;

    protected bool $processed = false;

    protected $configuration;

    /**
     * @param string     $alias         The builder alias
     * @param mixed|null $configuration The builder configuration
     */
    public function __construct(string $alias, $configuration = null)
    {
        $this->alias = $alias;
        $this->configuration = $configuration;
    }

    /**
     * @param string     $alias         The builder alias
     * @param mixed|null $configuration The builder configuration
     */
    public static function get(string $alias, $configuration = null): ExtensionConfiguration
    {
        return new static($alias, $configuration);
    }

    public function getAlias()
    {
        return $this->alias;
    }

    public function getConfiguration()
    {
        return $this->configuration;
    }

    public function setProcessedConfiguration($configuration)
    {
        if (true === $this->processed) {
            throw new Exception('Extension configuration has already been processed');
        }
        $this->configuration = $configuration;
        $this->processed = true;
    }

    public function isProcessed()
    {
        return $this->processed;
    }

    public function toArray(): array
    {
        return array_filter([
            'alias' => $this->alias,
            'configuration' => $this->configuration,
        ]);
    }
}
