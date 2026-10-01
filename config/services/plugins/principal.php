<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('secotrust.sabredav.principal_backend.class', \Secotrust\Bundle\SabreDavBundle\SabreDav\PrincipalBackend::class);
    $parameters->set('secotrust.sabredav.principal_plugin.class', \Sabre\DAVACL\Plugin::class);
    $parameters->set('secotrust.sabredav.principal_collection.class', \Sabre\DAVACL\PrincipalCollection::class);

    $services->set('secotrust.sabredav_principal_backend', '%secotrust.sabredav.principal_backend.class%')
        ->private()
        ->args([service('service_container')]);

    $services->set('secotrust.sabredav_principal_plugin', '%secotrust.sabredav.principal_plugin.class%')
        ->private()
        ->tag('secotrust.sabredav.plugin');

    $services->set('secotrust.sabredav_principal_collection', '%secotrust.sabredav.principal_collection.class%')
        ->private()
        ->args([service('secotrust.sabredav_principal_backend')])
        ->tag('secotrust.sabredav.collection');
};
