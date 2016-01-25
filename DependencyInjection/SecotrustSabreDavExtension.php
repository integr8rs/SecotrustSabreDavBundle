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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * Class SecotrustSabreDavExtension.
 */
class SecotrustSabreDavExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services/services.xml');

        if (isset($config['base_uri'])) {
            $container->getDefinition('secotrust.sabredav.server')->addMethodCall('setBaseUri', array($config['base_uri']));
            $container->setParameter('secotrust.sabredav.base_uri', $config['base_uri']);
        }

        // load all plugins
        foreach ($config['plugins'] as $plugin => $enabled) {
            if ($enabled) {
                $loader->load(sprintf('services/plugins/%s.xml', $plugin));
            }
        }

        // no root dir is set, but webdav plugin is active: throw exception
        if (!empty($config['root_dir']) && $config['plugins']['webdav']) {
            //replace argument
            $container->getDefinition('secotrust.sabredav_root')->replaceArgument(0, $config['root_dir']);
        }

        // add logo to browser-plugin
        if ($config['plugins']['browser']) {
            $container->setParameter('secotrust.sabredav.browser_plugin.logo', $config['browser_logo']);
            $container->setParameter('secotrust.sabredav.browser_plugin.favicon', $config['favicon']);
        }

        // add security-service-class
        if ($config['security_service']) {
            $container->setParameter('secotrust.sabredav.acl.securityService', $config['security_service']);
        }

        $container->setParameter('secotrust.cards_class', $config['settings']['cards_class']);
        $container->setParameter('secotrust.addressbooks_class', $config['settings']['addressbooks_class']);
        $container->setParameter('secotrust.calendarobjects_class', $config['settings']['calendarobjects_class']);
        $container->setParameter('secotrust.calendar_class', $config['settings']['calendar_class']);
        $container->setParameter('secotrust.principals_class', $config['settings']['principals_class']);
        $container->setParameter('secotrust.principalgroups_class', $config['settings']['principalgroups_class']);
    }
}
