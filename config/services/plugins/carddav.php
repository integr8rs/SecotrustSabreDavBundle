<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('secotrust.sabredav.carddav_backend.class', \Secotrust\Bundle\SabreDavBundle\SabreDav\CardDavBackend::class);
    $parameters->set('secotrust.sabredav.carddav_plugin.class', \Sabre\CardDAV\Plugin::class);
    $parameters->set('secotrust.sabredav.carddav_collection.class', \Sabre\CardDAV\AddressBookRoot::class);

    $services->set('secotrust.sabredav_carddav_backend', '%secotrust.sabredav.carddav_backend.class%')
        ->private()
        ->args([service('service_container')]);

    $services->set('secotrust.sabredav_carddav_plugin', '%secotrust.sabredav.carddav_plugin.class%')
        ->private()
        ->tag('secotrust.sabredav.plugin');

    $services->set('secotrust.sabredav_carddav_collection', '%secotrust.sabredav.carddav_collection.class%')
        ->private()
        ->args([
            service('secotrust.sabredav_principal_backend'),
            service('secotrust.sabredav_carddav_backend'),
        ])
        ->tag('secotrust.sabredav.collection');
};
