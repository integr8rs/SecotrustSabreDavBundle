<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('secotrust.sabredav.mount_plugin.class', \Sabre\DAV\Mount\Plugin::class);

    $services->set('secotrust.sabredav.mount_plugin', '%secotrust.sabredav.mount_plugin.class%')
        ->private()
        ->tag('secotrust.sabredav.plugin');
};
