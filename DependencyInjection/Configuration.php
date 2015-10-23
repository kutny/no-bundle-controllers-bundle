<?php
namespace Kutny\NoBundleControllersBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('kutny_no_bundle_controllers');

        $rootNode
            ->children()
                ->arrayNode('templates_namespaces')
                    ->isRequired()
                    ->prototype('scalar')->end()
                ->end()
                ->scalarNode('templates_dir')
                    ->defaultNull()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
