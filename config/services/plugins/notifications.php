<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('secotrust.sabredav.notification_plugin.class', \Sabre\CalDAV\Notifications\Plugin::class);

    $services->set('secotrust.sabredav.notification_plugin', '%secotrust.sabredav.notification_plugin.class%')
        ->private()
        ->tag('secotrust.sabredav.plugin');
};
