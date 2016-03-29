<?php

namespace Draw\DrawBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class DrawDrawExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($config, $container);
        $config = $this->processConfiguration($configuration, $config);
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        if($config['use_api_exception_subscriber']) {
            $loader->load('api_exception_subscriber.yml');
        }

        if($config['use_doctrine_repository_factory']) {
            $loader->load('doctrine_repository_factory.yml');
        }

        $container->getDefinition('draw.serializer.self_link')
            ->addMethodCall('setAddClass', [$config['serialization_add_class']]);
    }

    public function getAlias()
    {
        return 'draw';
    }
}
