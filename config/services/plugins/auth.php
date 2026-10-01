<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('secotrust.sabredav.auth_backend.class', \Secotrust\Bundle\SabreDavBundle\SabreDav\AuthBackend::class);
    $parameters->set('secotrust.sabredav.auth_plugin.class', \Sabre\DAV\Auth\Plugin::class);
    $parameters->set('secotrust.sabredav.auth.realm', 'SabreDAV');

    $services->set('secotrust.sabredav_auth_backend', '%secotrust.sabredav.auth_backend.class%')
        ->private()
        ->args([
            service('service_container'),
            '%secotrust.sabredav.auth.realm%',
        ]);

    $services->set('secotrust.sabredav_auth_plugin', '%secotrust.sabredav.auth_plugin.class%')
        ->private()
        ->args([
            service('secotrust.sabredav_auth_backend'),
            '%secotrust.sabredav.auth.realm%',
        ])
        ->tag('secotrust.sabredav.plugin');
};
