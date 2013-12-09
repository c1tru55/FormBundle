<?php

namespace ITE\FormBundle\DependencyInjection;

use ITE\FormBundle\SF\ExtensionInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @var ContainerBuilder
     */
    protected $container;

    /**
     * @param ContainerBuilder $container
     */
    public function __construct(ContainerBuilder $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ite_form');

        $this->addComponentsConfiguration($rootNode);
        $this->addPluginsConfiguration($rootNode);

        $rootNode
            ->children()
                ->scalarNode('timezone')->defaultValue(date_default_timezone_get())->end()
            ->end()
        ->end();

        return $treeBuilder;
    }

    /**
     * @param ArrayNodeDefinition $rootNode
     */
    private function addComponentsConfiguration(ArrayNodeDefinition $rootNode)
    {
        $componentsNode = $rootNode
            ->children()
                ->arrayNode('components')
                    ->canBeUnset();

        $serviceIds = $this->container->findTaggedServiceIds('ite_form.component');
        foreach ($serviceIds as $serviceId => $attributes) {
            /** @var $component ExtensionInterface */
            $component = $this->container->get($serviceId);
            $component->addConfiguration($componentsNode);
        }
    }

    /**
     * @param ArrayNodeDefinition $rootNode
     */
    private function addPluginsConfiguration(ArrayNodeDefinition $rootNode)
    {
        $pluginsNode = $rootNode
            ->children()
                ->arrayNode('plugins')
                    ->canBeUnset();

        $serviceIds = $this->container->findTaggedServiceIds('ite_form.plugin');
        foreach ($serviceIds as $serviceId => $attributes) {
            /** @var $plugin ExtensionInterface */
            $plugin = $this->container->get($serviceId);
            $plugin->addConfiguration($pluginsNode);
        }
    }

}
