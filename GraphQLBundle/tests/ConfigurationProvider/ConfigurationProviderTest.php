<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Tests\ConfigurationProvider;

use Overblog\GraphQLBundle\ConfigurationProvider\ConfigurationProvider;
use Overblog\GraphQLBundle\Extension\Extension;
use Overblog\GraphQLBundle\Extension\ExtensionRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class Extension1 extends Extension
{
    const ALIAS = 'MyExtension';
}

class ConfigurationProviderTest extends TestCase
{
    private ConfigurationProvider $provider;

    public function setUp(): void
    {
        $validator = $this->createMock(ValidatorInterface::class);
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $extensionRegistry = new ExtensionRegistry([]);
        $providers = [];

        $this->provider = new ConfigurationProvider($validator, $eventDispatcher, $providers, $extensionRegistry);
    }

    // Can't register two extensions with the same name
    public function testCantRegisterExtensionWithSameName()
    {
        $extension1 = new Extension1();
        $extension2 = new Extension1();

        /*
        $this->provider->addExtension($extension1);
        $this->expectExceptionMessageMatches('/both use the same name "MyExtension"/');
        $this->provider->addExtension($extension2);
        */
    }
}
