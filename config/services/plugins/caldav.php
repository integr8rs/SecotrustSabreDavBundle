<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('secotrust.sabredav.caldav_backend.class', \Secotrust\Bundle\SabreDavBundle\SabreDav\CalDavBackend::class);
    $parameters->set('secotrust.sabredav.caldav_plugin.class', \Sabre\CalDAV\Plugin::class);
    $parameters->set('secotrust.sabredav.caldav_collection.class', \Sabre\CalDAV\CalendarRoot::class);

    $services->set('secotrust.sabredav_caldav_backend', '%secotrust.sabredav.caldav_backend.class%')
        ->private()
        ->args([service('service_container')]);

    $services->set('secotrust.sabredav_caldav_plugin', '%secotrust.sabredav.caldav_plugin.class%')
        ->private()
        ->tag('secotrust.sabredav.plugin');

    $services->set('secotrust.sabredav_caldav_collection', '%secotrust.sabredav.caldav_collection.class%')
        ->private()
        ->args([
            service('secotrust.sabredav_principal_backend'),
            service('secotrust.sabredav_caldav_backend'),
        ])
        ->tag('secotrust.sabredav.collection');
};
