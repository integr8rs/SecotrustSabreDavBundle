<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('secotrust.sabredav.content_type_plugin.class', \Sabre\DAV\Browser\GuessContentType::class);

    $services->set('secotrust.sabredav_content_type_plugin', '%secotrust.sabredav.content_type_plugin.class%')
        ->private()
        ->tag('secotrust.sabredav.plugin');
};
