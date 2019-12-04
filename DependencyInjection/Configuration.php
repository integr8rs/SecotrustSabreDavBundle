<?php

/*
 * This file is part of the SecotrustSabreDavBundle package.
 *
 * (c) Henrik Westphal <henrik.westphal@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Secotrust\Bundle\SabreDavBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration.
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('secotrust_sabre_dav');
        if (\method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // BC layer for symfony/config 4.1 and older
            $rootNode = $treeBuilder->root('secotrust_sabre_dav');
        }

        $default_base_uri = '/app_dev.php/remote';

        $rootNode
            ->children()
                ->scalarNode('root_dir')
                    ->example('%kernel.root_dir%/../web/dav/')
                ->end()
                ->scalarNode('browser_logo')
                    ->example('%kernel.root_dir%/../web/logo/sabredav.png')
                    ->defaultValue('')
                ->end()
                ->scalarNode('favicon')
                    ->example('%kernel.root_dir%/../web/logo/favicon.ico')
                    ->defaultValue('')
                ->end()
                ->scalarNode('security_service')
                    ->example('sabredav.security_service')
                    ->defaultValue('')
                ->end()
                ->scalarNode('base_uri')
                    ->example($default_base_uri)
                ->end()
                ->booleanNode('use_symfony_exception_handler')
                    ->defaultFalse()
                ->end()
                ->arrayNode('plugins')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('acl')->defaultFalse()->end()
                        ->booleanNode('auth')->defaultFalse()->end()
                        ->booleanNode('browser')->defaultFalse()->end()
                        ->booleanNode('lock')->defaultTrue()->end()
                        ->booleanNode('temp')->defaultTrue()->end()
                        ->booleanNode('mount')->defaultFalse()->end()
                        ->booleanNode('patch')->defaultFalse()->end()
                        ->booleanNode('content_type')->defaultFalse()->end()
                        ->booleanNode('webdav')->defaultFalse()->end()
                        ->booleanNode('principal')->defaultFalse()->end()
                        ->booleanNode('carddav')->defaultFalse()->end()
                        ->booleanNode('caldav')->defaultFalse()->end()
                    ->end()
                ->end()
                ->arrayNode('settings')
                    ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('cards_class')->defaultValue('')->end()
                            ->scalarNode('addressbooks_class')->defaultValue('')->end()
                            ->scalarNode('calendarobjects_class')->defaultValue('')->end()
                            ->scalarNode('calendar_class')->defaultValue('')->end()
                            ->scalarNode('principals_class')->defaultValue('')->end()
                            ->scalarNode('principalgroups_class')->defaultValue('')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
