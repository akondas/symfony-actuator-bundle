<?php

declare(strict_types=1);

namespace Akondas\ActuatorBundle\DependencyInjection;

use Akondas\ActuatorBundle\Service\Health\Indicator\DatabaseConnectionHealthIndicator;
use Akondas\ActuatorBundle\Service\Health\Indicator\DiskSpaceHealthIndicator;
use Akondas\ActuatorBundle\Service\Info\Collector\Database;
use Akondas\ActuatorBundle\Service\Info\Collector\Git;
use Akondas\ActuatorBundle\Service\Info\Collector\Php;
use Akondas\ActuatorBundle\Service\Info\Collector\Symfony;
use Doctrine\DBAL\Connection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

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

        if ($container->willBeAvailable('doctrine/doctrine-bundle', Connection::class, [])) {
            $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config/extensions'));
            $loader->load('doctrine.yaml');
        }

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

        if ($container->willBeAvailable('doctrine/doctrine-bundle', Connection::class, [])) {
            if (is_array($config['builtin']) && is_array($config['builtin']['database'])) {
                $databaseConfig = $config['builtin']['database'];
                if (!$this->isConfigEnabled($container, $databaseConfig)) {
                    $container->removeDefinition(DatabaseConnectionHealthIndicator::class);
                } else {
                    $definition = $container->getDefinition(DatabaseConnectionHealthIndicator::class);

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
                'php' => Php::class,
                'symfony' => Symfony::class,
                'git' => Git::class,
            ];
            foreach ($builtinMap as $key => $definition) {
                if (isset($config['builtin'][$key]) && is_array($config['builtin'][$key]) && !$this->isConfigEnabled($container, $config['builtin'][$key])) {
                    $container->removeDefinition($definition);
                }
            }
            if ($container->willBeAvailable('doctrine/doctrine-bundle', Connection::class, []) && isset($config['builtin']['database'])) {
                $databaseConfig = $config['builtin']['database'];
                if (isset($databaseConfig['connections']) && is_array($databaseConfig['connections'])) {
                    $connectionReferences = [];
                    foreach ($databaseConfig['connections'] as $name => $connectionDefintion) {
                        $connectionReferences[$name] = new Reference($connectionDefintion);
                    }
                    $definition = $container->getDefinition(Database::class);
                    $definition->replaceArgument(0, $connectionReferences);
                }
            }
        }
    }
}
