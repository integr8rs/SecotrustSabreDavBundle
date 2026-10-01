<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('secotrust.sabredav.root.class', \Sabre\DAV\FS\Directory::class);

    $services->set('secotrust.sabredav_root', '%secotrust.sabredav.root.class%')
        ->private()
        ->args([''])
        ->tag('secotrust.sabredav.collection');
};
