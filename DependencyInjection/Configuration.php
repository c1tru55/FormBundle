<?php

namespace ITE\FormBundle\DependencyInjection;

use Doctrine\Common\Inflector\Inflector;
use ITE\FormBundle\Service\SFFormExtension;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
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
        $rootNode = $treeBuilder->root('ite_form');

        // add plugins configuration
        $pluginsNode = $rootNode->children()->arrayNode('plugins');
        foreach (SFFormExtension::getPlugins() as $plugin) {
            // add common plugin configuration
            $pluginNode = $this->addPluginConfiguration($plugin, $pluginsNode);

            // load specific plugin configuration
            $method = 'add' . Inflector::classify($plugin) . 'Configuration';
            if (method_exists($this, $method)) {
                $this->$method($pluginNode);
            }
        }

        $rootNode
            ->children()
                ->scalarNode('timezone')->defaultValue(date_default_timezone_get())->end()
            ->end()
        ->end();

        return $treeBuilder;
    }

    /**
     * @param $plugin
     * @param ArrayNodeDefinition $pluginsNode
     * @return NodeBuilder
     */
    private function addPluginConfiguration($plugin, ArrayNodeDefinition $pluginsNode)
    {
        /** @var $pluginNode NodeBuilder */
        $pluginNode = $pluginsNode
            ->children()
                ->arrayNode($plugin)
                    ->canBeUnset()
                    ->addDefaultsIfNotSet()
                    ->treatNullLike(array('enabled' => true))
                    ->treatTrueLike(array('enabled' => true))
                    ->children();

        $pluginNode
            ->booleanNode('enabled')->defaultFalse()->end()
            ->variableNode('options')->defaultValue(array())->end();

//        $pluginNode
//                    ->end()
//                ->end()
//            ->end()
//        ;

        return $pluginNode;
    }

    /**
     * @param $pluginNode
     */
    private function addFileuploadConfiguration(NodeBuilder $pluginNode)
    {
        $pluginNode
            ->scalarNode('web_root')->defaultValue('%kernel.root_dir%/../web')->end()
            ->scalarNode('prefix')->defaultValue('')->end()
            ->variableNode('file_manager')->defaultValue(array())->end();
        ;
    }
}
