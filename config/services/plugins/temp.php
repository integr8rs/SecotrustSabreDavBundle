<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('secotrust.sabredav.temp_plugin.class', \Sabre\DAV\TemporaryFileFilterPlugin::class);

    $services->set('secotrust.sabredav_temp_plugin', '%secotrust.sabredav.temp_plugin.class%')
        ->private()
        ->args(['%kernel.cache_dir%/sabredav/temp'])
        ->tag('secotrust.sabredav.plugin');
};
