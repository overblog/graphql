<?php

declare(strict_types=1);

namespace Overblog\GraphQL\Bundle\ProfilerBundle\DependencyInjection;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class GraphQLProfilerExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $this->loadConfigFiles($container);

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter($this->getAlias().'.query_match', $config['query_match']);
    }

    /**
     * @throws Exception
     */
    public function loadConfigFiles(ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');
    }

    public function getAlias()
    {
        return Configuration::NAME;
    }
}
