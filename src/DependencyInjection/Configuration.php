<?php

declare(strict_types=1);

namespace Akondas\ActuatorBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('actuator');

        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('health')
                    ->canBeDisabled()
                    ->children()
                        ->arrayNode('builtin')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('disk_space')
                                    ->addDefaultsIfNotSet()
                                    ->canBeDisabled()
                                    ->children()
                                        ->integerNode('threshold')->defaultValue(50 * 1024 * 1024)->end()
                                        ->scalarNode('path')->defaultValue('%kernel.project_dir%')->end()
                                    ->end()
                                ->end()
                                ->arrayNode('database')
                                    ->addDefaultsIfNotSet()
                                    ->canBeDisabled()
                                    ->children()
                                        ->arrayNode('connections')
                                            ->useAttributeAsKey('name')
                                            ->defaultValue(['default' => ['service' => 'Doctrine\DBAL\Connection', 'check_sql' => 'SELECT 1']])
                                            ->arrayPrototype()
                                                ->children()
                                                    ->scalarNode('service')->isRequired()->end()
                                                    ->scalarNode('check_sql')->defaultValue('SELECT 1')->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('info')
                    ->addDefaultsIfNotSet()
                    ->canBeDisabled()
                    ->children()
                        ->arrayNode('builtin')
                            ->useAttributeAsKey('name')
                            ->defaultValue(['php', 'symfony', 'git', 'database'])
                            ->arrayPrototype()
                                ->children()
                                    ->canBeDisabled()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
