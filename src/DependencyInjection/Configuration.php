<?php

namespace Oka\AttachmentManagerBundle\DependencyInjection;

use Oka\AttachmentManagerBundle\Model\AttachmentInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('oka_attachment_manager');
        /** @var \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->addDefaultsIfNotSet()
            ->validate()
                ->ifTrue(static function ($v) {
                    $relatedObjectNames = [];

                    foreach (['orm', 'mongodb'] as $dbDriver) {
                        foreach ($v[$dbDriver]['related_objects'] as $key => $value) {
                            $relatedObjectNames[] = strtolower($key);
                        }
                    }

                    return count($relatedObjectNames) !== count(array_unique($relatedObjectNames));
                })
                ->thenInvalid('Related objects cannot have the same name.')
            ->end()
            ->validate()
                ->ifTrue(static function ($v) {
                    $volumeNames = array_keys($v['volumes']);

                    foreach (['orm', 'mongodb'] as $dbDriver) {
                        foreach ($v[$dbDriver]['related_objects'] as $value) {
                            if (!in_array($value['volume_used'], $volumeNames)) {
                                return true;
                            }
                        }
                    }

                    return false;
                })
                ->thenInvalid('A related object uses an undefined volume name.')
            ->end()
            ->children()
                ->scalarNode('prefix_separator')
                    ->defaultValue('.')
                ->end()

                ->arrayNode('volumes')
                    ->treatNullLike([])
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('name')
                                ->defaultNull()
                            ->end()

                            ->scalarNode('dsn')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()

                            ->scalarNode('public_url')
                                ->defaultNull()
                            ->end()

                            ->arrayNode('options')
                                ->treatNullLike([])
                                ->ignoreExtraKeys(false)
                            ->end()
                        ->end()
                    ->end()
                ->end()

                ->append($this->getDBDriverNodeDefinition('orm'))

                ->append($this->getDBDriverNodeDefinition('mongodb'))
            ->end();

        return $treeBuilder;
    }

    private function getDBDriverNodeDefinition(string $name): NodeDefinition
    {
        $node = new ArrayNodeDefinition($name);
        $node
            ->treatNullLike([])
            ->canBeEnabled()
            ->addDefaultsIfNotSet()
            ->beforeNormalization()
                ->always(static function ($v) {
                    $relatedObjects = [];

                    foreach ($v['related_objects'] as $key => $value) {
                        $relatedObjects[$value['name'] ?? strtolower($key)] = $value;
                    }

                    $v['related_objects'] = $relatedObjects;

                    return $v;
                })
            ->end()
            ->children()
                ->scalarNode('model_manager_name')
                    ->defaultNull()
                ->end()

                ->scalarNode('class')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->validate()
                        ->ifTrue(static function ($v) {
                            return null !== $v && !(new \ReflectionClass($v))->implementsInterface(AttachmentInterface::class);
                        })
                        ->thenInvalid('The configuration value "oka_attachment_manager.'.$name.'.class" is not valid because "%s" class given must implement "'.AttachmentInterface::class.'".')
                    ->end()
                ->end()

                ->arrayNode('related_objects')
                    ->treatNullLike([])
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('name')
                                ->defaultNull()
                                ->beforeNormalization()
                                    ->always(static function ($v) {
                                        return strtolower($v);
                                    })
                                ->end()
                            ->end()

                            ->scalarNode('class')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()

                            ->scalarNode('volume_used')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()

                            ->scalarNode('upload_max_size')
                                ->defaultNull()
                            ->end()

                            ->scalarNode('directory')
                                ->defaultNull()
                                ->validate()
                                    ->ifTrue(static function ($v) {
                                        if (null === $v) {
                                            return false;
                                        }

                                        return str_starts_with($v, '/') || str_ends_with($v, '/');
                                    })
                                    ->thenInvalid('The configuration value "oka_attachment_manager.'.$name.'.volumes.related_objects.directory" is not valid, it should not start and end with the character "/".')
                                ->end()
                            ->end()

                            ->scalarNode('prefix')
                                ->defaultNull()
                                ->validate()
                                    ->ifTrue(static function ($v) {
                                        if (null === $v) {
                                            return false;
                                        }

                                        return (bool) preg_match('#/#', $v);
                                    })
                                    ->thenInvalid('The configuration value "oka_attachment_manager.'.$name.'.volumes.related_objects.prefix" is not valid because "%s" contains prohibited character "/".')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $node;
    }
}
