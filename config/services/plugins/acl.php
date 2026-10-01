<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('secotrust.sabredav.acl_plugin.class', \Secotrust\Bundle\SabreDavBundle\SabreDav\AclPlugin::class);
    $parameters->set('secotrust.sabredav.acl_securitymanager.class', \Secotrust\Bundle\SabreDavBundle\SabreDav\Acl\SecurityManager::class);
    $parameters->set('sabredav.acl.hideNodesFromListings', true);
    $parameters->set('sabredav.acl.accessToNodesWithoutACL', true);
    $parameters->set('sabredav.acl.defaultUsernamePath', 'principals');

    $services->set('secotrust.sabredav_acl_plugin', '%secotrust.sabredav.acl_plugin.class%')
        ->private()
        ->args([
            service('security.authorization_checker'),
            service('service_container'),
        ])
        ->tag('secotrust.sabredav.plugin')
        ->call('setHideNodesFromListings', ['%sabredav.acl.hideNodesFromListings%'])
        ->call('setAccessToNodesWithoutACL', ['%sabredav.acl.accessToNodesWithoutACL%'])
        ->call('setDefaultUsernamePath', ['%sabredav.acl.defaultUsernamePath%']);

    $services->set('secotrust.sabredav_acl_securityManager', '%secotrust.sabredav.acl_securitymanager.class%')
        ->lazy()
        ->args([service('service_container')]);
};
