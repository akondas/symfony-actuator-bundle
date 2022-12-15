<?php

declare(strict_types=1);

namespace Akondas\ActuatorBundle\DependencyInjection;

use Akondas\ActuatorBundle\Service\Health\Indicator as HealthIndicator;
use Akondas\ActuatorBundle\Service\Info\Collector as InfoCollector;
use Doctrine\DBAL\Connection;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Mailer\Transport\Transports;

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
                $container->removeDefinition(HealthIndicator\DiskSpace::class);
            } else {
                $definition = $container->getDefinition(HealthIndicator\DiskSpace::class);
                $definition->replaceArgument(0, $diskSpaceConfig['path']);
                $definition->replaceArgument(1, $diskSpaceConfig['threshold']);
            }
        }

        if (
            $container->willBeAvailable('doctrine/doctrine-bundle', Connection::class, []) &&
            isset($config['builtin']['database']) &&
            $this->isConfigEnabled($container, $config['builtin']['database'])
        ) {
            $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config/extensions'));
            $loader->load('doctrine_health.yaml');

            $databaseConfig = $config['builtin']['database'];
            $definition = $container->getDefinition(HealthIndicator\Database::class);

            if (is_array($databaseConfig['connections'])) {
                $constructorArgument = [];
                foreach ($databaseConfig['connections'] as $name => $connection) {
                    if (!is_array($connection)) {
                        continue;
                    }

                    $constructorArgument[$name] = [
                        'connection' => new Reference($connection['service']),
                        'sql' => $connection['check_sql'],
                    ];
                }

                $definition->replaceArgument(0, $constructorArgument);
            }
        }

        if (
            $container->willBeAvailable('symfony/mailer', Transports::class, []) &&
            isset($config['builtin']['mailer']) &&
            $this->isConfigEnabled($container, $config['builtin']['mailer'])
        ) {
            $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config/extensions'));
            $loader->load('mailer_health.yaml');
            $transportConfig = $config['builtin']['mailer'];
            if (isset($transportConfig['transports']) && is_array($transportConfig['transports'])) {
                $transportReferences = [];
                foreach ($transportConfig['transports'] as $name => $currentTransportConfig) {
                    $transportReferences[$name] = new Reference($currentTransportConfig['service']);
                }
                $definition = $container->getDefinition(HealthIndicator\Mailer::class);
                $definition->replaceArgument('$transports', $transportReferences);
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

        if (isset($config['builtin']) && is_array($config['builtin'])) {
            $builtinMap = [
                'php' => InfoCollector\Php::class,
                'symfony' => InfoCollector\Symfony::class,
                'git' => InfoCollector\Git::class,
            ];
            foreach ($builtinMap as $key => $definition) {
                if (isset($config['builtin'][$key]) && is_array($config['builtin'][$key]) && !$this->isConfigEnabled($container, $config['builtin'][$key])) {
                    $container->removeDefinition($definition);
                }
            }

            if (
                $container->willBeAvailable('doctrine/doctrine-bundle', Connection::class, []) &&
                isset($config['builtin']['database']) &&
                $this->isConfigEnabled($container, $config['builtin']['database'])
            ) {
                $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config/extensions'));
                $loader->load('doctrine_info.yaml');

                $databaseConfig = $config['builtin']['database'];
                if (isset($databaseConfig['connections']) && is_array($databaseConfig['connections'])) {
                    $connectionReferences = [];
                    foreach ($databaseConfig['connections'] as $name => $connectionDefinition) {
                        $connectionReferences[$name] = new Reference($connectionDefinition);
                    }
                    $definition = $container->getDefinition(InfoCollector\Database::class);
                    $definition->replaceArgument(0, $connectionReferences);
                }
            }

            if (
                $container->willBeAvailable('symfony/mailer', Transports::class, []) &&
                isset($config['builtin']['mailer']) &&
                $this->isConfigEnabled($container, $config['builtin']['mailer'])
            ) {
                $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config/extensions'));
                $loader->load('mailer_info.yaml');
                $transportConfig = $config['builtin']['mailer'];
                if (isset($transportConfig['transports']) && is_array($transportConfig['transports'])) {
                    $transportReferences = [];
                    foreach ($transportConfig['transports'] as $name => $transportDefinition) {
                        $transportReferences[$name] = new Reference($transportDefinition);
                    }
                    $definition = $container->getDefinition(InfoCollector\Mailer::class);
                    $definition->replaceArgument(0, $transportReferences);
                }
            }
        }
    }
}
