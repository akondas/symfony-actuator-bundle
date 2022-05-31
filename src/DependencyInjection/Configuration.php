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

        $rootNode->children()
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
            ->end()
            ->end()
            ->end()
            ->end()
            ->arrayNode('info')
            ->addDefaultsIfNotSet()
            ->canBeDisabled()
            ->children()
            ->arrayNode('builtin')->defaultValue(['php', 'symfony', 'git'])->scalarPrototype()->validate()->ifNotInArray(['php', 'symfony', 'git'])->thenInvalid('Invalid info builtin configuration. Available builtins are: php, symfony and git')->end()
            ->end()
            ->end()
            ->end()
            ->end();

        return $treeBuilder;
    }
}
