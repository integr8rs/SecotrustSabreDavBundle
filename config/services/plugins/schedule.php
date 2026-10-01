<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('secotrust.sabredav.schedule_plugin.class', \Sabre\CalDAV\Schedule\Plugin::class);

    $services->set('secotrust.sabredav.schedule_plugin', '%secotrust.sabredav.schedule_plugin.class%')
        ->private()
        ->tag('secotrust.sabredav.plugin');
};
