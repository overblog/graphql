<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\ConfigurationProvider;

use Overblog\GraphQLBundle\Configuration\Configuration;
use Overblog\GraphQLBundle\Configuration\ConfigurationException;
use Overblog\GraphQLBundle\Configuration\TypeConfiguration;
use Overblog\GraphQLBundle\Event\ConfigurationEvent;
use Overblog\GraphQLBundle\Extension\ExtensionException;
use Overblog\GraphQLBundle\Extension\ExtensionRegistry;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ConfigurationProvider
{
    protected ValidatorInterface $validator;
    protected EventDispatcherInterface $eventDispatcher;
    protected ExtensionRegistry $extensionRegistry;

    /** @var ConfigurationProviderInterface[] */
    protected iterable $providers = [];

    public function __construct(ValidatorInterface $validator, EventDispatcherInterface $eventDispatcher, iterable $providers, ExtensionRegistry $extensionRegistry)
    {
        $this->validator = $validator;
        $this->eventDispatcher = $eventDispatcher;
        $this->providers = $providers;
        $this->extensionsRegistry = $extensionRegistry;
    }

    public function getConfiguration(): Configuration
    {
        $configuration = new Configuration();

        // Merge all configurations from providers
        foreach ($this->providers as $provider) {
            $configuration->merge($provider->getConfiguration());
        }

        $exception = new ConfigurationException();

        // Apply extension configurations
        $configuration->apply(function (TypeConfiguration $type) use ($configuration, $exception) {
            foreach ($type->getExtensions() as $extConfiguration) {
                try {
                    $extension = $this->extensionsRegistry->getExtension($extConfiguration->getAlias());

                    $parsedConfiguration = $extension->processConfiguration($type, $extConfiguration->getConfiguration());
                    $extConfiguration->setProcessedConfiguration($parsedConfiguration);

                    // Allow extension to alter configuration tree
                    $extension->handleConfiguration($configuration, $type, $extConfiguration->getConfiguration());
                } catch (ExtensionException $e) {
                    $exception->addError($type, $e->getMessage());
                }
            }
        });

        $event = new ConfigurationEvent($configuration);
        $this->eventDispatcher->dispatch($event);

        $errors = $this->validator->validate($configuration);
        $exception->addViolations($errors);

        if ($exception->hasErrors()) {
            throw $exception;
        }

        return $configuration;
    }
}
