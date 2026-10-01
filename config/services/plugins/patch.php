<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('secotrust.sabredav.patch_plugin.class', \Sabre\DAV\PartialUpdate\Plugin::class);

    $services->set('secotrust.sabredav.patch_plugin', '%secotrust.sabredav.patch_plugin.class%')
        ->private()
        ->tag('secotrust.sabredav.plugin');
};
