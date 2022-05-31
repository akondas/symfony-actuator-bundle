<?php

declare(strict_types=1);

namespace Akondas\ActuatorBundle\DependencyInjection;

use Akondas\ActuatorBundle\Service\Health\Indicator\DiskSpaceHealthIndicator;
use Akondas\ActuatorBundle\Service\Info\Collector\Git;
use Akondas\ActuatorBundle\Service\Info\Collector\Php;
use Akondas\ActuatorBundle\Service\Info\Collector\Symfony;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class ActuatorExtension extends Extension
{
    /**
     * @param mixed[] $configs
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $this->processHealthConfiguration($config['health'], $container);
        $this->processInfoConfiguration($config['info'], $container);
    }

    /**
     * @param mixed[] $config
     */
    private function processHealthConfiguration(array $config, ContainerBuilder $container): void
    {
        $enabled = true;
        if (!$this->isConfigEnabled($container, $config)) {
            $enabled = false;
        }
        $container->setParameter('actuator.health.enabled', $enabled);
        if (is_array($config['builtin']) && is_array($config['builtin']['disk_space'])) {
            $diskSpaceConfig = $config['builtin']['disk_space'];
            if (!$this->isConfigEnabled($container, $diskSpaceConfig)) {
                $container->removeDefinition(DiskSpaceHealthIndicator::class);
            } else {
                $definition = $container->getDefinition(DiskSpaceHealthIndicator::class);
                $definition->replaceArgument(0, $diskSpaceConfig['path']);
                $definition->replaceArgument(1, $diskSpaceConfig['threshold']);
            }
        }
    }

    /**
     * @param mixed[] $config
     */
    private function processInfoConfiguration(array $config, ContainerBuilder $container): void
    {
        $enabled = true;
        if (!$this->isConfigEnabled($container, $config)) {
            $enabled = false;
        }
        $container->setParameter('actuator.info.enabled', $enabled);

        if (is_array($config['builtin'])) {
            if (false === in_array('php', $config['builtin'], true)) {
                $container->removeDefinition(Php::class);
            }
            if (false === in_array('symfony', $config['builtin'], true)) {
                $container->removeDefinition(Symfony::class);
            }
            if (false === in_array('git', $config['builtin'], true)) {
                $container->removeDefinition(Git::class);
            }
        }
    }
}
